/*
 * Grafik-Editor: Paper.js (MIT, Jürg Lehni & Jonathan Puckey – public/assets/ext/grafik/vendor/LICENSE-paper.txt) als eigenes Modul,
 * nur für Vereinen/Abziehen/Schnittmenge/Ausschluss/Teilen, Vereinfachen, Kontur in Fläche und Pfad versetzen (assets/js/grafik/bool.js lädt es per import()).
 * Nur paper-core (ohne PaperScript, ohne Acorn). MIT
 */
import paper from 'paper';   // = paper-core (Alias in tools/build.mjs)
// Kontur in Fläche und Pfad versetzen: paperjs-offset (MIT, glenzli – public/assets/ext/grafik/vendor/LICENSE-paperjs-offset.txt)
import { offset, offsetStroke } from 'paperjs-offset';

export default paper;
export { offset, offsetStroke };
