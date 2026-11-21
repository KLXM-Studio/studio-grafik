/*
 * Grafik-Editor in der Verwaltung (Erweiterung „grafik“) – kleines Skript auf jeder Seite (Extension::adminAssets), der Editor
 * selbst (grafik.js + grafik.css) lädt erst beim ersten Öffnen. Angaben kommen vom Server (Extension::adminData →
 * CMSAdmin.extData('grafik'): js, css, boot, store, page, kinds).
 *
 *   CMSAdmin.graphic.open(opts)  → Promise<{ saved, svg }>   Overlay (Mediathek, Blockauswahl, andere Erweiterungen)
 *   CMSAdmin.graphic.fill(opts)  → Promise<{ saved }>        Vorlage ausfüllen (Persona Bild)
 *   Knöpfe mit data-ge-open (data-format, data-id, data-media, data-block, data-block-from, data-template, data-w, data-h,
 *   data-out, data-persona) öffnen das Overlay.
 *
 * Mediathek (window.CMSMedia.extend, Haken create, edit, side): Menü „Neu“ (Infografiken, Bild mit Foto und Text, Aus Vorlage,
 * Symbol, Blockvorschau, Grafiken verwalten), „Im Grafik-Editor bearbeiten“ für SVGs und Bilder aus dem Editor, „Felder der
 * Vorlage ändern“, Link „Grafik-Ablage“ in der Seitenleiste. MIT
 */
import { toast } from 'klxm-studio/_toast.js';
import { t } from 'klxm-studio/_i18n.js';

const cfg = () => window.CMSAdmin?.extData?.('grafik') || {};

let loading = null;
function load() {
  if (window.CMSGraphicEditor?.open) return Promise.resolve(window.CMSGraphicEditor);
  const c = cfg();
  if (!c.js) return Promise.reject(new Error(t('Für den Grafik-Editor fehlt Ihrer Rolle die Berechtigung.')));
  return (loading ||= new Promise((res, rej) => {
    if (c.css && !document.querySelector('link[data-grafik-css]')) {
      const l = document.createElement('link');
      l.rel = 'stylesheet'; l.href = c.css; l.dataset.grafikCss = '1';
      document.head.append(l);
    }
    const s = document.createElement('script');
    s.src = c.js; s.defer = true;
    s.onload = () => res(window.CMSGraphicEditor);
    s.onerror = () => { loading = null; rej(new Error(t('Der Grafik-Editor konnte nicht geladen werden.'))); };
    document.head.append(s);
  }));
}

export async function openGraphicEditor(opts = {}) {
  try {
    const api = await load();
    return await api.open(opts);
  } catch (e) {
    toast(e.message, 'error');
    return { saved: null, svg: null };
  }
}

/** Vorlage ausfüllen (Persona Bild): Formular mit Vorschau, fertige Bilder in der Mediathek → Promise<{ saved }> */
export async function fillGraphicTemplate(opts = {}) {
  try {
    const api = await load();
    return await api.fill(opts);
  } catch (e) {
    toast(e.message, 'error');
    return { saved: null };
  }
}

// Knöpfe data-ge-open (Grafik-Ablage, Erweiterungen)
document.addEventListener('click', e => {
  const b = e.target.closest?.('[data-ge-open]');
  if (!b || e.metaKey || e.ctrlKey) return;
  e.preventDefault();
  const d = b.dataset;
  // Übersicht „Grafiken“: nach dem Speichern neu laden, damit die Liste stimmt
  const block = d.blockFrom ? document.querySelector(d.blockFrom)?.value : d.block;
  openGraphicEditor({ format: d.format, id: d.id, mediaId: d.media, block, template: d.template, w: d.w, h: d.h, out: d.out, persona: d.persona, returnFocus: b })
    .then(r => { if (r.saved && b.closest('.ge-store, .adm-head')) location.reload(); });
});

// Öffentliche Schnittstelle (vorher Teil des Cores): CMSAdmin.graphic.open({ format, mediaId, id, block, svg, target, onSave })
if (window.CMSAdmin) window.CMSAdmin.graphic = { open: openGraphicEditor, fill: fillGraphicTemplate };

// ---------------------------------------------------------------- Mediathek
const ext = m => m?.ext?.grafik || null;

window.CMSMedia?.extend?.({
  name: 'grafik',
  // Neu → Grafik: Infografik in die Mediathek, Symbol/Blockvorschau in die eigene Ablage, Bilder mit Foto und Text, Vorlagen
  create(finder) {
    const c = cfg();
    if (!c.js) return [];
    const kinds = c.kinds || [];
    const btn = () => finder.root.querySelector('[data-newmenu]');
    const graphic = async opts => {
      const r = await openGraphicEditor({ returnFocus: btn(), ...opts });
      // Infografiken erscheinen danach ausgewählt in der Mediathek (im Auswahldialog direkt wählbar)
      if (r.saved?.target === 'media') finder.load(r.saved.item.id);
    };
    const info = (label, w, h) => ({ label, run: () => graphic({ format: 'info', w, h }) });
    return [
      info(t('Infografik 16:9 (1600 × 900)'), 1600, 900),
      info(t('Infografik quadratisch (1080 × 1080)'), 1080, 1080),
      info(t('Infografik frei (800 × 500)'), 800, 500),
      // Persona Bild: Foto und Text gemischt → Pixelbild; Vorlagen der Agentur nur ausfüllen
      { label: t('Bild mit Foto und Text (1200 × 630)'), run: () => graphic({ format: 'info', w: 1200, h: 630, out: 'jpeg', persona: 'bild' }) },
      { label: t('Aus Vorlage erstellen …'), run: async () => {
        const r = await fillGraphicTemplate({ returnFocus: btn() });
        const first = r.saved && Object.values(r.saved.outputs || {})[0];
        if (first) finder.load(first.id);
      } },
      // Symbole/Blockvorschauen nur mit dem Recht der Art (Graphics::RIGHTS)
      ...(kinds.includes('icon') ? [{ label: t('Symbol 24 × 24 (eigene Ablage)'), run: () => graphic({ format: 'icon' }) }] : []),
      ...(kinds.includes('preview') ? [{ label: t('Blockvorschau 160 × 100 (eigene Ablage)'), run: () => graphic({ format: 'preview' }) }] : []),
      { label: t('Grafiken verwalten …'), run: () => { location.href = c.store; } },
    ];
  },
  // „Im Grafik-Editor bearbeiten“: SVG der Mediathek (neue Fassung derselben Datei – ID und Verwendungen bleiben) bzw. Bild
  // aus dem Editor (Arbeitsfassung öffnen, Speichern ersetzt das Bild); Vorlage: Felder ändern
  edit(finder, m) {
    const g = ext(m);
    if (!g || !cfg().js || (!g.svg && !g.compose)) return [];
    const out = [{
      label: t('Im Grafik-Editor bearbeiten'), menuLabel: t('Im Grafik-Editor bearbeiten …'), icon: 'bezier-curve',
      hint: g.compose ? t('Bild aus dem Grafik-Editor: Ebenen und Texte ändern Sie dort – Speichern ersetzt dieses Bild.') : '',
      run: async (f, file, ctx) => {
        const r = await openGraphicEditor(g.compose ? { id: g.compose } : { mediaId: file.id });
        if (!r.saved) return;
        ctx?.close?.();
        f.load(r.saved.item?.id || file.id);
      },
    }];
    if (g.tpl) out.push({ label: t('Felder der Vorlage ändern …'), detail: false, run: async f => {
      const r = await fillGraphicTemplate({ id: g.compose });
      const first = r.saved && Object.values(r.saved.outputs || {})[0];
      if (first) f.load(first.id);
    } });
    return out;
  },
  // Seitenleiste: eigene Ablage (Symbole, Blockvorschauen, Bausteine – nicht in der Mediathek)
  side() {
    const c = cfg();
    return c.store ? [{ label: t('Grafik-Ablage'), href: c.store, icon: 'pen-nib', title: t('Symbole, Blockvorschauen und Bausteine des Grafik-Editors – nicht in der Mediathek') }] : [];
  },
});
