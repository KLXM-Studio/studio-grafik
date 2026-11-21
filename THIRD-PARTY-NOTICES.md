# Third-party notices – KLXM Studio Grafik-Editor (`klxm/studio-grafik`)

The extension itself is MIT licensed (see `LICENSE`). Its browser bundles contain the following components. They are built by the
KLXM Studio build (`tools/build.mjs` → `build.mjs` of this package) from the npm packages in `package.json`; the license texts are
written to `public/vendor/` (published to `public/assets/ext/grafik/vendor/`). All are loaded on demand only.

| Component | Version | License | Shipped as | License text |
|---|---|---|---|---|
| Paper.js (`paper`, only `paper-core`), © 2011–2020 Jürg Lehni & Jonathan Puckey – boolean operations on Bézier curves, simplify | 0.12.18 | MIT | `public/js/grafik-paper.mjs` | `public/vendor/LICENSE-paper.txt` |
| paperjs-offset, © 2016–2026 glenzli – offset path, stroke to fill (inside `grafik-paper.mjs`) | 2.2.2 | MIT | `public/js/grafik-paper.mjs` | `public/vendor/LICENSE-paperjs-offset.txt` |
| fontkit, © 2014 Devon Govett – text to curves from the self-hosted WOFF2 fonts (also variable fonts) | 2.0.4 | MIT | `public/js/grafik-fonts.mjs` | `public/vendor/LICENSE-fontkit.txt` (the package ships without one; text in `licenses/LICENSE_FONTKIT.txt`) |
| – bundled: brotli, clone, dfa, fast-deep-equal, pako, restructure, tiny-inflate, unicode-properties, unicode-trie, base64-js (MIT); @swc/helpers (Apache-2.0) | | see left | inside `grafik-fonts.mjs` | `public/vendor/THIRD-PARTY-LICENSES-fontkit.txt` (generated) |
| jsPDF, © 2010–2025 James Hall, yWorks GmbH and contributors – PDF export (vector), without the optional HTML output (html2canvas, DOMPurify, canvg are not bundled) | 4.2.1 | MIT | `public/js/grafik-pdf.mjs` | `public/vendor/LICENSE-jspdf.txt` |
| svg2pdf.js, © yWorks GmbH – SVG → PDF (inside `grafik-pdf.mjs`) | 2.8.1 | MIT | `public/js/grafik-pdf.mjs` | `public/vendor/LICENSE-svg2pdf.txt` |
| – bundled: @babel/runtime, fast-png, fflate, iobuffer, cssesc, font-family-papandreou, specificity, svgpath (MIT); pako (MIT AND Zlib) | | see left | inside `grafik-pdf.mjs` | `public/vendor/THIRD-PARTY-LICENSES-jspdf.txt`, `THIRD-PARTY-LICENSES-svg2pdf.txt` (generated) |
| hyphen, © 2026 Yevhen Tiurin – hyphenation in text frames (Liang algorithm), per language | 1.14.1 | ISC | `public/js/grafik-hyph-de.mjs`, `grafik-hyph-en.mjs` | `public/vendor/LICENSE-hyphen.txt` (text also in `licenses/LICENSE_HYPHEN.txt`) |
| – patterns: hyph-de-1996 © 2013–2024 Deutschsprachige Trennmustermannschaft (MIT); hyph-en-us © 1990, 2004, 2005 Gerard D.C. Kuiken (free copying and distribution, notice preserved) – from hyph-utf8 | | MIT / permissive | inside `grafik-hyph-de.mjs` / `grafik-hyph-en.mjs` | `public/vendor/LICENSE-hyphen-patterns.txt` |

The admin helpers of KLXM Studio (`_i18n.js`, `_html.js`, `_icons.js`, `_toast.js`, MIT) are bundled into `grafik.js` and
`grafik-admin.js`. Icons come from the icon sprite of the core (Phosphor Icons, MIT).
