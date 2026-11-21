/**
 * Build-Haken des Grafik-Editors für den Build des Cores (tools/build.mjs, `cd tools && pnpm run build`):
 *   vendors(ctx)  Lizenzen der gebündelten Bibliotheken → public/assets/ext/grafik/vendor/
 *   esbuild(src)  Sonderfälle je Quelle: jsPDF ohne die optionale HTML-Ausgabe, Paper.js nur als paper-core
 *   built(ctx)    gebaute Dateien nach public/ dieses Pakets spiegeln – auf dem Server legt `php bin/console extensions:publish`
 *                 sie nach public/assets/ext/grafik (ohne Node)
 * Quellen: assets/js/grafik.js (Editor), grafik-admin.js (Verwaltung), grafik-{paper,fonts,pdf,hyph-de,hyph-en}.mjs (auf Abruf),
 * assets/css/grafik.css. Abhängigkeiten aus package.json dieses Pakets (der Build des Cores installiert sie bei Bedarf). MIT
 */
import fs from 'node:fs';
import path from 'node:path';

export function esbuild(src) {
  const base = path.basename(src);
  return {
    // grafik-pdf.mjs (jsPDF): optionale HTML-Ausgabe von jsPDF (html2canvas, DOMPurify, canvg) wird nie aufgerufen – nicht mitbündeln
    external: base === 'grafik-pdf.mjs' ? ['html2canvas', 'dompurify', 'canvg', 'core-js'] : [],
    // paperjs-offset importiert „paper“ (paper-full mit PaperScript/Acorn) – der Editor braucht nur paper-core
    alias: { paper: 'paper/dist/paper-core.js' },
  };
}

export function vendors({ copy, pkg, licenses, dir, publicDir }) {
  const v = path.join(publicDir, 'vendor');
  copy(pkg('paper/LICENSE.txt'), path.join(v, 'LICENSE-paper.txt'));
  copy(pkg('paperjs-offset/LICENSE'), path.join(v, 'LICENSE-paperjs-offset.txt'));
  copy(path.join(dir, 'licenses/LICENSE_FONTKIT.txt'), path.join(v, 'LICENSE-fontkit.txt'));   // Paket ohne Lizenzdatei
  licenses(fs.realpathSync(pkg('fontkit')), path.join(v, 'THIRD-PARTY-LICENSES-fontkit.txt'));
  copy(pkg('jspdf/LICENSE'), path.join(v, 'LICENSE-jspdf.txt'));
  copy(pkg('svg2pdf.js/LICENSE'), path.join(v, 'LICENSE-svg2pdf.txt'));
  licenses(fs.realpathSync(pkg('jspdf')), path.join(v, 'THIRD-PARTY-LICENSES-jspdf.txt'));
  licenses(fs.realpathSync(pkg('svg2pdf.js')), path.join(v, 'THIRD-PARTY-LICENSES-svg2pdf.txt'));
  // Silbentrennung in Textrahmen (grafik-hyph-{de,en}.mjs): hyphen (ISC) + TeX-Trennmuster (de-1996 MIT, en-us frei mit Hinweis)
  copy(path.join(dir, 'licenses/LICENSE_HYPHEN.txt'), path.join(v, 'LICENSE-hyphen.txt'));
  copy(path.join(dir, 'licenses/LICENSE_HYPHEN_PATTERNS.txt'), path.join(v, 'LICENSE-hyphen-patterns.txt'));
}

/** public/assets/ext/grafik → {paket}/public (gleicher Stand: veraltete Dateien fallen weg) */
export function built({ dir, publicDir }) {
  const out = path.join(fs.realpathSync(dir), 'public');
  const want = new Set();
  const walk = (from, rel = '') => {
    for (const e of fs.readdirSync(path.join(from, rel), { withFileTypes: true })) {
      const r = path.join(rel, e.name);
      if (e.isDirectory()) { walk(from, r); continue; }
      want.add(r);
      fs.mkdirSync(path.join(out, rel), { recursive: true });
      fs.copyFileSync(path.join(from, r), path.join(out, r));
    }
  };
  walk(publicDir);
  const prune = (rel = '') => {
    const d = path.join(out, rel);
    if (!fs.existsSync(d)) return;
    for (const e of fs.readdirSync(d, { withFileTypes: true })) {
      const r = path.join(rel, e.name);
      if (e.isDirectory()) prune(r);
      else if (!want.has(r)) fs.unlinkSync(path.join(out, r));
    }
  };
  prune();
  console.log(`  gespiegelt → ${out} (${want.size} Dateien)`);
}
