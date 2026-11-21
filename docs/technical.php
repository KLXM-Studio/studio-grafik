<?php /** Entwicklerhandbuch · Grafik-Editor (Erweiterung „grafik“, Paket klxm/studio-grafik) – Extension::docs('technical'), nach „Medien“ */ ?>
  <p class="lead">Der Grafik-Editor ist ein eigenes Paket (<code>klxm/studio-grafik</code>, Composer-Typ <code>klxm-studio-extension</code>,
    Namensraum <code>Klxm\Grafik</code>). Er dockt nur über die Integrationspunkte von <code>Core\Extension</code> an; Daten und Anzeige
    gehören dem Core (<code>Core\Graphics\Store</code>, <code>Themed</code>, <code>Motion</code>, <code>Bind</code> – Entwicklerhandbuch →
    Medien → „Grafiken anzeigen“).</p>
  <h3 id="grafik-paket">Aufbau des Pakets und Andockstellen</h3>
  <ul>
    <li><b>Dateien:</b> <code>extension.php</code> (Manifest, Routen, Haken), <code>src/</code> (<code>Graphics</code> Ablage und Speichern,
      <code>Compose</code> Bilder mit Foto und Text, <code>Templates</code> Vorlagen, <code>Library</code> Bibliothek, <code>Shared</code>
      Bausteine für alle Websites, <code>Controller</code> Seiten und JSON-API, <code>Grafik</code> Anbindung, <code>SelfTest</code>),
      <code>views/</code>, <code>lang/en.php</code>, <code>docs/</code>, <code>assets/</code> (Quellen) und <code>public/</code> (gebaut,
      <code>php bin/console extensions:publish</code> legt sie nach <code>public/assets/ext/grafik</code>).</li>
    <li><b>Bauen:</b> mit dem Build des Cores (<code>cd tools &amp;&amp; pnpm run build</code>): <code>build.mjs</code> des Pakets kopiert die Lizenzen
      (<code>vendors</code>), setzt Sonderfälle für jsPDF und Paper.js (<code>esbuild</code>) und spiegelt das Ergebnis nach <code>public/</code>
      (<code>built</code>). Abhängigkeiten (Paper.js, paperjs-offset, fontkit, jsPDF, svg2pdf.js, hyphen) stehen in <code>package.json</code> des Pakets;
      Kern-Helfer (<code>_i18n.js</code>, <code>_html.js</code>, <code>_icons.js</code>, <code>_toast.js</code>) kommen über <code>klxm-studio/…</code> ins Bündel.</li>
    <li><b>Routen</b> (<code>routes()</code>, alle mit Recht <code>media.upload</code>, Löschen <code>media.delete</code>, CSRF bei POST): dieselben Adressen wie
      früher im Core – <code>/admin/grafik</code>, <code>/admin/grafik/editor</code>, <code>/admin/api/grafik/*</code>.</li>
    <li><b>Verwaltung:</b> <code>adminAssets()</code> lädt <code>js/grafik-admin.js</code> (Overlay-Lader, Knöpfe <code>data-ge-open</code>,
      <code>CMSAdmin.graphic.open/fill</code>), <code>adminData()</code> liefert Adressen und Rechte (<code>CMSAdmin.extData('grafik')</code>).</li>
    <li><b>Mediathek:</b> <code>mediaJson()</code> → <code>ext.grafik</code> (<code>svg</code>, <code>compose</code>, <code>tpl</code>); im Browser
      <code>CMSMedia.extend()</code> mit den Haken <code>create</code> (Menü „Neu“), <code>edit</code> („Im Grafik-Editor bearbeiten“, „Felder der Vorlage
      ändern“) und <code>side</code> (Link „Grafik-Ablage“). Filter <code>media.usages</code> setzt die Adresse zum Bearbeiten an Bildern mit Foto und Text.</li>
    <li><b>Blockauswahl:</b> <code>previewEditor()</code> (nur mit <code>design.edit</code>) – „Vorschau gestalten“ öffnet
      <code>/admin/grafik/editor?format=preview&amp;block=…&amp;embed=1</code>; die Seite meldet sich mit
      <code>postMessage({source: 'klxm-preview-editor', type: 'saved' | 'close', …})</code>.</li>
    <li><b>Präsenz:</b> Filter <code>presence.object</code> für die Art <code>graphic</code> (<code>media:{id}</code>, <code>graphic:{id}</code>).</li>
    <li><b>Rechte:</b> <code>graphics.templates</code> („Vorlagen für Bilder gestalten“) meldet das Paket an (<code>permissions()</code>); Rollen, die es
      schon hatten, behalten es. Blockvorschauen <code>design.edit</code>, Symbole und Bausteine <code>blocks.build</code>.</li>
    <li><b>Daten</b> bleiben im Core (Tabellen <code>graphics</code>, <code>graphic_versions</code>, <code>graphic_sources</code>,
      <code>graphic_media</code>, <code>graphic_shared</code>; Einstellungen <code>block_previews</code>, <code>graphic_text_styles</code>;
      persönliche Einstellungen <code>ui_prefs.grafik</code>) – das Paket hat keine eigenen Tabellen und keine Migrationen.</li>
  </ul>
  <h3 id="grafik-editor-technik">Editor</h3>
  <p>Vektoreditor (Erweiterung „grafik“, Paket <code>klxm/studio-grafik</code>): Infografiken für die Mediathek, Symbole und Blockvorschauen in einer eigenen Ablage. Eine Komponente,
    zwei Hüllen: eigene Seite <code>/admin/grafik/editor</code> und Overlay (<code>CMSAdmin.graphic.open()</code>, lädt
    <code>public/assets/ext/grafik/js/grafik.js</code> und <code>css/grafik.css</code> erst beim ersten Öffnen).</p>
  <ul>
    <li><b>Engine:</b> eigener Code direkt auf dem SVG-DOM (keine Fremdbibliothek). Das Dokument ist das SVG selbst, eingehängt in
      einen Shadow Root (ein <code>&lt;style&gt;</code> der Grafik wirkt nur dort). Unveränderte Elemente bleiben Zeichen für Zeichen
      erhalten – geöffnet und gespeichert ohne Änderung ergibt dieselbe Datei. Der Shadow Root setzt <code>:host{all:initial;color:#1F2430}</code>:
      die Zeichenfläche erbt weder Schrift, Größe noch Glättung der Verwaltung (Text ohne eigene Schrift sieht aus wie im <code>&lt;img&gt;</code>
      der Website), <code>currentColor</code> ist Tinte – auch im dunklen Modus; eigene Eigenschaften (<code>var(--token)</code>) erben weiter. Transformationen werden, wo es ohne Verlust geht, in die
      Geometrie eingerechnet (Rechteck, Ellipse, Linie, Vieleck, Pfad, Textposition; nicht bei Verläufen in Benutzerkoordinaten,
      Masken, Filtern), sonst als <code>transform</code> gesetzt. Geprüft wurde <code>@svgedit/svgcanvas</code> 7.4 (MIT, 65 KB gzip
      ohne PDF-Export): beim Import verlustbehaftet (viewBox und Einheiten weg, Verläufe umgerechnet, Filter- und Wurzelattribute
      verworfen, Pfade relativ und auf 2 Nachkommastellen) – deshalb nicht verwendet.</li>
    <li><b>Module:</b> <code>geom.js</code> (Matrizen, Pfaddaten lesen/schreiben, Bögen → Kurven, Knotenmodell, Glätten),
      <code>doc.js</code> (Eigenschaften setzen – Attribut oder, wenn eine Klassenregel gewinnt, Stil; Transformation anwenden; ids
      eindeutig machen samt Verweisen), <code>color.js</code> (Farbwähler), <code>panels.js</code> (Bereiche), <code>editor.js</code>
      (Werkzeuge, Auswahl, Griffe, Einrasten, Protokoll, Tastatur, Speichern), <code>shell.js</code> (Overlay und Seite).
      Phase 2: <code>pathops.js</code> (Knoten einfügen per de Casteljau, auftrennen, verbinden, umkehren, Knotenarten – eigener Code),
      <code>bool.js</code> (boolesche Operationen, zusammengesetzte Form, Kontur in Fläche, Versatz, Vereinfachen),
      <code>text.js</code>/<code>textedit.js</code>/<code>textpanel.js</code> (Textmodell mit Läufen, Satz/Umbruch, Bearbeitung auf der Fläche,
      Bereich Zeichen/Absatz), <code>outline.js</code> (Text in Kurven), <code>guides.js</code> (Lineale, Hilfslinien, Raster),
      <code>clip.js</code> (Beschneiden, Masken, Ausstanzen per Maske), <code>gradhandles.js</code> (Verlaufswerkzeug),
      <code>export.js</code> (Web-Optimierung, Desktop-SVG, PDF).
      Ausbaustufe 3a: <code>fontpicker.js</code> (Schriftauswahl), <code>typo.js</code> (OpenType, Absatz-Einstellungen, Silbentrennung),
      <code>styles.js</code> (Absatz-/Zeichenvorlagen), <code>connect.js</code> (Verbinder, Text in Formen, Baum anordnen, Lesereihenfolge).
      Ausbaustufe 3b: <code>scheme.js</code> (Design-Farben, Hell/Dunkel, Ansicht), <code>check.js</code> (Prüfen), <code>builder.js</code> (Formgitter),
      <code>repeat.js</code> (Wiederholen), <code>fx.js</code> (Ebeneneffekte), <code>warp.js</code> (Perspektive, Hülle).
      Ausbaustufe 4: <code>anim.js</code> (Modell der Bewegung, Zeitrechnung, Vorschau, Standbild), <code>motion.js</code> (Persona Motion:
      Zeitleiste, Bereich Bewegung/Interaktion/Szene, Prüfen-Hinweise).</li>
    <li><b>Bibliotheken auf Abruf</b> (eigene ES-Module, per <code>import()</code> erst beim ersten Gebrauch; Adressen in
      <code>boot.modules</code>; Lizenzen in <code>public/assets/ext/grafik/vendor/</code>, siehe <code>THIRD-PARTY-NOTICES.md</code>):
      <table class="doc-table">
        <tr><th>Modul</th><th>Inhalt</th><th>Lizenz</th><th>Größe (min./gzip)</th><th>Warum</th></tr>
        <tr><td><code>js/grafik-paper.mjs</code></td><td>Paper.js 0.12 (nur paper-core) + paperjs-offset 2.2</td><td>MIT</td><td>≈ 228 KB / 80 KB</td>
          <td>Boolesche Operationen direkt auf Bézierkurven (Schnittpunkte der Kurven, kein Umweg über Vielecke – Kreise bleiben Bögen).
          Geprüft und verworfen: polybooljs, martinez, clipper-lib (nur Vielecke, Kurven müssten zerlegt und neu angepasst werden).</td></tr>
        <tr><td><code>js/grafik-fonts.mjs</code></td><td>fontkit 2.0</td><td>MIT</td><td>≈ 357 KB / 150 KB</td>
          <td>liest WOFF2 (Brotli) und variable Schriften – die Dateien des Schriften-Managers reichen, keine TTF-Kopien. opentype.js kann
          kein WOFF2. Zwei Lücken von fontkit bei WOFF2 werden in <code>outline.js</code> umgangen (Varianten über eine eigene Instanz je
          Gewicht, <code>gvar</code> für einfache Glyphen nachgerüstet).</td></tr>
        <tr><td><code>js/grafik-pdf.mjs</code></td><td>jsPDF 4.2 + svg2pdf.js 2.8 (ohne html2canvas/DOMPurify/canvg)</td><td>MIT</td><td>≈ 488 KB / 160 KB</td>
          <td>PDF als Vektor; Texte vorher in Kurven, Filter und Mischmodi fehlen im PDF (Hinweis).</td></tr>
        <tr><td><code>js/grafik-hyph-de.mjs</code>, <code>js/grafik-hyph-en.mjs</code></td><td>hyphen 1.14 + TeX-Trennmuster de-1996 bzw. en-us (hyph-utf8)</td>
          <td>ISC; Muster MIT bzw. frei mit Hinweis</td><td>≈ 288 KB / 103 KB bzw. 48 KB / 18 KB</td>
          <td>Silbentrennung im Textrahmen (Liang-Algorithmus), je Sprache erst beim Einschalten bzw. Öffnen einer Grafik mit Trennung geladen.
          Lizenztexte <code>LICENSE-hyphen.txt</code>, <code>LICENSE-hyphen-patterns.txt</code>.</td></tr>
      </table></li>
    <li><b>Arbeitsfassung und Web-Fassung:</b> <code>POST …/save</code> nimmt <code>svg</code> (Arbeitsfassung des Editors) und <code>web</code>
      (im Browser optimiert). Ausgeliefert wird die Web-Fassung (nach <code>Svg::clean</code>); die Arbeitsfassung mit Ebenennamen,
      <code>data-locked</code> und Editor-Daten <code>data-ge-*</code> (Hilfslinien <code>data-ge-guides="v:120 h:40"</code>, Raster
      <code>data-ge-grid</code>, Textrahmen <code>data-ge-frame</code>, Zeilenabstand <code>data-ge-lh</code>, umbrochene Zeilen
      <code>data-ge-soft</code>, zusammengesetzte Form <code>data-ge-bool</code>/<code>-src</code>/<code>-result</code>, Maskenfarbe
      <code>data-ge-fill</code>) läuft durch <code>Svg::clean($svg, ['editor' => true])</code> (nur Text, kein Markup, keine
      <code>url()</code>) und liegt in <code>graphic_sources</code> (<code>ref</code>, <code>svg</code>, <code>hash</code> = sha1 der Web-Datei).
      Beim Öffnen gilt sie nur, wenn die Datei noch dieselbe ist (<code>Graphics::source()</code>); Symbole/Vorschauen: Spalte
      <code>graphics.svg</code> = Arbeitsfassung, Datei = Web-Fassung.</li>
    <li><b>Regeln der Web-Optimierung</b> (<code>export.js → optimizeSvg</code>): Editor-Daten und ausgeblendete Ebenen weg (außer mit
      Verweis); Standardwerte weg (<code>fill-opacity="1"</code>, <code>stroke-miterlimit="4"</code> … – vererbbare nur, wenn kein
      Vorfahr einen Wert setzt, nichts, sobald ein <code>&lt;style&gt;</code> mitspielt); Zahlen auf N Nachkommastellen (automatisch:
      Fehler ≤ längere Seite / 20 000, Verläufe in Objektkoordinaten mindestens 4); Pfade je Befehl relativ/absolut, H/V, minimale
      Trenner, gegen die gerundete Lage gerechnet; reine <code>translate</code> in die Geometrie (exakt), Drehen/Skalieren bleibt;
      ungenutzte Definitionen, leere Gruppen, Leerraum; kurze Farben. Danach werden vorher/nachher gerendert und verglichen
      (<code>checkSame</code>, Abweichung ≤ 0,15 % der Pixel) – sonst gilt die sichere Stufe (nur Editor-Daten entfernt).
      Texte mit Webschriften auf Wunsch in Kurven (SVG als <code>&lt;img&gt;</code> lädt keine Webschriften).</li>
    <li><b>Desktop-Export</b> (<code>desktopSvg</code>): eingerückt, nicht verkleinert, oberste Gruppen als Inkscape-Ebenen
      (<code>inkscape:groupmode="layer"</code>, <code>inkscape:label</code>), Namen auch als <code>id</code> (Illustrator, Figma) und
      <code>data-name</code>, Sperren als <code>sodipodi:insensitive</code>, <code>textPath</code> zusätzlich mit <code>xlink:href</code>.</li>
    <li><b>Nur Vektoren:</b> <code>Svg::clean</code> zählt entfernte <code>&lt;image&gt;</code>/<code>&lt;feImage&gt;</code> (<code>images</code>,
      auch in der Antwort von <code>…/clean</code>); Import, Ziehen und Einfügen melden Pixelbilder. Kein PNG-Export.</li>
    <li><b>Import und Zwischenablage:</b> <code>importSvg(text, name, { at, inPlace })</code> bereinigt über <code>…/clean</code>, übernimmt die
      Darstellungsattribute der Wurzel (<code>fill</code>, <code>stroke</code>, <code>style</code>, <code>class</code> …) auf die neue Gruppe und
      macht Klassen eines mitgebrachten <code>&lt;style&gt;</code> eindeutig (<code>remapClasses</code> in <code>doc.js</code>, z. B.
      <code>.cls-1</code> → <code>.cls-1-2</code>). <code>at</code> = Mitte an diesem Dokumentpunkt (Datei auf die Fläche gezogen). ⌘C schreibt
      <code>&lt;svg data-ge-clip="1"&gt;</code> mit den verwendeten Definitionen (Verläufe, Marker, Filter, Masken) und der aus Gruppen geerbten
      Darstellung in die Zwischenablage; ein anderer Editor fügt das mit <code>inPlace</code> ohne Hülle an derselben Stelle ein.</li>
    <li><b>Protokoll:</b> jeder Eintrag merkt <code>sel</code> (Auswahl danach) und <code>selBefore</code> (Auswahl davor, Pfade im vorigen
      Stand) – Rückgängig wählt <code>selBefore</code>, Wiederholen <code>sel</code>; eine betretene Gruppe bleibt betreten. Pfeiltasten sammeln
      sich 400 ms zu einem Schritt (<code>flushNudge()</code> vor jedem anderen Schritt und vor ⌘Z). Während der Textbearbeitung hat
      <code>TextEditor</code> ein eigenes Rückgängig (Getipptes in einem Zug, Formatierung, Schreibweise) – das Textfeld kennt nur Getipptes.</li>
    <li><b>Hintergrund von Texten</b> (<code>textbg.js</code>): <code>text[data-ge-bg="p=6;r=4"]</code> (Innenabstand, Eckenradius) und direkt
      davor ein gewöhnliches <code>&lt;rect data-ge-bgfor="text-id"&gt;</code>, das <code>transform</code>, <code>display</code> und Rahmen des
      Texts übernimmt (<code>getBBox()</code>, bei Textrahmen die ganze Breite aus <code>frameSpan()</code>). Farbe, Deckkraft und Rahmen sind
      Füllung/Kontur des Rechtecks (Design- und Dunkel-Farben, <code>backgroundIn()</code> sieht es als Fläche unter dem Text).
      <code>data-ge-bgs</code> spiegelt die Gestaltung („fill=…;stroke=…“, ohne <code>url()</code>), damit Kopien und Einfügen sie mitbringen;
      <code>updateBgs()</code> legt fehlende Rechtecke an und entfernt verwaiste. <code>pickable()</code> leitet einen Klick auf das Rechteck auf den
      Text um. Die Namen sind so gewählt, dass <code>Core\Svg</code> sie in der Arbeitsfassung behält (<code>data-ge-[a-z]+</code>).</li>
    <li><b>Lage von Textrahmen:</b> <code>frameSpan(el)</code> (<code>text.js</code>) – <code>x</code> ist je nach <code>text-anchor</code> linke
      Kante, Mitte oder rechte Kante des Rahmens. Überlagerung (<code>drawTextFrame</code>), Breite ziehen (Mitte: zu beiden Seiten, rechts: Griff
      links) und Trefferprüfung beim Bearbeiten nutzen sie. Das unsichtbare Eingabefeld folgt bei jedem Zeichnen der Schreibmarke
      (<code>TextEditor.placeAt</code>), damit Wortvorschläge und Bildschirmtastatur am Text erscheinen.</li>
    <li><b>Text:</b> Modell aus Absätzen mit Läufen (<code>text.js</code>); geschrieben wird nur, was bearbeitet wird. Zeilen als
      <code>&lt;tspan x dy="1.2em"&gt;</code>, Läufe als <code>&lt;tspan font-weight=…&gt;</code>, Textrahmen bricht mit vom Browser
      gemessenen Breiten um (kein <code>foreignObject</code>), Text auf Pfad als <code>&lt;textPath href&gt;</code>. In Kurven: Positionen je
      Zeichen vom Browser (<code>getStartPositionOfChar</code>, Drehung auf Pfaden), Glyphen von fontkit (Ligaturen per Layout je Lauf).
      Schriftdateien: <code>boot.fontFiles</code> aus <code>Graphics::fontFiles()</code> (Schriften-Manager + Lato der Verwaltung).</li>
    <li><b>Schriftauswahl</b> (<code>fontpicker.js</code>): Kombinationsfeld nach WAI-ARIA (<code>role=combobox</code>,
      <code>aria-activedescendant</code>, zwei Listboxen für Schriften und Schnitte, Abschnitte als <code>role=group</code>). Einträge aus
      <code>boot.fontFiles</code> (jetzt mit <code>axes</code> und <code>category</code> aus dem Manifest des Schriften-Managers) und
      <code>boot.fonts</code>, dazu Systemstapel. Vorschau: <code>ed.textPreview(props|null)</code> setzt Eigenschaften ohne Schritt im
      Protokoll (Schnappschuss, Esc stellt ihn wieder her), Übernehmen über <code>ed.textProps(props)</code> (ein Schritt). Favoriten und
      „Zuletzt verwendet“ je Konto: <code>Core\UiPrefs</code> Schlüssel <code>grafik</code> (<code>fontFav</code>, <code>fontRecent</code>,
      <code>colorMode</code>, <code>libFav</code>, <code>libSiteColors</code>) über <code>POST /admin/api/grafik/prefs {key, value}</code>,
      bereinigt in <code>Graphics::prefs()</code>. Achsen variabler Schriften liest <code>outline.js → fontInfo()</code> per fontkit
      (<code>variationAxes</code>); <code>wght</code> wird <code>font-weight</code>, alle anderen <code>font-variation-settings</code> (nur
      im <code>style</code>, siehe <code>doc.js → CSS_ONLY</code>) – „In Kurven umwandeln“ setzt alle Achsen (<code>fontAt(url, coords)</code>).</li>
    <li><b>OpenType</b> (<code>typo.js</code>): eine Eigenschaft <code>font-feature-settings</code> je Text bzw. Lauf; geschrieben wird nur die
      Abweichung von den Standards (<code>liga</code>, <code>calt</code>, <code>kern</code> an). Welche Merkmale die Schrift hat (GSUB/GPOS,
      Namen der Stilsätze/Zeichenvarianten aus <code>featureParams</code> und der <code>name</code>-Tabelle), liefert <code>fontInfo()</code>.
      <code>ed.textFeature({tag: 1|0|null})</code> führt je Lauf zusammen (markierte Zeichen behalten ihre übrigen Merkmale). Hoch-/Tiefstellen
      ohne <code>sups</code>/<code>subs</code>: <code>baseline-shift="super|sub"</code> + <code>font-size="60%"</code>. In Kurven: dieselben
      Merkmale an <code>font.layout(text, features)</code>; Brüche: fontkit wendet <code>frac</code> nur um U+2044 an – <code>/</code> zwischen
      Ziffern wird dafür ersetzt (gleiche Länge).</li>
    <li><b>Absatz</b> (<code>data-ge-para="al:justify;pb:6;pa:12;li:0;ri:0;fi:16;hy:de"</code>, nur Arbeitsfassung): <code>text.js → layoutText</code>
      bricht mit Einzügen je Zeile um, Silbentrennung teilt ein Wort an der letzten passenden Trennstelle (mind. 2 Zeichen vorn, 3 hinten) und
      schreibt einen echten Trennstrich; die Folgezeile trägt <code>data-ge-soft="h"</code>, <code>readText</code> entfernt den Strich wieder.
      Blocksatz: <code>word-spacing</code> je Zeile (Rest der Breite ÷ Leerzeichen), letzte Zeile eines Absatzes links, Zeilen ohne Leerzeichen
      bleiben. Abstand vor/nach: <code>dy</code> der ersten Zeile eines Absatzes in Einheiten der Zeichenfläche. Die Web-Fassung braucht keine
      dieser Angaben – Positionen, Wortabstände und Trennstriche stehen schon im SVG. H/V skalieren: Skalierung um den Ankerpunkt in
      <code>transform</code>, Wert in <code>data-ge-sx</code>/<code>-sy</code>.</li>
    <li><b>Vorlagen</b> (<code>styles.js</code>): <code>data-ge-styles</code> am <code>&lt;svg&gt;</code> = Base64-JSON
      <code>{p: [{n, props, para}], c: [{n, props}]}</code> (<code>Svg::clean</code> mit <code>editor</code> nimmt hier nur Base64 bis 20 000
      Zeichen); Verwendung am Text <code>data-ge-ps</code>, am Lauf <code>data-ge-cs</code>. Bibliothek der Website: Einstellung
      <code>graphic_text_styles</code> (<code>Graphics::textStyles()</code>, <code>saveTextStyles()</code> bereinigt Eigenschaften und Werte),
      <code>POST /admin/api/grafik/styles</code> mit dem Recht wie eigene Bausteine.</li>
    <li><b>Farbe</b> (<code>color.js</code>): Kanäle für Hex, RGB, HSL, HSB, OKLCH (OKLab nach Ottosson, außerhalb von sRGB wird die
      Buntheit verringert); jede CSS-Farbangabe über den Canvas gelesen. Gespeichert wird Hex + <code>fill-opacity</code>. Kontrast nach
      WCAG zum Hintergrund (Flächen davor in der Zeichenreihenfolge über Weiß). <b>Andockstelle für Design-Farben als Variablen</b>
      (Ausbaustufe 3b): Farbangabe als Objekt <code>{kind: 'value'|'token', value, token, alpha}</code> (<code>paintValue()</code>,
      <code>ed.panels.paintValue(which)</code>, <code>setPaintValue(which, v)</code>); Farbfelder des Designs tragen <code>token</code>
      (<code>Graphics::swatches()</code>), angewendet wird Hex und der Token am Element gemerkt (<code>data-ge-ftoken</code>,
      <code>data-ge-stoken</code>, Bausteine mit „Farben der Website“ <code>data-ge-ctoken</code>; bei <code>&lt;stop&gt;</code> gilt
      <code>-ftoken</code> für <code>stop-color</code>); jede andere Farbe entfernt ihn. Daraus entstehen Hell/Dunkel und die Fassungen je
      Website (nächster Punkt).</li>
    <li id="grafik-dunkel-technik"><b>Design-Farben und Hell/Dunkel</b> (Ausbaustufe 3b, <code>scheme.js</code>, <code>Core\Graphics\Themed</code>):
      <ul>
        <li><b>Modell:</b> das SVG bleibt die helle Fassung. Dunkel steht als Editor-Daten am Element: <code>data-ge-fdark</code> /
          <code>-sdark</code> / <code>-cdark</code> = <code>#RRGGBB</code>, <code>t:token</code> (Design-Farbe nur dunkel) oder <code>=</code>
          (wie hell, auch bei Design-Farben). Ohne Angabe: Design-Farbe → ihr Dunkel-Wert (<code>Graphics::swatches()</code> liefert <code>dark</code>),
          eigene Farbe → gleich. <code>paintIn(ed, el, which, mode)</code> löst Farbe, Vererbung und <code>currentColor</code> je Schema auf.</li>
        <li><b>Ansicht</b> (<code>SchemeView</code>): Umschalter Hell | Dunkel | Nebeneinander (<code>ed.sv.set(mode)</code>, kein Schritt im Protokoll).
          Dunkel = ein <code>&lt;style&gt;</code> im Shadow Root mit Attributselektoren und <code>!important</code> (<code>darkCss()</code>);
          <code>setProp()</code> schaltet es für die Prüfung „gewinnt eine Klassenregel?“ kurz ab (<code>VIEW.sheet</code> in <code>doc.js</code>).
          Nebeneinander: nicht bearbeitbare Kopie rechts (<code>.ge-twin</code>, gedrosselt neu gezeichnet). Hintergründe der Vorschau:
          <code>Graphics::sections()</code> (Abschnitts-Hintergründe des Kits mit Farben aus üblichen Token-Namen, hell/dunkel) plus „Weiß (neutral)“.</li>
        <li><b>Farbe in der Dunkel-Ansicht:</b> <code>setPaint()</code> schreibt die Dunkel-Angabe; <code>finishColor()</code> fragt beim Übernehmen
          „nur Dunkel | beide“ (+ alle gleichen Farben; „nicht mehr fragen“ = <code>ed.darkScope</code>), Abbrechen stellt den Stand davor her.
          <code>suggestDark()</code>: Helligkeit in OKLCH umkehren (L' = 1 − L, begrenzt auf 0,16–0,96), Farbton und Buntheit bleiben.</li>
        <li><b>Web-Fassung:</b> <code>optimizeSvg()</code> schreibt <code>themeClasses()</code> als Klassen: <code>ge-tf-{token}</code>,
          <code>ge-ts-…</code>, <code>ge-tc-…</code> (Design-Farbe), <code>ge-df-{rrggbb}</code>, <code>ge-df-t-{token}</code>, <code>ge-df-same</code>
          (und <code>-ds-</code>, <code>-dc-</code>). Die Hex-Farben bleiben als Rückfall. Desktop-Export: feste Farben; „Dunkel-Fassung“ über
          <code>bakeScheme()</code>. Bausteine: <code>tokensToVars()</code> schreibt <code>var(--token, #hex)</code>, <code>resolveTokens()</code> setzt
          beim Einfügen die Farbe der Ziel-Website ein und merkt den Token wieder (auch <code>stop-color</code>, <code>color</code>, <code>style</code>).</li>
        <li><b>Auslieferung – Entscheidung:</b> Fassungen je Website als Dateien statt CSS-Variablen. Ein SVG im <code>&lt;img&gt;</code> sieht die
          Variablen der Seite nicht; inline eingebettet bräuchte es ein <code>&lt;style&gt;</code> in der Seite (CSP <code>style-src 'self'</code>),
          <code>var()</code> in Darstellungsattributen ist nicht überall verlässlich, ids kollidieren, Browser-Cache und Lazy-Loading fielen weg.
          <code>Themed::variants($m)</code> erzeugt beim ersten Ausgeben <code>{Medienordner}/grafik/t/gt-{id}-{hash}-l.svg</code> und <code>-d.svg</code>
          (Hash über Datei und verwendete Design-Werte; ältere Fassungen derselben Grafik werden dabei gelöscht; Schutz durch <code>Svg::guard</code>
          des Ordners <code>grafik</code>). <code>Themed::render()</code> ändert nur Farben in öffnenden Tags mit diesen Klassen (Attribut bzw. Eigenschaft
          im <code>style</code>), alles andere bleibt bytegleich; unbekannte Tokens behalten die gespeicherte Farbe.</li>
        <li><b>Ausgabe:</b> <code>Media::pictureOf()</code> setzt für solche SVGs die helle Fassung ins <code>&lt;img&gt;</code> und – nur wenn
          <code>Themed::darkActive()</code> (Design mit <code>'dark' =&gt; ['scope' =&gt; …]</code>, alle Klassen des Bereichs am <code>&lt;html&gt;</code>, z. B.
          „Dunkles Farbschema“ an) – <code>&lt;source media="(prefers-color-scheme: dark)" data-scheme="dark"&gt;</code> und die Klasse
          <code>cms-scheme-img</code>. Ohne Skript entscheidet das Gerät. <code>Themed::inject()</code> (SiteController, vor dem Seiten-Cache)
          bindet auf Seiten mit solchen Bildern <code>js/graphic-scheme.js</code> ein (≈ 0,8 KB): helle Schrift an der Stelle des Bildes = dunkler Grund
          (wie Karten und Diagramme) – damit folgen die Bilder dem Hell/Dunkel-Schalter (<code>scheme-dark</code>/<code>scheme-light</code>,
          <code>data-theme</code>) und dunklen Abschnitten einer hellen Seite, auch ohne Neuladen (MutationObserver). Design speichern leert den
          Seiten-Cache; neue Werte ergeben neue Dateinamen.</li>
      </ul></li>
    <li id="grafik-builder-technik"><b>Formgitter</b> (<code>builder.js</code>): zerlegt die Auswahl wie „Teilen“ mit Paper.js in Bereiche (Inseln einer
      zusammengesetzten Fläche einzeln, Treffer über <code>item.contains()</code> im Dokumentraum), Überlagerung <code>.ge-ov__piece</code>. Ergebnis:
      alle Bereiche als Pfade im Raum der untersten Form (<code>pathLike(src, root, d, place)</code>, Stil und Design-Farben des Ursprungs), vereinte
      mit dem Stil des ersten Bereichs; ein Schritt im Protokoll.</li>
    <li id="grafik-repeat-technik"><b>Wiederholen</b> (<code>repeat.js</code>): <code>duplicateAgain()</code> merkt <code>ed.lastDup = {src, copies, T}</code>;
      ⌘D auf die letzte Kopie berechnet einmal den Schritt (Versatz der Mitten, Drehung/Skalierung aus <code>getCTM()</code> – nur was als
      <code>transform</code> am Element steht) und wiederholt ihn. Pfeiltasten-Schritte werden vor jedem anderen Befehl abgeschlossen
      (<code>flushNudge()</code> in Tastatur und Zeiger). Dialog: Kopien mit <code>applyTransform(c, T, root)</code> (Dokumentraum) als Vorschau im DOM,
      Abbrechen entfernt sie, OK fasst zur Gruppe „Wiederholung“ zusammen.</li>
    <li id="grafik-fx-technik"><b>Ebeneneffekte</b> (<code>fx.js</code>): Modell am Filter <code>data-ge-fx="ds:dx=4,dy=6,b=6,c=#000000,o=0.25;!og:b=8,c=…,o=…"</code>
      (<code>!</code> = aus; Arten <code>ds is og ig ol co bl</code>), daraus <code>writeFilter()</code>: nur Schlagschatten → ein <code>feDropShadow</code> (wie
      bisher, ältere Grafiken werden so gelesen); sonst ein Graph aus <code>feGaussianBlur</code>, <code>feOffset</code>, <code>feFlood</code>,
      <code>feComposite</code>, <code>feMorphology</code> (Kontur außen), <code>feComponentTransfer</code> (umgekehrte Alpha für innere Effekte) und
      <code>feMerge</code> in der Reihenfolge Schatten · Leuchten · Kontur · Objekt (bzw. weichgezeichnet) · Farbüberlagerung · innerer Schatten · inneres
      Leuchten, <code>color-interpolation-filters="sRGB"</code>, Filterbereich ±50 %. Alles erlaubt <code>Core\Svg</code> schon – keine Änderung an der
      Bereinigung. Geteilte Filter werden beim Ändern kopiert, fremde Filter (aus Dateien) nicht angefasst. Verlaufsüberlagerung fehlt bewusst
      (bräuchte <code>feImage</code>, das als Pixelbild entfernt wird) – dafür Füllung mit Verlauf.</li>
    <li id="grafik-warp-technik"><b>Verzerren</b> (<code>warp.js</code>): Pfade im Dokumentraum (<code>toNodes(transformPath(…, docMatrix))</code>),
      Abbildung Einheitsquadrat → Viereck: Perspektive projektiv (Homographie nach Heckbert, Strecken bleiben <code>L</code>), Hülle als
      Coons-Fläche aus vier quadratischen Kanten durch die Kantenmitten. Kurven (bei der Hülle auch Strecken) werden vor der Abbildung per
      de Casteljau in 4 bzw. 6 Stücke geteilt, deren Kontrollpunkte abgebildet werden. Ergebnis im Raum des Elternelements ohne <code>transform</code>;
      Rechtecke, Ellipsen … werden Pfade. Sitzung mit Schnappschuss (Esc stellt exakt her), ein Schritt im Protokoll. Neigen: <code>skewX</code> um den
      Bezugspunkt in der Lage des Rahmens.</li>
    <li id="grafik-motion-technik"><b>Bewegung und Interaktion</b> (Ausbaustufe 4, <code>anim.js</code>, <code>motion.js</code>, <code>Core\Graphics\Motion</code>,
      <code>js/graphic-motion.js</code>):
      <ul>
        <li><b>Modell</b> (nur Arbeitsfassung, alles im Dokument – Rückgängig/Wiederholen gelten exakt wie für jede Änderung, Kopieren/Duplizieren
          nimmt die Bewegung mit): Szene am <code>&lt;svg&gt;</code> <code>data-ge-motion="d=4;dl=0;it=1;dir=normal;tr=view"</code> (Dauer, Verzögerung in s,
          Wiederholungen, 0 = endlos, Richtung wie <code>animation-direction</code>, Auslöser <code>load|view|hover|click</code>). Je Element
          <code>data-ge-anim</code> = Base64(JSON) <code>{p: [px, py], k: {pos: [[t, dx, dy, e]], s|r|o|w: [[t, v, e]], f|c: [[t, '#hell', e, '#dunkel']]},
          n?: {a, b, d, g, p, t0, t1, e}, tr?: 'hover', on?: 'hotspot-id'}</code> – Zeiten in Sekunden, Werte relativ zur Grundform (Versatz im Raum des
          Elternelements, Faktor, Grad, Deckkraft-Faktor, Anteil gezeichnet), <code>e</code> = Verlauf ab diesem Schlüsselbild
          (<code>smooth|out|in|linear|spring|hold</code> oder <code>[x1, y1, x2, y2]</code>). <code>p</code> (Drehpunkt = Mitte des Rahmens im Raum des
          Elternelements) und die Lage sichtbarer Tooltips (<code>data-ge-tipbox</code>) leitet der Editor vor jedem <code>getSvg()</code> aus der
          Geometrie ab (ohne Vorschau, Abweichungen unter ¼ % bleiben stehen – Textmaße schwanken mit dem Zoom). Interaktion:
          <code>data-ge-link</code> (<code>page:ID</code> u. a. aus der Linkauswahl, URL, <code>mailto:</code>, <code>tel:</code>), <code>data-ge-linklabel</code>,
          <code>data-ge-tip</code>, <code>data-ge-hot="1"</code>. Animierte/verlinkte Elemente bekommen eine <code>id</code> (bleibt mit <code>keep_ids</code>).
          <code>Svg::clean</code> mit <code>editor</code> nimmt <code>data-ge-anim</code> nur als Base64 bis 120 000 Zeichen.</li>
        <li><b>Rechnung:</b> Bewegung um den Drehpunkt <code>X = T(p)·translate(dx, dy)·rotate(r)·scale(s)·T(−p)</code>; Verlauf je Abschnitt als
          cubic-bezier (<code>bez()</code>, wie CSS). Vorschau im Editor: eigenes <code>&lt;style data-ge-motion-preview&gt;</code> im Shadow Root mit
          <code>transform: matrix(X·M)</code> (M = <code>transform</code>-Attribut), Deckkraft, Farben, Strichlänge aus <code>getTotalLength()</code> –
          das Dokument ändert sich beim Abspielen nicht; Zähler ändern nur den Textknoten und werden vor <code>getSvg()</code> zurückgesetzt; Verbinder
          rechnen während der Vorschau nicht neu. Ein Test vergleicht die angehaltene Website-Animation mit der Editor-Rechnung (Abweichung 0).</li>
        <li><b>Website</b> (<code>Media::pictureOf()</code> → <code>Motion::inline()</code>): Die Web-Fassung trägt an der Wurzel die Klasse <code>ge-anim</code>
          (<code>optimizeSvg</code>); nur dann liest der Server die Arbeitsfassung (<code>Graphics::source()</code>, gilt nur bei gleicher Datei) und baut
          <code>Motion::build()</code>: <code>&lt;style&gt;</code> der Grafik und <code>style</code>-Attribute wandern als Regeln mit Wurzel-Klasse in eine
          CSS-Datei (<code>{Medienordner}/grafik/m/gm-{id}-{hash}.css</code>, Name aus Datei, Modell, Design-Werten und <code>Motion::generator()</code> –
          Fassung plus Prüfsumme von <code>Motion.php</code> und <code>graphic-motion.js</code>, nach einem Update entstehen neue Dateien von selbst;
          Verweise <code>url(#…)</code> aus <code>style</code> werden Darstellungsattribute), Klassen bekommen ein Präfix, ids je Einbindung
          (<code>{K}-id</code>, weitere Einbindungen <code>{K}n2-id</code>, Verweise mit). Bewegte Elemente in <code>&lt;g class="gm-a gm-aN"&gt;</code> mit
          <code>transform-box: view-box; transform-origin: p</code> und CSS <code>translate/rotate/scale/opacity/stroke-dashoffset</code> (eigene
          <code>@keyframes</code> je Eigenschaft, <code>animation-timing-function</code> je Schlüsselbild), Farben am Element (<code>gm-cN</code>, mit
          Dunkel-Keyframes), Linie zeichnen über <code>pathLength="1"</code>. Ruhe = Endzustand; <code>.is-armed</code> Anfang, <code>.is-play</code> spielt,
          <code>.is-off</code> hält an, Hotspots <code>.is-hN</code>/<code>.was-hN</code> (eigene Dauer = letztes Schlüsselbild, aus = rückwärts), Zeigen über
          <code>:hover</code>/<code>:focus-within</code> ohne Skript. <code>@media (prefers-reduced-motion: reduce)</code> und <code>.gm-still</code> (Design-Token
          <code>motion</code> mit Klasse aus, z. B. „Dezente Animationen“) zeigen den Endzustand. Hell/Dunkel: Farben, die sich zwischen
          <code>Themed::render()</code> hell und dunkel unterscheiden, als Regeln unter <code>.is-dark</code> bzw. <code>@media (prefers-color-scheme: dark)</code>
          ohne <code>.is-light</code>; <code>js/graphic-scheme.js</code> setzt die Klassen an <code>svg.cms-scheme-img</code>. Markup im Zwischenspeicher
          <code>storage/…/cache/grafik-motion/</code> (Platzhalter für ids, Links, Alt-Text); Links löst <code>Runtime::resolveLink()</code> bei jeder
          Ausgabe auf. <code>role="img"</code> + <code>aria-label</code> (Alt-Text; dekorativ: <code>aria-hidden</code>), mit Links/Schaltern <code>role="group"</code>,
          Auslöser Klick <code>role="button"</code>; <code>&lt;title&gt;</code>/<code>&lt;desc&gt;</code> bleiben. Links als <code>&lt;a href&gt;</code> mit
          <code>aria-label</code>, Tooltip als <code>&lt;title&gt;</code> plus Schild <code>.gm-tipN</code> (<code>:has(:hover, :focus-visible)</code>), Schalter
          <code>role="button" tabindex="0" aria-pressed</code>. Im Bearbeiten-Modus und mit <code>'static' =&gt; true</code> bleibt es das <code>&lt;img&gt;</code>.</li>
        <li><b>Skript</b> <code>js/graphic-motion.js</code> (≈ 3,6 KB, Modul) nur auf Seiten mit <code>data-gm-js</code> (<code>Motion::inject()</code> im
          SiteController, vor dem Seiten-Cache): sichtbar ab 25 % (IntersectionObserver), Klick/Enter/Leertaste, Hotspots, Zählen (eigener Takt mit
          derselben Zeitrechnung, Format aus <code>data-gm-count</code>), <code>.is-off</code> außerhalb des Bildes; nichts bei „Bewegung reduzieren“ und
          <code>.gm-still</code>. Auslöser „beim Laden“ ohne Schleife und „beim Zeigen“ brauchen kein Skript.</li>
        <li><b>Anhalten (WCAG 2.2.2):</b> <code>Motion::needsPause($scene)</code> – Auslöser Laden/Sichtbar/Klick und <code>dl + d·it &gt; 5 s</code> oder endlos.
          Dann hüllt <code>Motion::inline()</code> das SVG in <code>&lt;span class="cms-gm-box {K}-box"&gt;</code> mit einem echten
          <code>&lt;button class="cms-gm-pause" hidden&gt;</code> (36 px, unten rechts, Farben <code>--kit-bg</code>/<code>--kit-ink</code>/<code>--kit-accent</code>,
          <code>forced-colors</code>) und einer Statusmeldung <code>role="status"</code>; Texte über <code>lt()</code> („Animation anhalten/fortsetzen“,
          „Animation angehalten/läuft“). Das Skript macht den Knopf sichtbar und setzt <code>.is-paused</code> (CSS hält wie <code>.is-off</code> an, Zählen
          pausiert); angehalten bleibt angehalten, auch nach Scrollen. Beim Auslöser „beim Laden“ spielt die Szene mit Knopf über
          <code>.is-armed</code>/<code>.is-play</code> – ohne Skript steht das Endbild. Bei „Bewegung reduzieren“ verschwindet der Knopf. Geprüft in Chromium und
          WebKit (Tastatur, Name, Drehpunkt <code>transform-box: view-box</code>, Dunkel-Fassung, Link-Bereiche, CSP).</li>
        <li><b>Export:</b> Desktop/PDF als Standbild (<code>stillSvg()</code>, Ende bzw. Anfang; schreibt <code>transform</code>, <code>opacity</code>, Farben,
          Strichlänge fest), <b>animiertes SVG</b> über <code>POST /admin/api/grafik/motion-svg {svg, web}</code> → <code>Motion::standalone()</code> (beide
          Fassungen bereinigt, CSS als <code>&lt;style&gt;</code>, Auslöser „beim Laden“, Links absolut, Zählen zeigt den Endwert).</li>
        <li><b>Erweiterung Motion:</b> bleibt unverändert lauffähig. Ihr Block kann künftig eine Mediendatei mit Bewegung über <code>Media::picture()</code>
          ausgeben; eine Übernahme bestehender Szenen ist möglich (Element → Gruppe mit <code>data-ge-anim</code>: Schlüsselbilder <code>t·dur</code>,
          <code>dx/dy</code> → <code>pos</code>, <code>s/r/o/draw</code> → <code>s/r/o/w</code>, Verlauf je Element → je Schlüsselbild; <code>trigger</code> → <code>tr</code>).</li>
        <li><b>Grenzen:</b> keine Pfad-Morphs (CSS <code>d</code> fehlt in Safari) – „Pfad folgen“ tastet den Weg ab (36 Punkte, gleichmäßig über die
          Länge); Skalierung nur gleichmäßig; Zeigen/Klick-Auslöser starten nicht im eigenständigen SVG.</li>
      </ul></li>
    <li id="grafik-daten-technik"><b>Persona Daten</b> (<code>data.js</code>, <code>bind.js</code>, <code>Core\Graphics\Bind</code>):
      <ul>
        <li><b>Modell</b> (nur Arbeitsfassung, <code>Svg::clean</code> mit <code>'editor'</code>, Base64-JSON): an der Wurzel <code>data-ge-data</code>
          <code>{v: {name: {t: 'table', tb, a: count|sum|avg|min|max|latest, f, w: [Feld, op, Wert], l}}}</code>, am Element <code>data-ge-bind</code>
          <code>[{p: text|w|h|r|fill|stroke|o|show, v, …}]</code> – Text <code>tpl</code> (mit <code>{v}</code>), <code>fmt</code> (num, pct, cur, time, raw),
          <code>d</code>; Länge <code>a: [min, max]</code>, Anker <code>o</code> im Raum des Elternelements, <code>c</code> begrenzen; Drehung <code>g: [Grad, Grad]</code>;
          Farbe <code>m: scale|steps</code>, <code>c</code> bzw. <code>s: [[ab, Farbe]]</code>, <code>b</code> Grundfarbe; Sichtbarkeit <code>op</code>, <code>x</code>,
          <code>y</code>. Zustände: <code>@uhrzeit</code> (5-Minuten-Schritte), <code>@tag</code> (<code>date_sun_info</code> am Ort <code>Maps::siteLocation()</code>,
          Vortag/Tag/Folgetag geprüft; ohne Ort 7–19 Uhr), <code>@offen</code> (<code>HeaderActions::ranges()</code>), <code>@wochentag</code>, <code>@monat</code>,
          <code>@datum</code> (MMTT). Anker/Drehpunkt rechnet der Editor vor jedem Protokollschritt nach (<code>syncAnchors</code>, wie der Drehpunkt in Motion).</li>
        <li><b>Website:</b> Die Web-Fassung trägt <code>ge-bound</code> an der Wurzel; gebundene Texte werden beim Speichern nicht in Kurven umgewandelt.
          <code>Media::pictureOf()</code> → <code>Bind::forMedia()</code>: Werte (<code>Bind::current()</code>: alle eigenen Werte + benutzte Zustände, Tabellen über
          <code>Entries::count/query</code> nur veröffentlicht, geschützte und Eingangs-Tabellen liefern nichts), <code>Bind::apply()</code> (Elemente nach id:
          <code>transform</code> vorangestellt – <code>translate·scale·translate</code> bzw. <code>rotate(a x y)</code> –, <code>fill</code>/<code>stroke</code> am Element und
          an Kindern mit eigener Farbe, Design-Klassen <code>ge-[td][fs]-…</code> dieser Eigenschaft weichen, <code>opacity</code>, Sichtbarkeit entfernt das
          Element, Text in den ersten Textknoten, <code>&lt;desc&gt;</code> + Werte), bereinigt als Datei <code>{Medienordner}/grafik/b/gb-{id}-{hash}.svg</code>
          (Hash aus Erzeuger, Datei, Modell, Werten, Sprache; ältere nach einem Tag weg). Der Datensatz zeigt dann auf diese Datei
          (<code>file</code>, <code>_bound</code>, <code>_work</code>) – Design-Farben (<code>Themed</code>) und Bewegung (<code>Motion</code>, Arbeitsfassung aus
          <code>_work</code>) greifen wie sonst. Alt-Text: <code>Bind::alt()</code> ersetzt <code>{name}</code>, sonst werden die Werte angehängt.</li>
        <li><b>Cache:</b> Datentabellen leeren beim Speichern den Seiten-Cache (<code>Entries::changed</code>). Zeitabhängige Zustände melden
          <code>PageCache::until(Bind::nextChange())</code> (nächster Sonnenauf-/-untergang, nächstes Öffnen/Schließen, nächste 5 Minuten, Mitternacht);
          <code>PageCache::put()</code> schreibt <code>{hash}.until</code>, <code>get()</code> erzeugt die Seite nach Ablauf neu. Kein Inline-Skript, kein Polling.</li>
        <li><b>Editor:</b> <code>POST /admin/api/grafik/data-values {defs}</code> → aktuelle Werte für die Vorschau; Boot <code>data</code> =
          <code>Bind::forEditor()</code> (Tabellen mit Feldern, Öffnungszeiten, Sonnenzeiten heute, Zustände jetzt). Vorschau: Kopie mit eingesetzten Werten
          über dem Dokument (<code>applyAll</code>, gleiche Rechnung wie <code>Bind::apply</code>), Dokument bleibt die gezeichnete Fassung.</li>
      </ul></li>
    <li id="grafik-stile-technik"><b>Objektstile</b> (<code>objstyles.js</code>): <code>data-ge-ostyles</code> an der Wurzel (Base64 <code>[{n, props, fx}]</code>,
      <code>props</code> = <code>OBJ_PROPS</code>, <code>fx</code> = Modell der Ebeneneffekte), am Objekt <code>data-ge-os</code> = Name; Anwenden setzt alle
      Eigenschaften (fehlende zurück) und legt je Objekt einen eigenen Filter an. <b>Zeichenflächen</b>: Bereiche mit <code>k: 'board'</code> in
      <code>data-ge-slices</code>, <code>addBoard()</code> erweitert <code>viewBox</code>/<code>width</code>/<code>height</code> nach rechts (Formate
      <code>BOARDS</code>). Symbole mit Instanzen (<code>&lt;symbol&gt;</code>/<code>&lt;use&gt;</code> mit Überschreibungen) sind vorgesehen
      (<code>data-ge-over</code> lässt die Bereinigung schon als Base64 zu), aber noch nicht gebaut.</li>
    <li id="grafik-collab-technik"><b>Zusammenarbeit</b> (<code>collab.js</code>): Fassungen aus <code>graphic_versions</code>
      (<code>Graphics::versions()</code> mit <code>user</code> = wer durch Speichern ersetzt hat), Vergleich nebeneinander bzw. überblendet (zwei
      <code>&lt;img&gt;</code>, <code>clip-path</code> über <code>--x</code>, Regler und Ziehen), „Wiederherstellen“ = <code>load()</code> + <code>commit()</code>.
      <code>GET /admin/api/grafik/usage?ref=…</code> → <code>Graphics::usages()</code> (Mediathek: <code>Media::usages()</code>; eigene Ablage: Zuordnung in der
      Blockauswahl), Vorschau als verkleinertes <code>&lt;iframe&gt;</code> (gleicher Ursprung, erst auf Wunsch). Präsenz: <code>Core\Presence</code> mit Typ
      <code>graphic</code> und id = ref (<code>PresenceController::allowed()</code>: Recht <code>media.upload</code>, Grafik vorhanden; „zuletzt gespeichert“ aus
      <code>graphic_versions</code>), Herzschlag alle 15 s, <code>leave</code> beim Schließen; <code>save()</code> fragt über <code>Collab::confirmSave()</code> nach,
      wenn jemand anderes seit dem Öffnen gespeichert hat.</li>
    <li id="grafik-export-technik"><b>Persona Export</b> (<code>slices.js</code>, <code>raster.js</code>): Bereiche an der Wurzel als
      <code>data-ge-slices</code> = Base64 <code>[{id, n, x, y, w, h, f: [svg|svgd|pdf|ico|app|social|socialjpg], bg, snap: {16: 1, …}}]</code> (nur
      Arbeitsfassung). Überlagerung über den Persona-Haken <code>overlay()</code> in <code>draw()</code>. Vektor-Ausgaben mit <code>viewBox</code> = Bereich
      (<code>optimizeSvg</code>, <code>desktopSvg</code>, <code>pdfBlob</code>); Pixel-Ausgaben – die einzigen des Editors – im Browser: Texte vorher in Kurven,
      <code>renderPng()</code> (SVG → <code>&lt;img&gt;</code> → <code>&lt;canvas&gt;</code>, „contain“, Hintergrund), <code>snapToPixels()</code> (Ecken gerader Kanten und
      Linienstärken auf ganze Pixel der Zielgröße, ungerade Stärken auf halbe; nur ohne Drehung), <code>icoFile()</code> (PNG-Einträge wie
      <code>Core\AppIcons</code>), <code>zipFile()</code> (ohne Kompression, kein Fremdcode). Dateien: <code>-favicon.ico</code>, <code>-favicon-16/32/48.png</code>,
      <code>-apple-touch-icon-180.png</code>, <code>-icon-192/512.png</code>, <code>-icon-maskable-512.png</code>, <code>-social-1200x630.png/.jpg</code>.
      <code>POST /admin/api/grafik/app-icon {png}</code> (zusätzlich <code>system.manage</code>): quadratisches PNG → <code>Media::import</code>,
      <code>sys.icon_mode = image</code>, <code>sys.icon_image</code>, <code>AppIcons::generate()</code> – dieselbe Mechanik wie die Grundeinstellungen.</li>
    <li id="grafik-bild-technik"><b>Persona Bild</b> (Ausbaustufe 6, <code>photo.js</code>, <code>bild.js</code>, <code>host.js</code>, <code>Klxm\Grafik\Compose</code>):
      Fotos sind in der Arbeitsfassung <b>Platzhalter-Rechtecke</b> <code>&lt;rect data-ge-photo="Base64(JSON)"&gt;</code> mit <code>m</code> (Mediathek-ID), <code>z</code>
      (Zoom, 1 = füllt), <code>fx</code>/<code>fy</code> (Lage wie <code>object-position</code> in %, fehlt = Fokuspunkt der Mediathek), <code>b</code>/<code>c</code>/<code>s</code>
      (Helligkeit/Kontrast/Sättigung in %, wie <code>Core\ImageFx</code>), <code>bl</code> (Weichzeichnen) – nie ein <code>&lt;image&gt;</code>, nie Pixel.
      <code>Core\Svg::clean</code> lässt <code>data-ge-photo</code> und <code>data-ge-field</code> nur im Modus <code>editor</code> und nur als Base64 zu; eingebettete
      Bilder lehnt es weiter ab, <code>&lt;image&gt;</code> mit Adresse entfernt es (Website-SVGs bleiben reine Vektoren). Im Editor macht <code>Photos.adopt()</code>
      daraus ein <code>&lt;image&gt;</code> mit einem im Browser zugeschnittenen und korrigierten Bild in Rahmengröße (Object-URL, zwischengespeichert je
      Zustand); <code>getSvg()</code> → <code>toWork()</code> macht wieder den Platzhalter (Protokoll und Speichern bleiben Text). <code>applyTransform</code> rechnet
      Größenänderungen bei <code>image</code> wie bei <code>rect</code> in x/y/width/height ein – der Ausschnitt folgt dem neuen Rahmen.
      Pixel-Ausgabe: <code>renderImage(svg, box, scale, env, {type})</code> – Texte mit Webschriften in Kurven (<code>outlineAll</code>), Fotos in Ausgabegröße
      aus dem Original zugeschnitten (<code>drawCrop</code>, Korrektur als Pixel-Operation wie die CSS-Filter, Weichzeichnen als dreifacher Kastenfilter mit
      fortgesetzten Rändern), SVG → <code>&lt;img&gt;</code> → <code>&lt;canvas&gt;</code>, Faktor 2 (<code>outScale</code>, höchstens <code>Compose::MAX_PX</code> = 4000 px),
      JPEG (weiß) bzw. PNG (<code>data-ge-out="png"</code>). Upload über die Mechanik der Mediathek (<code>POST /admin/media/chunk</code>,
      <code>MediaController::completedUpload()</code>), dann <code>POST /admin/api/grafik/compose {svg, id?, name, title, alt, decorative, outputs: [{board, upload, name}], tpl?, vals?}</code>
      → <code>Compose::save()</code>: Arbeitsfassung in <code>graphics</code> (kind <code>photo</code>, nicht in der Mediathek und nicht in /admin/grafik, Fassungen in
      <code>graphic_versions</code> „graphic:{id}“), je Zeichenfläche ein Medium (<code>Media::import</code> bzw. <code>Media::replace</code> – ID und Verwendungen bleiben),
      Tabelle <code>graphic_media</code> (<code>role</code> out = Ausgabe je <code>board</code>, src = verwendete Fotos, <code>hash</code> der Ausgabe).
      <code>Media::toJson</code> liefert <code>compose</code> (ID der Arbeitsfassung) und <code>compose_tpl</code>; <code>boot?media={id}</code> öffnet bei einer Ausgabe die
      Arbeitsfassung; <code>Media::usages</code>/<code>usedIds</code> zählen die Quellfotos (<code>Compose::usagesOf</code>). <code>GET /admin/api/grafik/photos?ids=</code>
      liefert Anzeige-Adresse (WebP 1600), Original, Maße und Fokuspunkt.
      <b>Host-Anbindung:</b> alles, was die Persona vom CMS braucht, läuft über <code>ed.cms</code> (<code>host.js</code>: <code>can()</code>, <code>tokens()</code>,
      <code>media.pick()</code>, <code>media.info()</code>, <code>upload()</code>, <code>saveImage()</code>, <code>templates.list/save/remove()</code>); eine Einbettung kann
      mit <code>mount(el, boot, { cms })</code> ein eigenes Objekt mitgeben.</li>
    <li id="grafik-formate-technik"><b>Formate</b> (<code>formats.js</code>): Anker am Element <code>data-ge-anchor="h v größe"</code> (h: l|c|r|s, v: t|m|b|s,
      größe: k = skaliert | f = fest; ohne Angabe <code>anchorFor()</code>-Vorschlag aus der Lage, ≥ 90 % der Fläche = wächst mit). <code>addFormat(root, {id, w, h})</code>
      legt <code>data-ge-base</code> (Hauptformat) an, hängt einen Bereich <code>{k: 'board', fmt}</code> an <code>data-ge-slices</code>, kopiert die Ebenen des
      Hauptformats in <code>&lt;g data-ge-board="{id}"&gt;</code> und legt sie mit <code>layout()</code> (Faktor u = min(B/B₀, H/H₀); Texte, Gruppen, Verweise nur
      gleichmäßig) – danach normale Ebenen. Speichern: je Zeichenfläche (<code>boards()</code>) ein Bild, <code>board</code> = „main“ bzw. id des Bereichs.</li>
    <li id="grafik-vorlagen-technik"><b>Vorlagen</b> (<code>fields.js</code>, <code>fill.js</code>, <code>Klxm\Grafik\Templates</code>): Feld am Element
      <code>data-ge-field</code> = Base64 <code>{k, n, t: image|text|color|toggle, ml, max, lines, min, w, fs, on}</code> (gleicher Schlüssel in Kopien = dasselbe Feld).
      Ablage: diese Website <code>graphics</code> (kind <code>template</code>), alle Websites <code>graphic_shared</code> (Spalte <code>kind</code> = template;
      <code>Shared::all()</code> liefert nur Bausteine); Verweis <code>site:{id}</code> | <code>net:{id}</code>. Recht <code>graphics.templates</code> („Vorlagen für Bilder
      gestalten“, Standard nur Administration) für <code>POST /admin/api/grafik/template</code>, <code>…/template/delete</code> und <code>boot?template=</code>;
      <code>GET /admin/api/grafik/templates</code> (Vorlagen mit SVG, Design-Farben, Fotos, Schriften) mit <code>media.upload</code>. Formular:
      <code>CMSGraphicEditor.fill({ id?, template? })</code> – baut je Änderung die Vorschau neu (Vorlage → <code>applyValues()</code> → Formate), Text einpassen mit
      <code>fitText()</code> (4-%-Schritte bis <code>min</code>, Hinweis, wenn auch dann zu lang). <code>Templates::validate()</code> prüft serverseitig (Text ohne Markup,
      Höchstlänge, einzeilig; Bild = Pixelbild der Mediathek; Farbe = Name eines Design-Tokens); gespeichert in <code>graphics.tpl</code>/<code>vals</code>.</li>
    <li id="grafik-pruefen-technik"><b>Prüfen</b> (<code>check.js</code>): <code>analyze(ed)</code> → Hinweise
      <code>{key, sev: error|warn|info, mode: light|dark|null, el, title, detail, fix?}</code>: Titel/Beschreibung/Alt-Text, Kontrast Text 4,5:1 (groß 3:1)
      und Nicht-Text 3:1 (Konturen, Verbinder, kleine Flächen &lt; 4 % der Fläche) je Schema über <code>backgroundIn()</code> (Abschnitts-Hintergrund +
      Flächen davor mit Deckkraft), „verschwindet im Dunkeln“ (hell ≥ 3:1, dunkel &lt; 1,5:1), Schrift &lt; 12 px bei <code>data-ge-dispw</code>
      (vorgesehene Breite, Dokument-Dialog), Lesereihenfolge (Verbinder ohne Ablauf in <code>&lt;desc&gt;</code>; Texte in anderer Reihenfolge als
      sichtbar), dunkle Schatten. Höchstens 800 Elemente je Art. Läuft nach dem Speichern (blockiert nicht, Reiter „Prüfen“) und gedrosselt nach
      Änderungen, solange der Reiter offen ist; <code>checkOverlay()</code> markiert die Stellen der gezeigten Fassung. Alt-Text aus „Prüfen“ geht beim
      nächsten Speichern als <code>alt</code> mit (<code>Media::replace</code>).</li>
    <li><b>Verbinder</b> (<code>connect.js</code>): gewöhnlicher <code>&lt;path&gt;</code> mit Markern; Beziehung in
      <code>data-ge-conn="a=form-1@r;b=form-2@l;r=ortho;m=0.5;hs=none;he=triangle;s=4"</code> (Seite t/r/b/l, <code>a</code> = automatisch,
      <code>n3</code> = Knoten 3; freies Ende <code>@x,y</code>). <code>Connectors.update()</code> läuft vor jedem Zeichnen und jedem
      Protokollschritt (Verbinder neu berechnen, Beschriftung <code>text[data-ge-for]</code> an ihre Stelle – <code>data-ge-at</code> = Lage
      entlang der Linie 0–1, fehlt = Mitte; wurde sie verschoben (x/y bzw. <code>transform</code> anders als zuletzt gesetzt), rastet sie am nächsten
      Punkt der Linie ein und merkt die Lage –, Text in Formen <code>text[data-ge-in]</code> zentrieren und umbrechen, zuletzt Hintergründe von
      Texten). Beschriften: Doppelklick, <kbd>↵</kbd>, Kontextmenü oder Feld <code>[data-conn-label]</code> der Kontextleiste
      (<code>connLabel/connLabelSet/connUnlabel/connLabelCenter</code>). Fehlt die Form, wird das Ende frei (letzte Lage). Spitzen als
      <code>marker</code> je Art/Größe/Farbe (<code>data-ge-head</code>), der Pfad endet vor der Spitze. <code>layoutTree()</code> ordnet
      Bäume (Kinder über Verbinder a → b), <code>describe()</code> liefert die Lesereihenfolge für <code>&lt;desc&gt;</code>.</li>
    <li><b>Bibliothek</b>: <code>Library::all()</code> mit Gruppen Flussdiagramm, Organigramm, Hinweise &amp; Pfeile (Fläche für Text:
      <code>data-ge-box</code>, Platzhalter <code>text</code>, Absätze mit <code>|</code>). Eigene Bausteine: Spalte <code>graphics.folder</code>,
      <code>Graphics::partsForEditor()</code>, <code>updatePart()</code>; für alle Websites: <code>Klxm\Grafik\Shared</code> in der
      Netzwerk-Datenbank (Tabelle <code>graphic_shared</code>, <code>Network::ensureSchema</code>). Austauschdatei
      <code>{format: 'klxm-grafik-bausteine', version: 1, items: [{name, folder, svg}]}</code> – <code>Graphics::importParts()</code> bereinigt
      jedes SVG. Beim Einfügen wird <code>var(--token, ersatz)</code> zur Farbe des Tokens der Website.</li>
    <li><b>Protokoll:</b> jede Änderung speichert das SVG als Text (höchstens 200 Schritte bzw. ca. 40 MB); Rückgängig ersetzt das Dokument.</li>
    <li><b>Speichern:</b> <code>POST /admin/api/grafik/save</code> – das SVG aus dem Browser läuft immer durch
      <code>Core\Svg::clean($svg, ['keep_ids' => true])</code> (ids für Ebenen und spätere Animationen bleiben, <code>data-name</code> =
      Ebenenname). Infografik: <code>Graphics::saveMedia()</code> → <code>Media::import</code> bzw. <code>Media::replace</code> (Option
      <code>graphic</code>: gilt nicht als fremder Upload, „SVG-Grafiken hochladen“ muss nicht an sein). Ablage:
      <code>Graphics::store()</code> → Tabelle <code>graphics</code> (<code>kind</code> icon | preview | part), Datei
      <code>{Medienordner}/grafik/{kind}-{id}-{hash}.svg</code> (neuer Name je Fassung). Beim Überschreiben bleibt die vorige Fassung in
      <code>graphic_versions</code> (<code>ref</code> <code>media:{id}</code> bzw. <code>graphic:{id}</code>, höchstens 20).</li>
    <li><b>Blockvorschauen:</b> <code>Graphics::assignPreview($typ, $id)</code> schreibt die Website-Einstellung <code>block_previews</code> –
      dieselbe Andockstelle wie in <code>Core\Blocks\Picker::overrides()</code> (Kits und Erweiterungen: Filter <code>blocks.previews</code>).
      Schlüssel <code>typ--variante</code> gilt für eine Variante. In der Blockauswahl der Website öffnet „Vorschau gestalten“
      <code>/admin/grafik/editor?format=preview&amp;block=…&amp;variant=…&amp;embed=1</code> in einem <code>&lt;iframe&gt;</code> (eigene
      Verwaltungs-CSP); nach dem Speichern meldet der Editor per <code>postMessage</code> (<code>source: 'klxm-grafik'</code>, gleiche
      Herkunft) die neue Adresse.</li>
    <li><b>Rechte</b> (<code>Graphics::RIGHTS</code>, <code>Graphics::canKind()</code>): Infografiken <code>media.upload</code>;
      Blockvorschauen zusätzlich <code>design.edit</code> (gelten für die ganze Website – Knopf „Vorschau gestalten“, Speichern,
      Zuordnen, Löschen, Öffnen); Symbole und eigene Bausteine zusätzlich <code>blocks.build</code>. Fehlt das Recht: kein Knopf,
      Anfrage → 403.</li>
    <li><b>Routen</b> (alle mit Anmeldung und Recht <code>media.upload</code>, POST mit CSRF; Löschen <code>media.delete</code>; je Art zusätzlich siehe Rechte):
      <code>GET /admin/grafik</code> (Übersicht der Ablage), <code>GET /admin/grafik/editor?format=info|icon|preview&amp;w&amp;h&amp;block</code>
      bzw. <code>?media={id}</code> / <code>?id={id}</code>, <code>GET /admin/api/grafik/boot</code> (gleiche Parameter, JSON für das Overlay),
      <code>POST …/save</code>, <code>…/part</code> (eigener Baustein: <code>scope</code> site|network, <code>folder</code>, <code>id</code> = ersetzen),
      <code>…/part/update</code> (umbenennen, Ordner, <code>action: delete</code>), <code>…/parts/import</code>, <code>…/prefs</code>, <code>…/styles</code>,
      <code>…/clean</code> (nur bereinigen), <code>…/motion-svg</code> (animiertes SVG), <code>…/assign</code>, <code>…/{id}/delete</code>, <code>GET …/version?ref&amp;id</code>.</li>
    <li><b>Einbetten (z. B. Motion):</b>
      <pre><code>const r = await CMSAdmin.graphic.open({
  format: 'info',              // 'info' | 'icon' | 'preview'
  mediaId: 12,                 // oder id (Ablage) oder svg: '&lt;svg …&gt;' (wird vorher bereinigt)
  target: 'none',              // nichts ablegen – onSave bekommt das bereinigte SVG
  onSave: ({ svg, item }) => { … },
});                            // → { saved, svg } beim Schließen

// Ohne Overlay in eine eigene Fläche (nach dem Laden von grafik.js):
const ed = CMSGraphicEditor.mount(el, boot, { onSave });   // boot = JSON von /admin/api/grafik/boot
ed.on('change', e => …); ed.on('selection', list => …); ed.getSvg(); ed.setSvg(svg); ed.root  // das SVG
ed.registerPersona({ id: 'motion', label: 'Motion', icon: 'film-strip', mount(ed, area) { … }, unmount() { … } });</code></pre>
      Personas teilen das Dokument; eine Persona bekommt unter der Zeichenfläche eine eigene Fläche (<code>area</code>, z. B. Zeitleiste) – die
      Persona Motion des Editors nutzt genau diese Stelle (<code>ed.motion</code>, <code>MotionPersona</code>).
      Animationen hängen sich an <code>id</code>s der Elemente – die bleiben beim Speichern erhalten.</li>
    <li><b>Bibliothek:</b> <code>Klxm\Grafik\Library</code> (Bausteine aus Motion), eigene Bausteine (<code>kind = part</code>, nur in der
      Datenbank), Symbole aus <code>public/assets/icons/icons.svg</code>. Farbfelder: Tokens vom Typ <code>color</code> aus
      <code>Core\Design</code>; Schriften: die Schriften des Designs plus allgemeine Familien.</li>
    <li><b>Grenzen heute:</b> keine Bilder in Grafiken (gewollt); Strichmuster und Pfeilspitzen werden bei „Kontur in Fläche“ nicht
      mitgewandelt; Absatz-Einstellungen gelten je Text (nicht je Absatz); Verbinder umgehen keine Hindernisse.</li>
    <li><b>Selbsttest:</b> <code>php bin/console grafik:selftest --site=key</code> (Editor) und <code>graphics:selftest</code> (Anzeige, Core) – Editor-Ausgabe übersteht die Bereinigung (idempotent),
      Schadcode fällt weg, Ablage erscheint nicht in <code>Media::all()</code>, Blockvorschau wirkt in der Blockauswahl (auch je Variante),
      Fassungen, Arbeitsfassung/Web-Fassung, Editor-Daten nur mit <code>editor</code>, Pixelbilder gezählt, Rechte je Art (403);
      Ausbaustufe 3a: Formatvorlagen nur als Base64, Typografie bleibt in beiden Fassungen, persönliche Einstellungen bereinigt,
      Austauschdatei bereinigt bzw. abgelehnt, Ordner/Umbenennen, Formatvorlagen der Website bereinigt;
      Ausbaustufe 3b: Klassen der Design-Farben überstehen die Bereinigung, <code>Themed::render()</code> hell/dunkel (Attribut, <code>style</code>,
      eigene Dunkel-Farbe vor Token, „same“, unbekannter Token, Verlaufsstopp), bytegleich ohne Design-Farben, Fassungen je Website,
      <code>&lt;picture&gt;</code> mit <code>&lt;source&gt;</code> nur bei dunklem Farbschema, Skript nur auf Seiten mit solchen Grafiken;
      Ausbaustufe 4: Bewegung nur als Base64 in der Arbeitsfassung, unsichere Links verworfen, Ausgabe ohne <code>style</code>/<code>&lt;style&gt;</code>,
      ids mit Präfix, Hülle mit Drehpunkt, Links/Tooltips/Schalter, reduzierte Bewegung, eigenständiges SVG, Mediathek inline statt <code>&lt;img&gt;</code>
      (Grafik ohne Bewegung bleibt Bild, <code>'static'</code>), Skript nur bei Bedarf.</li>
  </ul>
