/*
 * Grafik-Editor – Modus „Markierungen“ (format=hotspots) für den Block „Interaktives Bild“ des Cores (Core\Hotspots).
 *
 * Das Feld „Markierungen“ öffnet den Editor eingebettet (Extension::hotspotEditor, shell.js spricht per postMessage mit ihm).
 * Das Bild der Mediathek ist die gesperrte Grundlage (<image data-hs-base>, viewBox = Bildgröße in Pixeln). Jede Markierung ist
 * eine gewöhnliche Form des Editors mit data-hs-shape und den Angaben als data-hs-*:
 *   point  <circle> fester Größe – zählt nur der Mittelpunkt
 *   circle <circle>/<ellipse> – Mittelpunkt, Radius = halbe Breite (in % der Bildbreite wie Core\Hotspots)
 *   rect   <rect> – Rahmen (x/y = linke obere Ecke)
 *   poly   <polygon> bzw. <path> nach dem Knoten-Werkzeug – Eckpunkte (3–64)
 * Zeichnen, Auswählen, Ziehen, Größe, Eckpunkte, Tastatur und Rückgängig sind die des Editors. Die Reihenfolge der Ebenen ist die
 * Nummer. Übernehmen rechnet alles in Prozent um (zwei Nachkommastellen); load() → data() ergibt dieselben Daten. MIT
 */
import { t } from 'klxm-studio/_i18n.js';
import { esc } from 'klxm-studio/_html.js';
import { ico } from 'klxm-studio/_icons.js';
import { toast } from 'klxm-studio/_toast.js';
import { NS, docMatrix, docBounds, pt, parsePath, toNodes } from './geom.js';

export const SHAPES = ['point', 'circle', 'rect', 'poly'];
const KEYS = ['title', 'text', 'link', 'link_label', 'term', 'icon'];
const ICON = { point: 'map-pin', circle: 'circle', rect: 'rectangle', poly: 'polygon' };
const r2 = v => Math.round(v * 100) / 100;
const clamp = (v, a = 0, b = 100) => Math.max(a, Math.min(b, v));
const fin = v => Number.isFinite(+v);

export const shapeLabel = s => ({ point: t('Punkt'), circle: t('Kreis'), rect: t('Rechteck'), poly: t('Fläche') })[s] || s;
export const isMarker = el => !!el?.getAttribute?.('data-hs-shape');
export const isBase = el => el?.hasAttribute?.('data-hs-base');

/** Schwerpunkt wie Core\Hotspots::anchor (Prozent oder Pixel, gleiche Rechnung) */
export function centroid(p) {
  let a = 0, cx = 0, cy = 0;
  for (let i = 0; i < p.length; i++) {
    const [x0, y0] = p[i], [x1, y1] = p[(i + 1) % p.length], f = x0 * y1 - x1 * y0;
    a += f; cx += (x0 + x1) * f; cy += (y0 + y1) * f;
  }
  if (Math.abs(a) < 1e-6) return [p.reduce((s, q) => s + q[0], 0) / p.length, p.reduce((s, q) => s + q[1], 0) / p.length];
  return [cx / (3 * a), cy / (3 * a)];
}

/**
 * Daten Version 1 bereinigen – gleiche Regeln wie Core\Hotspots::normalize (Lage, Formen, Höchstzahl, Kennungen); der Server
 * prüft beim Speichern des Formulars erneut (Rich-Text, Links).
 */
export function normalize(raw, max = 60, maxPoints = 64) {
  let data = raw;
  if (typeof data === 'string') { try { data = JSON.parse(data); } catch { data = null; } }
  const list = Array.isArray(data) ? data : Array.isArray(data?.items) ? data.items : [];
  const items = [], ids = new Set();
  for (const it of list) {
    if (!it || typeof it !== 'object' || items.length >= max) continue;
    let shape = SHAPES.includes(it.shape) ? it.shape : 'point';
    const h = { id: /^[A-Za-z0-9_-]{1,40}$/.test(String(it.id || '')) ? String(it.id) : '', shape };
    if (shape === 'poly') {
      const pts = (Array.isArray(it.points) ? it.points : []).map(p => [Array.isArray(p) ? p[0] : p?.x, Array.isArray(p) ? p[1] : p?.y])
        .filter(([x, y]) => fin(x) && fin(y)).slice(0, maxPoints).map(([x, y]) => [r2(clamp(+x)), r2(clamp(+y))]);
      if (pts.length >= 3) h.points = pts; else shape = h.shape = 'point';
    }
    if (shape !== 'poly') {
      if (!fin(it.x) || !fin(it.y)) continue;
      h.x = r2(clamp(+it.x)); h.y = r2(clamp(+it.y));
    }
    if (shape === 'circle') h.r = r2(Math.max(0.5, fin(it.r) ? clamp(+it.r) : 5));
    if (shape === 'rect') {
      h.w = r2(Math.max(0.5, Math.min(100 - h.x, fin(it.w) ? clamp(+it.w) : 10)));
      h.h = r2(Math.max(0.5, Math.min(100 - h.y, fin(it.h) ? clamp(+it.h) : 10)));
    }
    for (const k of KEYS) h[k] = typeof it[k] === 'string' ? it[k] : (k === 'text' && typeof it.content === 'string' ? it.content : '');
    // Kennung je Bild eindeutig (wie der Core: fehlt sie, entsteht sie aus der Position)
    let id = h.id;
    for (let k = 2; id === '' || ids.has(id); k++) id = (h.id || 'h' + (items.length + 1)) + (id === '' ? '' : '-' + k);
    h.id = id; ids.add(id);
    items.push(h);
  }
  return { v: 1, items };
}

export class HotspotMode {
  constructor(ed) {
    this.ed = ed;
    this.cfg = ed.boot.hs || {};
    this.max = this.cfg.max || 60;
    this.maxPoints = this.cfg.maxPoints || 64;
    this.formFor = null;
    this.timer = null;
  }

  get W() { return this.ed.vb.w; }
  get H() { return this.ed.vb.h; }
  get root() { return this.ed.root; }
  /** Größe eines Punkts im Editor (nur Darstellung) */
  get R() { return Math.max(this.W, this.H) * 0.012; }
  markers() { return [...this.root.querySelectorAll('[data-hs-shape]')].filter(el => !el.closest('defs')); }
  base() { return this.root.querySelector('image[data-hs-base]'); }
  isPoly(el) { return el?.getAttribute?.('data-hs-shape') === 'poly'; }
  /** Punkte haben keine Größe (keine Griffe), Kreise bleiben rund */
  fixedSize(list) { return list.length > 0 && list.every(el => el.getAttribute('data-hs-shape') === 'point'); }
  keepRatio(list) { return list.some(el => el.getAttribute('data-hs-shape') === 'circle'); }

  // ================================================================= Darstellung der Formen
  /** Neue Form des Editors (create) → Markierung */
  decorate(el, shape) {
    el.setAttribute('data-hs-shape', shape);
    if (!el.getAttribute('data-hs-id')) el.setAttribute('data-hs-id', this.freshId());
    this.paint(el);
  }

  paint(el) {
    const point = el.getAttribute('data-hs-shape') === 'point';
    el.setAttribute('fill', point ? '#C2410C' : '#2563EB');
    el.setAttribute('fill-opacity', point ? '1' : '0.22');
    el.setAttribute('stroke', point ? '#FFFFFF' : '#1D4ED8');
    el.setAttribute('stroke-width', String(r2(Math.max(this.W, this.H) * (point ? 0.003 : 0.002))));
    el.setAttribute('vector-effect', 'non-scaling-stroke');
  }

  freshId() {
    const used = new Set(this.markers().map(el => el.getAttribute('data-hs-id')));
    let n = this.markers().length + 1;
    while (used.has('h' + n)) n++;
    return 'h' + n;
  }

  /** Höchstzahl erreicht? (vor dem Zeichnen) */
  full() {
    if (this.markers().length < this.max) return false;
    toast(t('Höchstens {max} Markierungen.', { max: this.max }), 'warn');
    return true;
  }

  /** Werkzeug Punkt: Klick setzt einen Punkt, Ziehen verschiebt ihn gleich weiter */
  pointDown(e, p) {
    const ed = this.ed;
    if (this.full()) return;
    const el = document.createElementNS(NS, 'circle');
    el.setAttribute('cx', String(r2(p.x))); el.setAttribute('cy', String(r2(p.y))); el.setAttribute('r', String(r2(this.R)));
    this.decorate(el, 'point');
    this.root.append(el);
    ed.sel = [el];
    ed.commit(t('Punkt gesetzt'));
    ed.announce(t('Markierung {n} gesetzt: {x} % waagerecht, {y} % senkrecht', { n: this.markers().indexOf(el) + 1, x: Math.round(p.x * 100 / this.W), y: Math.round(p.y * 100 / this.H) }));
    ed.moveDrag(e, p);
  }

  // ================================================================= Daten ↔ Dokument
  /** Markierungen (Format v1) ins Dokument – das Bild bleibt, Protokoll beginnt neu („Geöffnet“) */
  load(data) {
    const { items } = normalize(data, this.max, this.maxPoints);
    const c = this.root.cloneNode(true);
    c.querySelectorAll('[data-hs-shape]').forEach(n => n.remove());
    const W = this.W, H = this.H, X = v => String(v * W / 100), Y = v => String(v * H / 100);
    for (const h of items) {
      let el;
      if (h.shape === 'poly') {
        el = document.createElementNS(NS, 'polygon');
        el.setAttribute('points', h.points.map(([x, y]) => X(x) + ',' + Y(y)).join(' '));
      } else if (h.shape === 'rect') {
        el = document.createElementNS(NS, 'rect');
        el.setAttribute('x', X(h.x)); el.setAttribute('y', Y(h.y)); el.setAttribute('width', X(h.w)); el.setAttribute('height', Y(h.h));
      } else {
        el = document.createElementNS(NS, 'circle');
        el.setAttribute('cx', X(h.x)); el.setAttribute('cy', Y(h.y)); el.setAttribute('r', h.shape === 'circle' ? X(h.r) : String(r2(this.R)));
      }
      el.setAttribute('data-hs-shape', h.shape);
      el.setAttribute('data-hs-id', h.id);
      for (const k of KEYS) if (h[k]) el.setAttribute('data-hs-' + k, h[k]);
      this.paint.call({ W, H }, el);
      c.append(el);
    }
    this.ed.load(new XMLSerializer().serializeToString(c), t('Geöffnet'));
  }

  /** Dokument → Format v1 (Prozent, Reihenfolge der Ebenen = Nummer) */
  data() {
    const W = this.W, H = this.H, px = v => v * 100 / W, py = v => v * 100 / H;
    const list = this.markers().slice(0, this.max).map(el => {
      const shape = SHAPES.includes(el.getAttribute('data-hs-shape')) ? el.getAttribute('data-hs-shape') : 'point';
      const h = { id: el.getAttribute('data-hs-id') || '', shape };
      const b = docBounds(el, this.root) || { x: 0, y: 0, w: 0, h: 0 };
      if (shape === 'poly') {
        h.points = this.points(el).map(([x, y]) => [px(x), py(y)]);
      } else if (shape === 'rect') {
        Object.assign(h, { x: px(b.x), y: py(b.y), w: px(b.w), h: py(b.h) });
      } else {
        Object.assign(h, { x: px(b.x + b.w / 2), y: py(b.y + b.h / 2) });
        if (shape === 'circle') h.r = px(b.w / 2);
      }
      for (const k of KEYS) h[k] = el.getAttribute('data-hs-' + k) || '';
      return h;
    });
    return normalize({ v: 1, items: list }, this.max, this.maxPoints);
  }

  /** Eckpunkte einer Fläche in Bildpixeln (Polygon oder Pfad nach dem Knoten-Werkzeug; Kurven zählen mit ihren Knoten) */
  points(el) {
    const M = docMatrix(el, this.root);
    let raw = [];
    if (el.localName === 'polygon' || el.localName === 'polyline') {
      const nums = (el.getAttribute('points') || '').trim().split(/[\s,]+/).map(Number);
      for (let i = 0; i + 1 < nums.length; i += 2) raw.push([nums[i], nums[i + 1]]);
    } else if (el.localName === 'path') {
      const sub = toNodes(parsePath(el.getAttribute('d') || '')).find(s => s.nodes.length >= 3) || toNodes(parsePath(el.getAttribute('d') || ''))[0];
      raw = (sub?.nodes || []).map(n => [n.x, n.y]);
      if (raw.length > 1 && Math.hypot(raw[0][0] - raw.at(-1)[0], raw[0][1] - raw.at(-1)[1]) < 1e-6) raw.pop();
    } else {
      const b = docBounds(el, this.root);
      return b ? [[b.x, b.y], [b.x + b.w, b.y], [b.x + b.w, b.y + b.h], [b.x, b.y + b.h]] : [];
    }
    return raw.filter(([x, y]) => Number.isFinite(x) && Number.isFinite(y)).slice(0, this.maxPoints).map(([x, y]) => { const q = pt(M, x, y); return [q.x, q.y]; });
  }

  /** Mittelpunkt in Bildpixeln (dort sitzt auf der Website der Knopf mit der Nummer) */
  anchor(el) {
    if (this.isPoly(el)) { const p = this.points(el); if (p.length >= 3) return centroid(p); }
    const b = docBounds(el, this.root);
    return b ? [b.x + b.w / 2, b.y + b.h / 2] : [0, 0];
  }

  /** Nach jeder Änderung: Bild bleibt ganz unten und gesperrt */
  guard() {
    // Kennungen eindeutig (Kopieren/Duplizieren übernimmt data-hs-id)
    const seen = new Set();
    for (const el of this.markers()) {
      const id = el.getAttribute('data-hs-id');
      if (!id || seen.has(id)) el.setAttribute('data-hs-id', this.freshId());
      seen.add(el.getAttribute('data-hs-id'));
    }
    const b = this.base();
    if (!b) return;
    if (b.getAttribute('data-locked') !== '1') b.setAttribute('data-locked', '1');
    const first = [...this.root.children].find(n => n.localName !== 'defs' && n.localName !== 'style' && n.localName !== 'title' && n.localName !== 'desc');
    if (first && first !== b) this.root.insertBefore(b, first);
  }

  // ================================================================= Überlagerung: Nummern
  overlay() {
    const ed = this.ed;
    return this.markers().map((el, i) => {
      const [x, y] = this.anchor(el), q = ed.toOv({ x, y }), on = ed.sel.includes(el) ? ' is-on' : '';
      return `<g class="ge-ov__hs${on}"><circle cx="${q.x}" cy="${q.y}" r="11"/><text x="${q.x}" y="${q.y}" dy="0.35em">${i + 1}</text></g>`;
    }).join('');
  }

  // ================================================================= Seitenleiste
  mountSide(side) {
    this.side = document.createElement('section');
    this.side.className = 'ge-pg ge-pg--hs';
    this.side.setAttribute('aria-labelledby', this.ed.uid + '-hs-h');
    this.side.innerHTML = `<div class="ge-hs">
      <h2 class="ge-hs__h" id="${this.ed.uid}-hs-h">${esc(t('Markierungen'))} <span class="ge-hs__count" data-hs-count></span></h2>
      <p class="ge-hs__hint" data-hs-empty>${esc(t('Mit Punkt, Kreis, Rechteck oder Fläche auf das Bild zeichnen. Die Reihenfolge ist die Nummer.'))}</p>
      <ol class="ge-hs__list" data-hs-list aria-label="${esc(t('Markierungen in der Reihenfolge ihrer Nummer'))}"></ol>
      <div class="ge-hs__form" data-hs-form hidden></div>
    </div>`;
    side.prepend(this.side);
    this.list = this.side.querySelector('[data-hs-list]');
    this.form = this.side.querySelector('[data-hs-form]');
    this.list.addEventListener('click', e => {
      const b = e.target.closest('[data-hs-act]');
      if (!b) return;
      const el = this.markers()[+b.dataset.i];
      if (!el) return;
      const act = b.dataset.hsAct;
      if (act === 'pick') { this.ed.select([el]); this.ed.stage.focus({ preventScroll: true }); return; }
      if (act === 'up' || act === 'down') this.move(el, act === 'up' ? -1 : 1, b);
    });
    this.form.addEventListener('input', e => this.write(e, false));
    this.form.addEventListener('change', e => this.write(e, true));
    this.form.addEventListener('focusout', () => this.flush());
    this.form.addEventListener('click', e => {
      if (e.target.closest('[data-hs-del]')) { this.flush(); this.ed.remove(); this.ed.stage.focus({ preventScroll: true }); }
    });
    this.ed.on('change', () => this.refresh());
    this.ed.on('selection', () => this.refresh());
    this.refresh();
  }

  /** Reihenfolge = Nummer: Markierung eine Stelle nach vorn/hinten */
  move(el, d, btn) {
    const list = this.markers(), i = list.indexOf(el), j = i + d;
    if (j < 0 || j >= list.length) return;
    if (d < 0) list[j].before(el); else list[j].after(el);
    this.ed.sel = [el];
    this.ed.commit(t('Reihenfolge geändert'));
    this.ed.announce(t('Markierung ist jetzt Nummer {n}.', { n: j + 1 }));
    const again = this.list.querySelector(`[data-hs-act="${btn.dataset.hsAct}"][data-i="${j}"]`);
    (again && !again.disabled ? again : this.list.querySelector(`[data-hs-act="pick"][data-i="${j}"]`))?.focus();
  }

  refresh() {
    if (!this.side) return;
    this.guard();
    const list = this.markers(), sel = this.ed.sel;
    this.side.querySelector('[data-hs-count]').textContent = t('{n} von {max}', { n: list.length, max: this.max });
    this.side.querySelector('[data-hs-empty]').hidden = list.length > 0;
    const rows = list.map((el, i) => ({ i, s: el.getAttribute('data-hs-shape'), name: el.getAttribute('data-hs-title') || t('Markierung {n}', { n: i + 1 }), on: sel.includes(el) }));
    // Gleiche Liste (Reihenfolge, Formen, Auswahl): nur Namen nachziehen – die Knöpfe bleiben (ein Klick, der gerade läuft, geht
    // nicht verloren, wenn das Verlassen eines Felds die Angaben ins Protokoll schreibt)
    const key = rows.map(r => r.s + (r.on ? '*' : '')).join(',') + '|' + list.map(el => el.getAttribute('data-hs-id')).join(',');
    if (key === this.listKey) {
      rows.forEach(r => {
        const li = this.list.children[r.i];
        if (!li) return;
        li.querySelector('.ge-hs__name').textContent = r.name;
        li.querySelector('[data-hs-act=up]').setAttribute('aria-label', t('{name} nach vorn (kleinere Nummer)', { name: r.name }));
        li.querySelector('[data-hs-act=down]').setAttribute('aria-label', t('{name} nach hinten (größere Nummer)', { name: r.name }));
      });
    } else {
      this.listKey = key;
      const focus = this.list.contains(document.activeElement) ? [document.activeElement.dataset.hsAct, document.activeElement.dataset.i] : null;
      this.list.innerHTML = rows.map(({ i, s, name, on }) => `<li class="ge-hs__item${on ? ' is-on' : ''}">
        <button type="button" class="ge-hs__pick" data-hs-act="pick" data-i="${i}" aria-pressed="${on}"><span class="ge-hs__num" aria-hidden="true">${i + 1}</span>${ico(ICON[s] || 'map-pin')}<span class="ge-hs__name">${esc(name)}</span><span class="adm-sr">, ${esc(shapeLabel(s))}</span></button>
        <button type="button" class="ge-ib" data-hs-act="up" data-i="${i}"${i === 0 ? ' disabled' : ''} aria-label="${esc(t('{name} nach vorn (kleinere Nummer)', { name }))}" title="${esc(t('Nach vorn'))}">${ico('arrow-up')}</button>
        <button type="button" class="ge-ib" data-hs-act="down" data-i="${i}"${i === rows.length - 1 ? ' disabled' : ''} aria-label="${esc(t('{name} nach hinten (größere Nummer)', { name }))}" title="${esc(t('Nach hinten'))}">${ico('arrow-down')}</button>
      </li>`).join('');
      if (focus) this.list.querySelector(`[data-hs-act="${focus[0]}"][data-i="${focus[1]}"]`)?.focus();
    }
    this.renderForm();
  }

  /** Angaben der gewählten Markierung (Felder des Cores: Rich-Text, Linkauswahl, Symbolauswahl) */
  renderForm() {
    const one = this.ed.sel.length === 1 && isMarker(this.ed.sel[0]) ? this.ed.sel[0] : null;
    if (!one) { this.flush(); this.form.hidden = true; this.form.innerHTML = ''; this.formFor = null; return; }
    const id = one.getAttribute('data-hs-id'), vals = Object.fromEntries(KEYS.map(k => [k, one.getAttribute('data-hs-' + k) || '']));
    const n = this.markers().indexOf(one) + 1, s = one.getAttribute('data-hs-shape');
    if (this.formFor === id) {
      // gleiche Markierung: nur nachziehen, wenn sich die Angaben woanders geändert haben (Rückgängig) und niemand tippt
      this.form.querySelector('[data-hs-title-n]').textContent = t('Markierung {n}', { n }) + ' · ' + shapeLabel(s);
      if (this.form.contains(document.activeElement) || KEYS.every(k => this.value(k) === vals[k])) return;
    }
    this.formFor = id;
    const tpl = document.createElement('template');
    tpl.innerHTML = this.cfg.form || '';
    const box = tpl.content;
    for (const k of KEYS) {
      const inp = box.querySelector(`[name="hs[${k}]"]`);
      if (!inp) continue;
      if (inp.tagName === 'SELECT') {
        if (vals[k] && ![...inp.options].some(o => o.value === vals[k])) inp.add(new Option(vals[k], vals[k]));
        [...inp.options].forEach(o => { o.selected = o.value === vals[k]; o.toggleAttribute('selected', o.value === vals[k]); });
      } else inp.setAttribute('value', vals[k]);
      if (k === 'text') { const a = inp.closest('.rte')?.querySelector('.rte-area'); if (a) a.innerHTML = vals[k]; }
    }
    this.form.innerHTML = `<h3 class="ge-hs__fh" data-hs-title-n>${esc(t('Markierung {n}', { n }) + ' · ' + shapeLabel(s))}</h3>`;
    this.form.append(box);
    this.form.insertAdjacentHTML('beforeend', `<p class="ge-hs__actions"><button type="button" class="adm-btn adm-btn--small adm-btn--ghost" data-hs-del>${ico('trash')} ${esc(t('Markierung löschen'))}</button></p>`);
    this.form.hidden = false;
    window.CMSAdmin?.init?.(this.form);
  }

  value(k) { return this.form.querySelector(`[name="hs[${k}]"]`)?.value ?? ''; }

  /** Eingaben → Angaben der Markierung (sofort), Protokoll gebündelt */
  write(e, now) {
    const name = e.target?.name || '';
    if (!this.formFor || !/^hs\[/.test(name) && !e.target.closest?.('.rte')) return;
    const el = this.markers().find(x => x.getAttribute('data-hs-id') === this.formFor);
    if (!el) return;
    for (const k of KEYS) {
      if (!this.form.querySelector(`[name="hs[${k}]"]`)) continue;
      const v = this.value(k).trim() === '' || (k === 'text' && this.value(k).replace(/<[^>]*>|&nbsp;/g, '').trim() === '') ? '' : this.value(k);
      if (v) el.setAttribute('data-hs-' + k, v); else el.removeAttribute('data-hs-' + k);
    }
    const it = this.list.querySelector(`.ge-hs__item:nth-child(${this.markers().indexOf(el) + 1}) .ge-hs__name`);
    if (it) it.textContent = el.getAttribute('data-hs-title') || t('Markierung {n}', { n: this.markers().indexOf(el) + 1 });
    clearTimeout(this.timer);
    if (now) this.flush(); else this.timer = setTimeout(() => this.flush(), 700);
    this.pending = true;
  }

  flush() {
    clearTimeout(this.timer);
    if (!this.pending) return;
    this.pending = false;
    this.ed.commit(t('Angaben geändert'));
  }

  /** Übernehmen: Daten an das Feld (shell.js → postMessage) */
  apply() {
    this.ed.finishPen(); this.ed.endText(); this.flush();
    const data = this.data();
    if (!this.ed.opts.onApply) { toast(t('Öffnen Sie den Editor über das Feld „Markierungen“ im Block „Interaktives Bild“.'), 'info'); return null; }
    this.ed.savedAt = this.ed.hi;
    this.ed.changed(false);
    this.ed.opts.onApply(data);
    return data;
  }
}
