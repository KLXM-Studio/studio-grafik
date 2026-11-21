/*
 * Grafik-Editor – Hintergrund für Texte (freier Text, Text in Formen, Beschriftungen von Verbindern): ein gewöhnliches <rect>
 * direkt vor dem Text, das Größe, Lage und Drehung des Texts übernimmt (Textänderung, Schriftgröße, Textrahmen, Verschieben,
 * Skalieren, Drehen). In der Web-Fassung und im Export ist es ein normales Rechteck – die Anzeige braucht nichts weiter.
 *
 *   <rect data-ge-bgfor="text-3" fill="#FFF3C4" rx="4" …/>   Hintergrund (Farbe, Deckkraft, Rahmen = Füllung/Kontur des Rechtecks)
 *   <text id="text-3" data-ge-bg="p=6;r=4" data-ge-bgs=…>…</text>  p = Innenabstand, r = Eckenradius (Einheiten des Texts);
 *                                                             data-ge-bgs = Abbild der Gestaltung des Rechtecks („fill=…;stroke=…“),
 *                                                             damit Kopien, Einfügen und Bausteine den Hintergrund mitbringen
 * Namen und Werte so, dass die Arbeitsfassung sie behält (Core\Svg: data-ge-[a-z]+, Text ohne Anführungszeichen und url()).
 *
 * Die Farbe des Rechtecks ist eine gewöhnliche Füllung: Design-Farben, Dunkel-Farben und die Kontrastprüfung (Fläche unter dem
 * Text, check.js) gelten wie für jede Form. MIT
 */
import { NS, num } from './geom.js';
import { uid } from './doc.js';
import { frameSpan } from './text.js';

export const BG_DEFAULT = { p: 6, r: 4, fill: '#FFFFFF', opacity: 1, stroke: 'none', sw: 1 };

/** Lage und Verknüpfung – alles andere am Rechteck ist Gestaltung (Füllung, Kontur, Dunkel-Farben …) */
const GEOM = new Set(['x', 'y', 'width', 'height', 'rx', 'ry', 'transform', 'display', 'visibility', 'data-ge-bgfor', 'id']);

/** Gestaltung des Rechtecks als „name=wert;…“ (ohne Lage, ohne url()-Werte) bzw. zurück */
const KEY = /^[a-z][a-z0-9-]{0,40}$/;
const writeStyle = r => [...r.attributes].filter(a => !GEOM.has(a.name) && KEY.test(a.name) && !/[;=<>"]|url\(/.test(a.value)).map(a => `${a.name}=${a.value}`).join(';');
function readStyle(text) {
  return String(text.getAttribute('data-ge-bgs') || '').split(';').map(p => { const i = p.indexOf('='); return i > 0 ? [p.slice(0, i), p.slice(i + 1)] : null; })
    .filter(p => p && KEY.test(p[0]) && !GEOM.has(p[0]) && !/^on/i.test(p[0]));
}

/** neues Rechteck für einen Text (Gestaltung aus data-ge-bgs, sonst Vorgabe) */
function makeRect(text, fill) {
  const r = document.createElementNS(NS, 'rect');
  const st = readStyle(text);
  if (st.length) for (const [k, v] of st) r.setAttribute(k, v);
  else { r.setAttribute('data-name', 'Texthintergrund'); r.setAttribute('fill', fill || BG_DEFAULT.fill); }
  r.setAttribute('data-ge-bgfor', text.id);
  text.before(r);
  return r;
}

export const hasBg = el => el?.localName === 'text' && el.hasAttribute('data-ge-bg');
export const isBgRect = el => el?.localName === 'rect' && el.hasAttribute('data-ge-bgfor');

/** data-ge-bg lesen: { p, r } */
export function parseBg(el) {
  const o = { p: BG_DEFAULT.p, r: BG_DEFAULT.r };
  for (const part of String(el?.getAttribute('data-ge-bg') || '').split(';')) {
    const [k, v] = part.split('=');
    if ((k === 'p' || k === 'r') && Number.isFinite(parseFloat(v))) o[k] = Math.max(0, parseFloat(v));
  }
  return o;
}

/** Rechteck zum Text (oder null) */
export function bgRect(root, text) {
  return text?.id ? root.querySelector(`rect[data-ge-bgfor="${CSS.escape(text.id)}"]`) : null;
}

/** Text zum Rechteck (oder null) */
export function bgText(root, rect) {
  const id = rect?.getAttribute('data-ge-bgfor');
  const t = id && root.querySelector(`#${CSS.escape(id)}`);
  return t && t.localName === 'text' ? t : null;
}

/** Stand für die Bereiche: { on, p, r, fill, opacity, stroke, sw } */
export function bgState(root, text) {
  if (!hasBg(text)) return { on: false, ...BG_DEFAULT };
  const r = bgRect(root, text), o = parseBg(text);
  const g = (a, d) => r?.getAttribute(a) ?? d;
  return { on: true, p: o.p, r: o.r, fill: g('fill', BG_DEFAULT.fill), opacity: parseFloat(g('fill-opacity', '1')) || 0,
    stroke: g('stroke', 'none'), sw: parseFloat(g('stroke-width', '1')) || 1 };
}

/**
 * Hintergrund ein-/ausschalten bzw. ändern. patch: { on, p, r, fill, opacity, stroke, sw } (fehlende Werte bleiben).
 * Rückgabe: das Rechteck (oder null, wenn aus).
 */
export function setBg(root, text, patch = {}) {
  if (patch.on === false) {
    bgRect(root, text)?.remove();
    text.removeAttribute('data-ge-bg'); text.removeAttribute('data-ge-bgs');
    return null;
  }
  if (!text.id) text.id = uid(root, 'text-');
  const o = { ...parseBg(text) };
  if (patch.p != null) o.p = Math.max(0, +patch.p || 0);
  if (patch.r != null) o.r = Math.max(0, +patch.r || 0);
  text.setAttribute('data-ge-bg', `p=${num(o.p, 2)};r=${num(o.r, 2)}`);
  let r = bgRect(root, text);
  if (!r) { text.removeAttribute('data-ge-bgs'); r = makeRect(text, patch.fill); }
  if (patch.fill) r.setAttribute('fill', patch.fill);
  if (patch.opacity != null) { const a = Math.max(0, Math.min(1, +patch.opacity)); a >= 1 ? r.removeAttribute('fill-opacity') : r.setAttribute('fill-opacity', num(a, 3)); }
  if (patch.stroke != null) {
    if (!patch.stroke || patch.stroke === 'none') { r.removeAttribute('stroke'); r.removeAttribute('stroke-width'); }
    else r.setAttribute('stroke', patch.stroke);
  }
  if (patch.sw != null && r.hasAttribute('stroke')) r.setAttribute('stroke-width', num(Math.max(0, +patch.sw || 0), 2));
  fitBg(root, text, r);
  return r;
}

/** Rechteck an den Text anpassen (Lage, Größe, Drehung, Reihenfolge direkt vor dem Text) */
export function fitBg(root, text, r = bgRect(root, text)) {
  if (!r) return;
  if (r.nextElementSibling !== text) text.before(r);
  const tr = text.getAttribute('transform');
  if (tr) { if (r.getAttribute('transform') !== tr) r.setAttribute('transform', tr); } else r.removeAttribute('transform');
  for (const a of ['display', 'visibility']) { const v = text.getAttribute(a); if (v) r.setAttribute(a, v); else r.removeAttribute(a); }
  let b;
  try { b = text.getBBox(); } catch { return; }
  const o = parseBg(text);
  let x0 = b.x, x1 = b.x + b.width;
  const f = frameSpan(text);
  if (f) { x0 = Math.min(x0, f.x0); x1 = Math.max(x1, f.x1); }
  // leerer Text (gerade angelegt): Höhe einer Zeile
  let y0 = b.y, h = b.height;
  if (!h) { const fs = parseFloat(text.getAttribute('font-size')) || 16, y = parseFloat(text.getAttribute('y')) || 0; y0 = y - fs * 0.9; h = fs * 1.15; }
  // Gestaltung am Text spiegeln (Kopie/Einfügen eines Texts ohne sein Rechteck)
  const js = writeStyle(r);
  if (text.getAttribute('data-ge-bgs') !== js) text.setAttribute('data-ge-bgs', js);
  const set = (a, v) => { const s = num(v, 2); if (r.getAttribute(a) !== s) r.setAttribute(a, s); };
  set('x', x0 - o.p); set('y', y0 - o.p); set('width', x1 - x0 + o.p * 2); set('height', h + o.p * 2);
  if (o.r) { set('rx', o.r); r.removeAttribute('ry'); } else { r.removeAttribute('rx'); r.removeAttribute('ry'); }
}

/** Alle Hintergründe nachführen, verwaiste entfernen (vor jedem Zeichnen und Protokollschritt) */
export function updateBgs(root) {
  const texts = root.querySelectorAll('text[data-ge-bg]');
  const rects = root.querySelectorAll('rect[data-ge-bgfor]');
  if (!texts.length && !rects.length) return;
  const seen = new Set();
  for (const r of rects) {
    const t = bgText(root, r);
    if (!t || !hasBg(t) || seen.has(t)) { r.remove(); continue; }   // Text gelöscht, Hintergrund aus, Doppel nach Kopieren
    seen.add(t);
  }
  for (const t of texts) {
    if (!t.id) t.id = uid(root, 'text-');
    let r = bgRect(root, t);
    // z. B. Text dupliziert oder eingefügt (neue id): Hintergrund mit der Gestaltung des Originals
    if (!r) r = makeRect(t);
    fitBg(root, t, r);
  }
}
