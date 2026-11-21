<?php
/**
 * Handbuch · Kapitel „Grafiken zeichnen und bearbeiten (Grafik-Editor)“ – Erweiterung „grafik“ (Extension::docs('manual'), nach „Bilder & Dateien“).
 * Anker wie früher im Core (#grafik-editor, #grafik-motion …), damit Verweise weiter stimmen.
 */
?>
  <p class="lead">Der Grafik-Editor ist eine Erweiterung: Die Administration schaltet ihn unter <b>Administration → Funktionen &amp; Erweiterungen</b> ein
    (Paket „Grafik-Editor“). Ohne ihn erscheinen vorhandene Grafiken auf der Website unverändert – nur Zeichnen und Bearbeiten entfallen.</p>
  <p>Infografiken, einfache Schaubilder, Symbole und Skizzen für die Blockauswahl entstehen direkt in der Verwaltung – ohne
    Grafikprogramm. Der Editor ist aufgebaut wie Affinity Designer, Illustrator oder Fireworks: oben die <b>Personas</b>
    (<b>Vektor</b> zum Zeichnen, <b>Bild</b> für Fotos mit Text als Pixelbild (<a href="#grafik-bild">unten</a>), <b>Motion</b> für Bewegung und Interaktion, <b>Daten</b> für Werte und Zustände, <b>Export</b> für Bereiche,
    Favicon und App-Symbole – siehe <a href="#grafik-motion">unten</a>), darunter die <b>Kontextleiste</b> mit den Optionen des Werkzeugs und der
    Auswahl, links die <b>Werkzeuge</b>, rechts die Bereiche <b>Farbe · Farbfelder · Kontur · Darstellung</b>,
    <b>Ebenen · Bibliothek · Symbole</b> und <b>Transformieren · Protokoll</b>, unten eine Statuszeile mit Tipps zum Werkzeug.</p>
  <ol class="doc-steps">
    <li><b>Neue Grafik:</b> Mediathek → <b>Neu</b> → Infografik (16:9, quadratisch oder frei – die Größe lässt sich unter
      <b>Dokument …</b> ändern). Der Editor öffnet sich über der Mediathek; <b>Vollbild</b> oben rechts (oder <kbd>⇧</kbd><kbd>⌘</kbd><kbd>F</kbd>)
      nutzt den ganzen Bildschirm und bleibt beim nächsten Mal so.</li>
    <li><b>Vorhandene SVG bearbeiten:</b> in der Mediathek per Rechtsklick → <b>Im Grafik-Editor bearbeiten …</b> oder im großen
      Bearbeitungsfenster. Auch Logos und Exporte aus Illustrator, Inkscape oder Figma lassen sich öffnen – was Sie nicht anfassen,
      bleibt genau so, wie es war.</li>
    <li><b>Speichern</b> (<kbd>⌘</kbd>/<kbd>Strg</kbd>+<kbd>S</kbd>): Beim ersten Speichern fragt der Editor nach Dateiname, Titel und
      Alt-Text. Danach ersetzt jedes Speichern die Datei – Adresse, Alt-Text und alle Verwendungen bleiben, die vorige Fassung
      steht unter <b>Dokument … → Frühere Fassungen</b>. „Speichern unter …“ (im Menü Exportieren) legt eine neue Datei an.</li>
  </ol>
  <table class="doc-table">
    <tr><th>Werkzeug</th><th>Taste</th><th>So geht es</th></tr>
    <tr><td>Verschieben</td><td><kbd>V</kbd></td><td>Klick wählt, Ziehen verschiebt, Rahmen ziehen wählt mehrere; Griffe ändern die Größe (<kbd>⇧</kbd> = Seitenverhältnis, <kbd>⌥</kbd> = von der Mitte), der runde Griff dreht (<kbd>⇧</kbd> = 15°-Schritte). <kbd>⌥</kbd>+Ziehen zieht eine Kopie, <kbd>⇧</kbd>+Ziehen verschiebt nur waagerecht bzw. senkrecht. Ein Klick (ohne Ziehen) auf ein Objekt einer Mehrfachauswahl wählt nur dieses. Doppelklick betritt eine Gruppe bzw. bearbeitet Text.</td></tr>
    <tr><td>Knoten</td><td><kbd>A</kbd></td><td>Zeigt die Punkte einer Kurve; Punkte und Griffe ziehen, <b>Klick auf die Kurve fügt einen Punkt ein</b> (die Form bleibt gleich), Doppelklick auf einen Punkt = Ecke/glatt, <kbd>⌥</kbd> beim Ziehen eines Griffs trennt die Griffe, <kbd>⌫</kbd> löscht Punkte. In der Kontextleiste: Knotenart Ecke/glatt/symmetrisch, an Knoten auftrennen, Endpunkte verbinden, Pfad schließen, Richtung umkehren, Teilpfade trennen, Vereinfachen. Rechtecke, Kreise und Linien werden dabei zu Kurven.</td></tr>
    <tr><td>Zeichenstift</td><td><kbd>P</kbd></td><td>Klick = Eckpunkt, Ziehen = glatter Punkt; Klick auf den ersten Punkt schließt die Form, <kbd>↵</kbd>/<kbd>Esc</kbd> beendet.</td></tr>
    <tr><td>Bleistift</td><td><kbd>N</kbd></td><td>Freihand zeichnen – die Linie wird geglättet (Stärke in der Kontextleiste).</td></tr>
    <tr><td>Rechteck, Ellipse, Vieleck/Stern</td><td><kbd>R</kbd> <kbd>E</kbd> <kbd>G</kbd></td><td>Ziehen; <kbd>⇧</kbd> = gleichmäßig, <kbd>⌥</kbd> = von der Mitte. Eckenradius, Zahl der Ecken und Stern in der Kontextleiste.</td></tr>
    <tr><td>Linie und Pfeil</td><td><kbd>L</kbd></td><td>Ziehen, <kbd>⇧</kbd> = 45°-Schritte; Pfeilspitzen am Anfang/Ende in der Kontextleiste.</td></tr>
    <tr><td>Verbinder</td><td><kbd>C</kbd></td><td>Von einer Form zur nächsten ziehen – die Enden haften an den Ankerpunkten und folgen, wenn Formen sich bewegen (siehe <a href="#grafik-diagramme">Diagramme</a>).</td></tr>
    <tr><td>Text</td><td><kbd>T</kbd></td><td>Klicken = Punkttext, <b>Rahmen ziehen = Textrahmen</b> (Zeilen brechen automatisch um). Tippen direkt auf der Fläche mit Schreibmarke; Ziehen wählt aus, Doppelklick ein Wort, <kbd>⌘</kbd><kbd>B</kbd>/<kbd>I</kbd>/<kbd>U</kbd> fett/kursiv/unterstrichen, eine Farbe wirkt auf die markierten Zeichen. <kbd>↵</kbd> = neuer Absatz, <kbd>Esc</kbd> beendet. <kbd>⌘</kbd><kbd>Z</kbd> nimmt beim Tippen Schritt für Schritt zurück – auch Formatierung wie Fett oder Farbe.</td></tr>
    <tr><td>Verlauf</td><td><kbd>F</kbd></td><td>Auf einem Objekt ziehen = linearen Verlauf anlegen bzw. neu ausrichten. Anfang/Ende (radial: Mitte/Radius) und Farbstopps auf der Fläche ziehen, Doppelklick auf die Linie = neuer Stopp, <kbd>⌫</kbd> löscht den gewählten.</td></tr>
    <tr><td>Formgitter</td><td><kbd>M</kbd></td><td>Mindestens zwei sich überlappende Formen auswählen. Der Zeiger zeigt die einzelnen Bereiche; Klick oder Ziehen über mehrere Bereiche vereint sie zu einer Form, mit <kbd>⌥</kbd> werden die Bereiche entfernt (siehe <a href="#grafik-wiederholen">Formgitter, Wiederholen, Effekte</a>).</td></tr>
    <tr><td>Pipette, Hand, Zoom</td><td><kbd>I</kbd> <kbd>H</kbd> <kbd>Z</kbd></td><td>Farbe eines Objekts übernehmen; Ansicht verschieben (auch Leertaste + Ziehen); vergrößern (<kbd>⌥</kbd> = verkleinern).</td></tr>
  </table>
  <ul>
    <li><b>Farbe:</b> Füllung oder Kontur wählen (Kreise unten in der Werkzeugleiste, <kbd>X</kbd> wechselt, <kbd>⇧</kbd><kbd>X</kbd> tauscht), dann
      Keine, Farbe, linearer oder radialer Verlauf. Verläufe: Farbstopps anklicken, ziehen, mit Klick auf den Balken ergänzen.
      <b>Farbmodell</b> umschalten wie in Affinity: <b>Hex, RGB, HSL, HSB</b> oder <b>OKLCH</b> – je Kanal ein Regler und ein Zahlenfeld,
      Deckkraft in Prozent; der gewählte Modus bleibt für Ihr Konto gemerkt. Ins Feld <b>Farbe</b> dürfen Sie jede CSS-Angabe schreiben
      (<code>#f80</code>, <code>rgb(10 20 30)</code>, <code>hsl(240 100% 50%)</code>, <code>oklch(0.7 0.1 200)</code>, Farbnamen, <code>#RRGGBBAA</code>) –
      sie wird umgerechnet. Gespeichert wird immer eine Hex-Farbe (Deckkraft getrennt), damit Desktop-Programme sie verstehen.
      Bei Text zeigt der Bereich den <b>Kontrast zum Hintergrund</b> nach WCAG (AA/AAA; großer Text ab 24 px bzw. 18,7 px fett).
      <b>Farbfelder</b> zeigt die Farben aus dem Design der Website, zuletzt verwendete Farben und die Farben der Grafik.
      „Schriftfarbe“ (A) übernimmt die Textfarbe der Umgebung – ideal für Symbole.</li>
    <li><b>Kontextmenü:</b> Rechtsklick auf ein Objekt (Tastatur: Menü-Taste oder <kbd>Umschalt</kbd>+<kbd>F10</kbd>) zeigt die üblichen Befehle –
      Ausschneiden, Kopieren, Einfügen, Duplizieren, Löschen, Gruppieren und Gruppierung aufheben, Anordnen (nach vorn/hinten), bei mehreren Objekten
      Ausrichten, Spiegeln, In Kurven umwandeln, Sperren, Ausblenden und Als Baustein speichern. Ist das Objekt noch nicht ausgewählt, wird es zuerst
      ausgewählt. „Weitere Objektbefehle …“ öffnet das vollständige Menü „Objekt“; Rechtsklick auf eine leere Stelle bietet Einfügen und Alles auswählen.</li>
    <li><b>Ebenen:</b> oben liegt, was vorn ist. Ziehen sortiert um (auch in Gruppen hinein), Doppelklick oder <kbd>F2</kbd> benennt um,
      Auge blendet aus, Schloss sperrt. Gesperrte Ebenen lassen sich hier trotzdem wählen, um Farbe, Effekte oder Namen zu ändern – auf der
      Fläche bleiben sie unverrückbar. Der Auswahlrahmen wählt alles, was er berührt; unter dem Zoom-Menü lässt er sich auf „Objekte vollständig
      umschließen“ umstellen. <b>Objekt → Auf Zeichenfläche einpassen</b> vergrößert z. B. ein klein importiertes Symbol verlustfrei. Deckkraft und Mischmodus gelten für die Auswahl.</li>
    <li><b>Bibliothek:</b> fertige Bausteine (Flussdiagramm, Organigramm, Hinweise und Pfeile, Browserfenster, Smartphone, Karte,
      Diagramm, Zeichen …) anklicken oder auf die Fläche ziehen – sie landen dort, wo Sie loslassen. Eine Auswahl lässt sich unter Ebenen
      <b>als Baustein speichern</b>: mit Ordner und wahlweise für <b>diese Website</b> oder <b>alle Websites der Installation</b>.
      Das Menü am Baustein (Knopf <b>…</b>, Rechtsklick oder <kbd>⇧</kbd><kbd>F10</kbd>) merkt ihn als <b>Favorit</b> (oben in der Liste, je Konto),
      benennt um, legt in einen Ordner, ersetzt ihn durch die aktuelle Auswahl, gibt ihn für alle Websites frei, exportiert oder löscht ihn.
      <b>Austausch zwischen Installationen:</b> Pfeil nach unten = eigene Bausteine als Datei sichern, Pfeil nach oben = Datei einlesen
      (jede Grafik wird dabei geprüft und bereinigt). Bausteine mit Design-Farben (<code>var(--accent)</code>) bekommen beim Einfügen die
      Farbe der Website; „Farben der Website“ setzt bei den mitgelieferten Bausteinen die Akzentfarbe ein.
      <b>Symbole</b> fügt Symbole aus dem Symbolsatz von KLXM Studio als bearbeitbare Pfade ein.</li>
    <li><b>Kopieren zwischen Grafiken:</b> <kbd>⌘</kbd><kbd>C</kbd> / <kbd>⌘</kbd><kbd>V</kbd> überträgt Objekte samt Verläufen, Pfeilspitzen,
      Schatten und der Farbe aus ihrer Gruppe in eine andere Grafik (auch in einem anderen Fenster) – an dieselbe Stelle und ohne zusätzliche
      Gruppe. SVG-Text aus anderen Programmen wird bereinigt und als Gruppe in die Mitte gesetzt; eine SVG-Datei, die Sie auf die Fläche
      ziehen, landet dort, wo Sie loslassen. Strich-Symbole (z. B. aus Lucide oder Tabler) behalten dabei ihre Kontur, Klassen aus
      Illustrator-Dateien (<code>.cls-1</code> …) färben vorhandene Formen nicht um.</li>
    <li><b>Gruppierung aufheben</b> gibt Füllung, Kontur, Schrift und Deckkraft der Gruppe an die Teile weiter – auch, wenn sie aus einer
      Klasse der importierten Datei stammen. Gruppen mit Beschneidung, Maske, Effekt oder Alternativen (<code>switch</code>) bleiben
      erhalten, weil sich das Bild sonst ändern würde; Beschneidung und Maske lösen Sie unter <b>Objekt → Maske freigeben</b>.</li>
    <li><b>Ausrichten und Verteilen</b> in der Kontextleiste: bei mehreren Objekten aneinander, bei einem an der Zeichenfläche.
      Beim Verschieben rasten Objekte an Kanten und Mitten ein (rote Hilfslinien); <kbd>⌘</kbd> beim Ziehen schaltet das ab.</li>
    <li><b>Transformieren:</b> Position, Breite, Höhe und Drehung als Zahl, Bezugspunkt im Raster, Kette = Seitenverhältnis sperren.
      <b>Protokoll</b> zeigt alle Schritte – ein Klick springt zurück.</li>
    <li>Alle Tastaturkürzel zeigt <kbd>?</kbd>. Wichtig: <kbd>⌘</kbd><kbd>Z</kbd> / <kbd>⇧</kbd><kbd>⌘</kbd><kbd>Z</kbd> Rückgängig/Wiederholen,
      <kbd>⌘</kbd><kbd>D</kbd> duplizieren, <kbd>⌘</kbd><kbd>G</kbd> / <kbd>⇧</kbd><kbd>⌘</kbd><kbd>G</kbd> gruppieren/aufheben, <kbd>⌘</kbd><kbd>[</kbd> / <kbd>]</kbd> nach hinten/vorn,
      Pfeiltasten verschieben (<kbd>⇧</kbd> = 10), <kbd>Tab</kbd> wählt das nächste Objekt, <kbd>⌘</kbd><kbd>0</kbd> einpassen. Rückgängig wählt wieder, was vor dem Schritt gewählt war,
      und bleibt in einer betretenen Gruppe.</li>
    <li><b>Barrierefreiheit:</b> Unter <b>Dokument …</b> erhalten Infografiken einen <b>Titel</b> und eine ausführliche <b>Beschreibung</b>;
      der Alt-Text in der Mediathek bleibt Pflicht. Schriften: In einer SVG als Bild zeigt der Browser nur Schriften, die auf dem
      Gerät vorhanden sind – deshalb wandelt das Speichern Texte in Schriften der Website auf Wunsch in Kurven um (siehe unten); im Editor bleiben sie Text.</li>
  </ul>
  <h3 id="grafik-motion">Bewegung und Interaktion (Persona Motion)</h3>
  <p>Oben links auf <b>Motion</b> umschalten: Unter der Zeichenfläche erscheint die <b>Zeitleiste</b>, rechts der Bereich
    <b>Bewegung · Interaktion · Szene</b>. Die Grafik bleibt dieselbe – Motion legt nur fest, wie sie sich bewegt. Zurück in <b>Vektor</b>
    bearbeiten Sie die Grundform; Hell | Dunkel gilt in beiden Personas.</p>
  <ol class="doc-steps">
    <li><b>Fertige Bewegung:</b> Objekt wählen, Beginn und Dauer (Sekunden) eintragen und eine Bewegung anklicken – <b>Einblenden</b>,
      <b>Von links/rechts/oben/unten</b>, <b>Heranzoomen</b>, <b>Aufploppen</b>, <b>Drehen</b>, <b>Pulsieren</b>, <b>Wackeln</b>, <b>Schweben</b>,
      <b>Linie zeichnen</b> (Linien und Formen), <b>Ausblenden</b>, <b>Zählen</b> (ein Text mit Zahl zählt bis zu seinem Wert hoch, z. B. „1.250 Projekte“)
      und <b>Pfad folgen</b> (Objekt und zuletzt den Weg wählen; „Richtung folgen“ dreht es mit). Bei mehreren Objekten beginnt jedes um
      <b>Versetzt</b> später. Die Bewegung spielt gleich einmal ab.</li>
    <li><b>Zeitleiste:</b> eine Zeile je bewegtem Objekt, darunter je Eigenschaft (Position, Größe, Drehung, Deckkraft, Linie zeichnen, Füllung,
      Kontur) eine Spur mit <b>Schlüsselbildern</b> (Rauten). Ins Lineal klicken oder ziehen setzt den <b>Abspielkopf</b> (rastet an Rauten und
      halben Sekunden ein, <kbd>⌥</kbd> = frei), <kbd>Leertaste</kbd> spielt ab und hält an, <kbd>Pos1</kbd>/<kbd>Ende</kbd> springen an Anfang und Ende,
      im Lineal bewegen <kbd>←</kbd>/<kbd>→</kbd> um ein Bild (<kbd>⇧</kbd> = eine Sekunde). Zoom mit − / + oder <kbd>⌘</kbd> + Mausrad, Magnet = Einrasten an/aus.</li>
    <li><b>Schlüsselbilder setzen:</b> Abspielkopf an die Stelle, dann das Objekt auf der Fläche <b>ziehen</b> (oder <kbd>←</kbd><kbd>→</kbd><kbd>↑</kbd><kbd>↓</kbd>) –
      das ergibt ein Schlüsselbild „Position“; ohne Bewegung entsteht dabei auch der Anfang bei 0 s. Werte wie Größe, Drehung, Deckkraft und Farben
      tragen Sie unter <b>Am Abspielkopf</b> ein; die Raute davor setzt oder entfernt das Schlüsselbild. <kbd>K</kbd> setzt Schlüsselbilder für alle Spuren
      der Auswahl, ein Doppelklick in eine Spur ebenso. Bei 0 s und ohne Bewegung verschiebt Ziehen die Grundform wie in Vektor.</li>
    <li><b>Rauten bearbeiten:</b> ziehen ändert den Zeitpunkt (<kbd>⇧</kbd>-Klick wählt mehrere, sie wandern gemeinsam), <kbd>←</kbd>/<kbd>→</kbd> verschiebt um 0,1 s
      (<kbd>⇧</kbd> = 1 s), <kbd>⌫</kbd> löscht, ein Klick stellt den Abspielkopf darauf.</li>
    <li><b>Verlauf:</b> für die gewählten Rauten (bzw. die am Abspielkopf) – <b>sanft</b>, <b>abbremsen</b>, <b>beschleunigen</b>, <b>gleichmäßig</b>,
      <b>federnd</b>, <b>halten</b> (springt am Ende) oder eine <b>eigene Kurve</b>: Griffe im Kurven-Editor ziehen oder X1/Y1/X2/Y2 eintragen. Der Verlauf
      gilt vom Schlüsselbild bis zum nächsten.</li>
    <li><b>Szene:</b> Dauer, Verzögerung, Wiederholen (einmal … endlos), Richtung (vorwärts, rückwärts, hin und zurück) und <b>Auslöser</b>:
      <b>sobald sichtbar</b> (Standard), <b>beim Laden</b>, <b>beim Zeigen auf die Grafik</b> oder <b>bei Klick auf die Grafik</b>. Je Objekt kann die Bewegung
      unter <b>Start</b> auch <b>beim Zeigen auf dieses Objekt</b> oder <b>bei Klick auf einen Schalter</b> beginnen.</li>
  </ol>
  <p><b>Interaktion:</b> Ein Objekt wird zum <b>Link-Bereich</b> (Ziel über die gewohnte Linkauswahl – Seiten bleiben verknüpft, auch wenn sich ihre
    Adresse ändert), bekommt einen <b>Namen für Screenreader</b> und einen <b>Tooltip</b> (erscheint beim Zeigen und bei Tastatur-Fokus) oder wird zum
    <b>Schalter</b>: Ein Klick (oder <kbd>Enter</kbd>/<kbd>Leertaste</kbd>) blendet zugeordnete Bewegungen ein, ein zweiter wieder aus – etwa ein Infokasten
    zu einem „i“. Die Tabulator-Reihenfolge auf der Website folgt den Ebenen; <b>Prüfen</b> meldet, wenn sie von der Lesereihenfolge abweicht, und
    bringt die Ebenen auf Wunsch in die richtige Reihenfolge. Links ohne Namen meldet Prüfen ebenfalls.</p>
  <p><b>Anhalten (WCAG 2.2.2):</b> Läuft eine Bewegung von selbst (beim Laden, sobald sichtbar oder nach einem Klick) länger als 5 Sekunden oder
    endlos, zeigt die Website in der Ecke der Grafik einen Knopf <b>„Animation anhalten“</b> – mit Maus und Tastatur bedienbar, Screenreader hören
    „Animation angehalten“ bzw. „Animation läuft“. Angehalten bleibt angehalten, auch wenn man weiterscrollt. Der Knopf lässt sich nicht abschalten;
    Prüfen weist nur darauf hin.</p>
  <p><b>Auf der Website:</b> Grafiken mit Bewegung oder Links erscheinen in Bild-Blöcken wie andere Bilder – die Website bindet sie dafür direkt in die
    Seite ein; Grafiken ohne Bewegung bleiben unverändert. Ohne JavaScript, bei „Bewegung reduzieren“ im Betriebssystem und wenn im Design
    „Dezente Animationen“ aus ist, steht das fertige Bild (Endzustand) still da; Schleifen halten an, solange die Grafik nicht zu sehen ist; mit
    dunklem Farbschema bewegt sich die dunkle Fassung. Im Bearbeiten-Modus der Website zeigt die Seite das Standbild. <b>Exportieren</b> bietet
    zusätzlich ein <b>animiertes SVG (eigenständig)</b> für Präsentationen oder andere Websites (spielt beim Öffnen) und für Desktop-Programme bzw.
    PDF das <b>Anfangsbild</b> (die üblichen Exporte zeigen das Endbild).</p>
  <h3 id="grafik-daten">Daten und Zustände (Persona Daten)</h3>
  <p>Mit <b>Daten</b> zeigt eine Grafik, was gerade gilt: ein Balken so lang wie die Zahl der Anmeldungen, ein Text mit den freien Plätzen,
    eine Sonne tagsüber und ein Mond nachts, „Jetzt geöffnet“ nach den Öffnungszeiten der Website. Die Website rechnet die Werte bei jedem Aufruf
    ein – ohne JavaScript, Texte bleiben Text.</p>
  <ol class="doc-steps">
    <li><b>Wert anlegen</b> (Bereich <b>Werte</b> → <b>Wert aus Datentabelle …</b>): Bezeichnung, Datentabelle und Kennzahl – <b>Anzahl der Einträge</b>,
      <b>Summe</b>, <b>Durchschnitt</b>, <b>Kleinster</b> oder <b>Größter Wert</b> eines Zahlenfelds, der <b>Neueste Eintrag</b> – optional nur Einträge, bei
      denen ein Feld einen Wert hat (z. B. Kategorie = Kurse). Es zählen nur veröffentlichte Einträge, wie auf der Website. Dazu kommen die
      <b>Zustände der Website</b>: Uhrzeit, Tag oder Nacht (Sonnenauf- und -untergang am Ort der Website, ohne Ort 7–19 Uhr), Öffnungsstatus (aus den
      Öffnungszeiten), Wochentag, Monat und Datum.</li>
    <li><b>Binden</b> (Bereich <b>Bindung</b>): Objekt wählen, unter <b>Eigenschaft binden</b> eine Eigenschaft wählen und <b>Binden</b> –
      <b>Text</b> (Vorlage mit <code>{v}</code>, z. B. „Noch {v} Plätze frei“, Format Zahl, Prozent, Betrag oder Uhrzeit),
      <b>Breite</b> oder <b>Höhe</b> (bei „bis“ so lang wie gezeichnet, bei „von“ null; wächst von links, der Mitte oder rechts bzw. unten),
      <b>Drehung</b> (Winkel von–bis, um die Mitte – z. B. ein Zeiger), <b>Füllung</b> oder <b>Kontur</b> (Verlauf zwischen zwei Farben oder
      <b>Schwellen</b>: ab einem Wert eine andere Farbe), <b>Deckkraft</b> und <b>Sichtbarkeit</b> (sichtbar, wenn der Wert über/unter einer Schwelle
      liegt; bei Tag/Nacht und Öffnungsstatus einfach „am Tag“, „in der Nacht“, „wenn geöffnet“, „wenn geschlossen“). Jede Änderung ist ein
      Schritt im Protokoll.</li>
    <li><b>Vorschau:</b> Unter der Zeichenfläche stehen <b>Beispielwerte</b> (Regler und Zahl, „aktuell“ übernimmt den Wert der Website), der
      <b>Uhrzeit-Regler</b>, Wochentag und Datum. Die Fläche zeigt sofort das Ergebnis, die Plaketten „Tag/Nacht“ und „geöffnet/geschlossen“ den
      Zustand. Das Dokument bleibt die gezeichnete Fassung; zum Bearbeiten einfach ziehen – während des Ziehens sehen Sie die Grundform.</li>
  </ol>
  <p><b>Barrierefreiheit:</b> Die Beschreibung der Grafik nennt die aktuellen Werte. Im <b>Alt-Text</b> der Mediathek setzt <code>{name}</code> den
    Wert ein (z. B. „Freie Plätze: {freie_plaetze}“); ohne Platzhalter werden die Werte angehängt. Ändern sich die Einträge der Tabelle, zeigt die Grafik
    beim nächsten Aufruf die neuen Werte; Seiten mit Zuständen (Uhrzeit, Tag/Nacht, geöffnet) hält die Website nur bis zum nächsten Wechsel im
    Zwischenspeicher.</p>
  <h3 id="grafik-export-persona">Export: Bereiche, Favicon, App-Symbole, Social-Vorschau</h3>
  <p>In <b>Export</b> ziehen Sie auf der Fläche <b>Bereiche</b> auf (wie Slices in Fireworks oder Affinity; <kbd>⇧</kbd> = quadratisch), verschieben sie
    durch Ziehen, ändern die Größe an den Ecken oder mit X/Y/B/H und <kbd>←</kbd><kbd>→</kbd><kbd>↑</kbd><kbd>↓</kbd> (<kbd>⇧</kbd> = 10), <kbd>⌫</kbd> löscht.
    Vorlagen: <b>Ganze Zeichenfläche</b>, <b>App-Symbol (quadratisch)</b>, <b>Social-Vorschau</b> (1200 × 630). Je Bereich wählen Sie die <b>Formate</b>:</p>
  <ul>
    <li><b>SVG fürs Web</b>, <b>SVG für Desktop-Programme</b> und <b>PDF (Vektor)</b> – nur der Ausschnitt des Bereichs.</li>
    <li><b>Favicon</b>: favicon.ico mit 16, 32 und 48 px und dieselben Größen als PNG; <b>App-Symbole</b>: 180 px fürs iPhone, 192 und 512 px,
      „maskable“ 512 px. Der gestrichelte Kreis zeigt die <b>Schutzzone</b> (80 %) – Android schneidet außen ab. Symbol-Bereiche bleiben quadratisch.</li>
    <li><b>Social-Vorschau</b> 1200 × 630 als PNG oder JPEG (für Links in sozialen Netzwerken und Messengern).</li>
  </ul>
  <p>Unten zeigt die Leiste jede <b>Symbolgröße</b> (16, 24, 32, 48 px) in echter Größe und vergrößert. <b>Pixelraster</b> richtet Kanten und Linien je
    Größe an ganzen Pixeln aus – kleine Symbole werden scharf statt verschwommen; abschalten, wenn eine Form dadurch leidet. <b>Bereich exportieren</b>
    lädt die Dateien herunter (mehrere als ZIP), <b>Alle Bereiche als ZIP</b> alles in Ordnern. Pixel-Formate entstehen nur hier beim Herunterladen –
    die Grafik selbst bleibt Vektor. Mit dem Recht für die Grundeinstellungen übernimmt <b>Als Favicon/App-Symbol der Website übernehmen</b> den Bereich
    für die ganze Website (Grundeinstellungen → App-Icon &amp; PWA zeigt danach dieses Bild).</p>
  <h3 id="grafik-bild">Bilder mit Foto und Text (Persona Bild)</h3>
  <p>Mit <b>Bild</b> mischen Sie wie in Fireworks: Fotos aus der Mediathek, Texte, Formen und Verläufe in einem Dokument – zum Beispiel ein
    Kopfbild für eine Veranstaltung oder ein Bild für soziale Netzwerke. Das <b>Ergebnis ist immer ein Pixelbild der Mediathek</b> (doppelte Auflösung,
    die Mediathek erzeugt daraus alle Größen als WebP); die <b>Arbeitsfassung</b> mit Ebenen und Texten bleibt erhalten und lässt sich jederzeit wieder
    öffnen. Infografiken ohne Foto bleiben reine Vektorgrafiken.</p>
  <ol class="doc-steps">
    <li><b>Neues Bild:</b> Mediathek → <b>Neu</b> → <b>Bild mit Foto und Text (1200 × 630)</b>. Der Editor öffnet sich in der Persona <b>Bild</b>.</li>
    <li><b>Foto einfügen:</b> Bereich <b>Bild</b> → Reiter <b>Foto</b> → <b>Foto aus der Mediathek …</b>. Das erste Foto füllt die ganze Fläche (Hintergrund),
      weitere erscheinen halb so groß in der Mitte. Das Foto wird nicht kopiert, sondern mit der Mediathek verknüpft – in der Mediathek steht danach unter
      <b>Verwendet auf</b> „Bild aus dem Grafik-Editor“.</li>
    <li><b>Ausschnitt:</b> Ein Foto füllt immer seinen Rahmen. Ohne eigene Einstellung zeigt es den <b>Fokuspunkt</b> aus der Mediathek; mit <b>Zoom</b>,
      <b>Lage waagerecht</b> und <b>Lage senkrecht</b> wählen Sie einen anderen Ausschnitt, <b>Fokuspunkt der Mediathek</b> stellt ihn zurück. Den Rahmen
      ziehen Sie wie jedes Objekt größer oder kleiner – der Ausschnitt folgt.</li>
    <li><b>Korrektur:</b> <b>Helligkeit</b>, <b>Kontrast</b>, <b>Sättigung</b> und <b>Weichzeichnen</b> (wie unter „Bild anpassen“ in der Mediathek, aber nur
      für dieses Bild – das Foto in der Mediathek bleibt unverändert). Dazu <b>Deckkraft</b> und <b>Mischmodus</b>.</li>
    <li><b>Verlauf darüber</b> legt einen Verlauf von durchsichtig nach dunkel über das Foto – Text darauf bleibt lesbar. Farben und Richtung ändern Sie wie bei
      jedem Verlauf. <b>Mit Form darüber beschneiden:</b> Foto und eine Form darüber auswählen (<kbd>⇧</kbd>-Klick) – das Foto erscheint nur innerhalb der Form
      (Kreis, abgerundetes Rechteck …). Masken und Ebeneneffekte wirken wie bei Vektoren.</li>
    <li><b>Speichern</b> (<kbd>⌘</kbd><kbd>S</kbd>): Dateiname, Titel und <b>Alt-Text</b> – <b>Vorschlag aus den Textebenen</b> übernimmt die Texte des Bildes;
      ergänzen Sie, was das Foto zeigt. Unter <b>Ausgabe</b> wählen Sie weißen Hintergrund (JPEG) oder Transparenz (PNG).</li>
  </ol>
  <p><b>Wieder bearbeiten:</b> In der Mediathek öffnet <b>Im Grafik-Editor bearbeiten</b> (Rechtsklick oder im großen Bearbeitungsfenster) die Arbeitsfassung.
    Speichern ersetzt das Bild – ID, Alt-Text und alle Verwendungen auf Seiten bleiben. Bearbeiten Sie das fertige Bild dagegen mit „Bild bearbeiten“,
    überschreibt das nächste Speichern im Grafik-Editor diese Änderung. Pinsel und Retusche gibt es bewusst nicht – dafür ist ein Bildprogramm da.</p>
  <h3 id="grafik-formate">Formate für soziale Netzwerke</h3>
  <p>Reiter <b>Formate</b>: Aus einem Entwurf entstehen die üblichen Größen – <b>Link-Vorschau 1200 × 630</b>, <b>Beitrag quadratisch 1080 × 1080</b>,
    <b>Beitrag hoch 1080 × 1350</b>, <b>Story 1080 × 1920</b>, <b>Kopfbild 1920 × 1080</b> oder eine freie Größe. Formate ankreuzen, <b>Formate anlegen</b> –
    jedes Format wird eine eigene Zeichenfläche rechts neben dem Hauptformat. Danach bessern Sie jedes Format einzeln nach (Text verschieben, Größe ändern);
    <b>Neu aus dem Hauptformat erzeugen</b> (Pfeile) baut es wieder nach den Regeln auf, der Papierkorb entfernt es.</p>
  <p>Wo eine Ebene in anderen Formaten landet, bestimmt ihr <b>Anker</b> (Reiter Formate, eine Ebene auswählen): waagerecht <b>links</b>, <b>Mitte</b>,
    <b>rechts</b> oder <b>Breite wächst mit</b>, senkrecht <b>oben</b>, <b>Mitte</b>, <b>unten</b> oder <b>Höhe wächst mit</b>; dazu <b>feste Größe</b>, wenn
    sie nicht mit dem Format wachsen soll (z. B. ein Logo). Ohne eigene Einstellung schlägt der Editor einen Anker aus der Lage vor – ein Hintergrundfoto
    wächst mit, ein Titel unten links bleibt unten links. Fotos füllen ihren neuen Rahmen nach dem Fokuspunkt; Texte werden nie verzerrt.</p>
  <p>Beim Speichern wird <b>jedes Format ein eigenes Bild in der Mediathek</b> (im Dialog lassen sich einzelne abwählen) – so laden Sie für jede Plattform
    die passende Datei hoch. Erneutes Speichern ersetzt alle Bilder.</p>
  <h3 id="grafik-vorlagen">Vorlagen zum Ausfüllen</h3>
  <p>Die Agentur bzw. Administration gestaltet ein Bild einmal und gibt nur einzelne Ebenen frei; die Redaktion füllt danach ein Formular aus und bekommt
    fertige Bilder – ohne Editor und ohne das Layout zu verändern.</p>
  <ol class="doc-steps">
    <li><b>Vorlage gestalten</b> (Recht <b>Vorlagen für Bilder gestalten</b>, unter Benutzer &amp; Rollen; standardmäßig nur die Administration): Bild wie
      gewohnt gestalten, dann Reiter <b>Vorlage</b>. Ebene auswählen und unter <b>Freigeben als</b> festlegen: <b>Bild</b> (Fotos), <b>Text</b> (mit Bezeichnung,
      einzeilig oder mehrzeilig, höchstens Zeichen, Zeilen und kleinste Schrift in %), <b>Farbe</b> (nur aus den Design-Farben der Website) oder
      <b>Ein/Aus</b> (z. B. ein Verlauf oder ein Störer). Alles, was nicht freigegeben ist, bleibt gesperrt.</li>
    <li><b>Als Vorlage speichern …</b> mit Namen – für <b>diese Website</b> oder <b>alle Websites der Installation</b>. Vorhandene Vorlagen finden Sie in der
      <a href="<?= e(url('/admin/grafik')) ?>">Grafik-Ablage</a> unter <b>Vorlagen zum Ausfüllen</b> (bearbeiten, löschen, neue gestalten). Formate, die Sie
      in der Vorlage schon angelegt und nachgebessert haben, verwendet das Formular so, wie sie sind.</li>
    <li><b>Ausfüllen</b> (Redaktion): Mediathek → <b>Neu</b> → <b>Aus Vorlage erstellen …</b>, Vorlage wählen. Links die Felder – <b>Bild wählen …</b> öffnet
      die Mediathek, Texte zeigen, wie viele Zeichen noch gehen –, rechts die <b>Vorschau</b> je Format. Wird ein Text zu lang, wird die Schrift kleiner, bis
      zur kleinsten Größe der Vorlage; reicht das nicht, erscheint am Feld ein Hinweis. Formate ankreuzen, Dateiname und Alt-Text (Vorschlag aus den Texten)
      prüfen, <b>Bilder erstellen</b>.</li>
  </ol>
  <p>Die fertigen Bilder stehen danach ausgewählt in der Mediathek. <b>Felder der Vorlage ändern …</b> (Rechtsklick auf das Bild) öffnet das Formular mit den
    bisherigen Werten wieder – Speichern ersetzt die Bilder, ihre Verwendungen bleiben.</p>
  <h3 id="grafik-stile">Objektstile und Zeichenflächen</h3>
  <p><b>Objektstile</b> (Bereich <b>Darstellung</b>, unten): <b>Stil aus Auswahl …</b> merkt sich das Aussehen des gewählten Objekts – Füllung, Kontur
    (Farbe, Stärke, Strichmuster, Enden, Ecken), Deckkraft, Mischmodus und Ebeneneffekte – unter einem Namen. <b>Anwenden</b> gibt anderen Objekten
    dasselbe Aussehen; <b>Aus Auswahl aktualisieren</b> übernimmt Änderungen in den Stil und passt alle Objekte mit diesem Stil an. Löschen entfernt nur
    den Stil, die Objekte behalten ihr Aussehen. Alles ist ein Schritt im Protokoll.</p>
  <p><b>Zeichenflächen</b> (Persona <b>Export</b> → <b>Zeichenfläche hinzufügen</b>): weitere Formate im selben Dokument, z. B. ein quadratischer Beitrag,
    ein Hochformat, eine Social-Vorschau, DIN A4 oder ein App-Symbol. Die Fläche wächst nach rechts, jede Zeichenfläche ist ein Bereich mit Rahmen und
    eigenen Formaten und wird für sich exportiert. Auf der Website erscheint die ganze Grafik – für einzelne Formate den Export nutzen.</p>
  <h3 id="grafik-fassungen">Fassungen, Verwendet auf, wer arbeitet gerade</h3>
  <p>Neben dem Namen der Grafik stehen zwei Knöpfe: <b>Fassungen vergleichen</b> (Uhr) zeigt die früheren Fassungen – jedes Speichern legt eine an,
    mit Datum und Person. Wählen Sie eine Fassung (auch mit <kbd>↑</kbd>/<kbd>↓</kbd>) und vergleichen Sie <b>nebeneinander</b> oder
    <b>überblendet</b>: Der Regler (oder ein Klick ins Bild) schiebt die Trennlinie zwischen früherer Fassung und aktuellem Stand. <b>Diese Fassung
    wiederherstellen</b> holt sie in den Editor – als Schritt im Protokoll, gespeichert wird erst mit <b>Speichern</b>; Rückgängig bringt den Stand davor
    zurück. <b>Verwendet auf</b> (Kette) listet die Seiten, Blöcke und Einträge, auf denen die Grafik eingebunden ist, mit Link und einer verkleinerten
    <b>Vorschau</b> der Seite – so sehen Sie vor dem Speichern, wo sich etwas ändert.</p>
  <p>Bearbeitet jemand anderes dieselbe Grafik, erscheint oben „… bearbeitet gerade“. Hat diese Person inzwischen gespeichert, fragt
    <b>Speichern</b> nach, bevor es ihren Stand ersetzt (er bleibt als frühere Fassung erhalten). Wie bei Seiten lässt sich die Anzeige in den
    Grundeinstellungen abschalten.</p>
  <h3 id="grafik-dunkel">Design-Farben, Hell und Dunkel</h3>
  <p>Wählen Sie eine Farbe unter <b>Farbfelder → Design der Website</b> (z. B. „Akzent“), merkt sich die Grafik die <b>Design-Farbe</b>, nicht nur
    den Farbwert: Ändern Sie später unter <b>Design</b> den Akzent, zeigt die Website die Grafik in der neuen Farbe – ohne sie neu zu speichern.
    Hat die Website ein dunkles Farbschema, erscheint die Grafik dort automatisch mit den Dunkel-Werten des Designs.</p>
  <ul>
    <li><b>Hell | Dunkel | Nebeneinander</b> oben neben den Personas schaltet die Ansicht um – das ist keine eigene Grafik, sondern dieselbe
      Grafik in zwei Fassungen. „Nebeneinander“ zeigt rechts die dunkle Fassung, während Sie links bearbeiten. Der Knopf daneben wählt den
      <b>Hintergrund</b> der Vorschau: neutral weiß oder einer der Abschnitts-Hintergründe der Website (Standard, Getönt, Akzent, Dunkel …), jeweils hell und dunkel.</li>
    <li>In der Ansicht <b>Dunkel</b> wirkt eine neue Farbe sofort nur in der dunklen Fassung; beim Übernehmen fragt der Editor:
      <b>Nur für Dunkel</b> oder <b>Für Hell und Dunkel</b> – wahlweise für <b>alle gleichen Farben</b> der Grafik. „Nicht mehr fragen“ gilt, bis Sie den Editor schließen.</li>
    <li>Im Bereich <b>Farbe</b> zeigt <b>Im Dunkeln</b>, was mit der gewählten Farbe passiert: <b>Folgt Design</b> (nur Design-Farben: ihr Dunkel-Wert),
      <b>Eigene Farbe</b> (eine feste Farbe nur für Dunkel – der Editor wechselt dafür in die dunkle Ansicht) oder <b>Gleich in beiden</b>.</li>
    <li><b>Dunkel-Fassung vorschlagen</b> (Menü am Hintergrund-Knopf; mit Auswahl nur für diese): Design-Farben folgen dem Design, eigene Farben
      bekommen die umgekehrte Helligkeit bei gleichem Farbton (Schwarz wird hell, Weiß dunkel, mittlere Töne bleiben). Danach zeigt der Editor beide
      Fassungen nebeneinander; <kbd>⌘</kbd><kbd>Z</kbd> nimmt den Vorschlag in einem Schritt zurück. <b>Eigene Dunkel-Farben entfernen</b> setzt zurück.</li>
    <li><b>Exportieren → SVG für Desktop-Programme</b> enthält feste Farben (die hellen); dazu gibt es die <b>Dunkel-Fassung</b> als eigene Datei.
      Bausteine, die Sie in der Bibliothek speichern, behalten ihre Design-Farben und übernehmen auf einer anderen Website deren Farben.</li>
  </ul>
  <h3 id="grafik-pruefen">Prüfen: Kontrast und Barrierefreiheit</h3>
  <p>Der Reiter <b>Prüfen</b> (rechts unten, auch über das Menü am Hintergrund-Knopf) listet Hinweise – beim Speichern läuft die Prüfung
    automatisch, hält das Speichern aber nicht auf. Ein Klick auf einen Hinweis wählt das Element, holt es ins Bild und zeigt die Fassung
    (hell oder dunkel), in der das Problem auftritt; kleine Markierungen auf der Fläche zeigen dieselben Stellen.</p>
  <ul>
    <li><b>Titel, Beschreibung, Alt-Text</b> fehlen – mit Knöpfen zum Ergänzen; der Alt-Text-Vorschlag stammt aus Titel und Texten der Grafik
      (auch im Speichern-Dialog: „Vorschlag aus Titel und Texten“).</li>
    <li><b>Kontrast</b> von Text zur Fläche darunter (4,5:1, große Schrift 3:1) und von wichtigen Linien und Konturen (3:1) – in Hell und Dunkel,
      auf dem gewählten Hintergrund. <b>Verschwindet im Dunkeln</b> meldet z. B. eine schwarze Kontur, die auf dunklem Grund nicht mehr zu sehen ist.</li>
    <li><b>Schrift zu klein</b>: unter 12 px in der vorgesehenen Größe. Wie breit die Grafik auf der Website erscheint, stellen Sie unter
      <b>Dokument → Breite auf der Website</b> ein (leer = so breit wie die Zeichenfläche).</li>
    <li><b>Lesereihenfolge</b>: Diagramme mit Verbindern brauchen den Ablauf in der Beschreibung (ein Klick fügt ihn ein); Texte, deren
      Reihenfolge in den Ebenen nicht der sichtbaren entspricht, werden genannt.</li>
  </ul>
  <h3 id="grafik-wiederholen">Formgitter, Wiederholen, Ebeneneffekte</h3>
  <ul>
    <li><b>Formgitter</b> (<kbd>M</kbd>, wie der Shape Builder in Illustrator/Affinity): Formen auswählen, Werkzeug wählen – die Überlappungen
      erscheinen als einzelne Bereiche (gestrichelt). Über einen Bereich fahren hebt ihn hervor, Klick oder Ziehen über mehrere vereint sie,
      <kbd>⌥</kbd>-Klick bzw. <kbd>⌥</kbd>-Ziehen entfernt sie. Alle übrigen Bereiche bleiben als eigene Formen erhalten; jeder Schritt lässt sich mit
      <kbd>⌘</kbd><kbd>Z</kbd> zurücknehmen.</li>
    <li><b>Wiederholen:</b> <kbd>⌘</kbd><kbd>D</kbd> dupliziert; verschieben (oder drehen) Sie die Kopie und drücken erneut <kbd>⌘</kbd><kbd>D</kbd>,
      bekommt jede weitere Kopie denselben Versatz – wie „Erneut transformieren“. <b>Objekt → Wiederholen …</b> (<kbd>⌥</kbd><kbd>⌘</kbd><kbd>D</kbd>)
      erzeugt viele Kopien auf einmal: <b>Versatz</b> (Anzahl, X/Y, Drehung und Skalierung je Kopie), <b>Kreis</b> (um einen Mittelpunkt, z. B. für
      Zifferblätter und Strahlen; Objekte mitdrehen oder nicht) oder <b>Raster</b> (Spalten × Zeilen mit Abstand). Die Vorschau erscheint sofort auf der
      Fläche; das Ergebnis ist eine Gruppe und ein Schritt im Protokoll.</li>
    <li><b>Ebeneneffekte</b> (Bereich Darstellung): Schlagschatten, innerer Schatten, äußeres und inneres Leuchten, Kontur außen, Farbüberlagerung und
      Weichzeichnen – mehrere je Ebene, jeweils mit Haken an/aus (die Einstellungen bleiben gemerkt) und „…“ für Größe, Versatz, Farbe und Deckkraft.
      Die Fläche zeigt das Ergebnis sofort. Große Weichzeichner kosten Rechenzeit beim Anzeigen; im PDF fehlen Effekte, in SVG für
      Desktop-Programme bleiben sie erhalten.</li>
  </ul>
  <h3 id="grafik-verzerren">Verzerren und Neigen</h3>
  <p><b>Objekt → Perspektivisch verzerren</b> zeigt vier Eckgriffe: Ziehen Sie eine Ecke, entsteht eine Fluchtperspektive (gerade Linien
    bleiben gerade). <b>Objekt → Hülle verzerren</b> hat zusätzlich Griffe in den Kantenmitten – damit wölben Sie Kanten, z. B. für ein
    gebogenes Banner. <kbd>↵</kbd> oder ein Klick daneben übernimmt, <kbd>Esc</kbd> bricht ab. Texte werden dafür nach einer Rückfrage in
    Kurven umgewandelt. <b>Neigen</b> steht im Bereich Transformieren (Feld N, Grad um den Bezugspunkt).</p>
  <h3 id="grafik-geometrie">Formen kombinieren, ausstanzen, umwandeln</h3>
  <p>Zwei oder mehr Objekte wählen, dann in der Kontextleiste bzw. im Menü <b>Objekt</b>. Gerechnet wird mit den echten Kurven
    (Kreise bleiben rund); Konturen zählen nicht zur Fläche.</p>
  <table class="doc-table">
    <tr><th>Befehl</th><th>Ergebnis</th><th>Wann?</th></tr>
    <tr><td>Vereinen</td><td>eine Form aus allen</td><td>Umriss aus mehreren Teilen, z. B. Sprechblase aus Kreis und Dreieck</td></tr>
    <tr><td><b>Abziehen</b> (Ausstanzen)</td><td>die oberen Formen schneiden Löcher in die unterste – der Hintergrund scheint durch</td><td>Schablonen, Stanz-Optik, Text aus einer Fläche ausstanzen (Text wird dafür in Kurven umgewandelt)</td></tr>
    <tr><td>Schnittmenge</td><td>nur der gemeinsame Teil</td><td>Ausschnitte, z. B. Halbkreis aus Kreis und Rechteck</td></tr>
    <tr><td>Ausschluss</td><td>alles außer dem gemeinsamen Teil</td><td>Muster, Logos mit Überlappung</td></tr>
    <tr><td>Teilen</td><td>einzelne Flächen an allen Schnittlinien</td><td>Teile getrennt einfärben</td></tr>
    <tr><td>Zusammengesetzt (bearbeitbar)</td><td>wie Abziehen bzw. Vereinen, die Originale bleiben erhalten</td><td>wenn sich Text oder Formen noch ändern: Doppelklick bzw. „Originale bearbeiten“, danach wird neu berechnet; „In Pfad umwandeln“ macht es endgültig. Auf der Website erscheint immer der fertige Pfad.</td></tr>
    <tr><td>Ausstanzen per Maske</td><td>Loch über eine umgekehrte Maske, nichts wird umgerechnet</td><td>wenn die Stanze ein Text bleiben soll (bearbeitbar) oder weiche Kanten/Verläufe hat</td></tr>
    <tr><td>Kontur in Fläche</td><td>die Kontur wird eine gefüllte Form</td><td>Schneide-/Plotter-Daten, Logos, bevor man die Kontur weiter kombiniert</td></tr>
    <tr><td>Pfad versetzen …</td><td>Umriss nach außen (+) oder innen (−), Ecken rund/spitz/abgeschrägt</td><td>Rand um ein Logo, Aufkleber-Kontur, Abstand für Ausstanzungen</td></tr>
  </table>
  <h3 id="grafik-text">Text: Schrift, Zeichen, Absatz, OpenType, Vorlagen</h3>
  <p>Ist ein Text gewählt, erscheint rechts der Bereich <b>Zeichen | Absatz | OpenType | Vorlagen</b>. Während Sie tippen, gelten
    Zeichen-Einstellungen nur für die markierten Zeichen, sonst für den ganzen Text.</p>
  <ul>
    <li><b>Schriftauswahl</b> (im Bereich Zeichen und in der Kontextleiste): jede Schrift erscheint in ihrem eigenen Schriftbild. Tippen filtert,
      <kbd>↑</kbd>/<kbd>↓</kbd> gehen durch die Liste, <kbd>→</kbd> zeigt rechts die <b>Schnitte</b> (Gewicht, kursiv) mit Vorschau, <kbd>↵</kbd> übernimmt,
      <kbd>Esc</kbd> verwirft. Schon beim Zeigen oder Durchblättern sehen Sie die Schrift am Text – erst <kbd>↵</kbd> bzw. ein Klick macht einen Schritt
      im Protokoll. Abschnitte: <b>Zuletzt verwendet</b>, <b>Favoriten</b> (Stern bzw. <kbd>⌘</kbd><kbd>D</kbd>, je Konto gemerkt), <b>Schriften der
      Website</b> (Schriften-Manager), <b>Systemschriften</b>. Bei <b>variablen Schriften</b> stehen darunter Regler für ihre Achsen
      (Gewicht, Breite, Neigung, optische Größe …).</li>
    <li><b>Zeichen:</b> Schnitt, Größe, Laufweite, Unterschneidung, Unterstrichen/Durchgestrichen, Groß-/Kleinschreibung (als echte Zeichen),
      <b>Grundlinienversatz</b>, <b>Breite/Höhe</b> in Prozent (horizontal/vertikal skalieren).</li>
    <li><b>Absatz:</b> Ausrichtung links, Mitte, rechts und <b>Blocksatz</b> (im Textrahmen; die letzte Zeile eines Absatzes bleibt links),
      Zeilen- und Wortabstand, <b>Abstand vor/nach Absätzen</b>, <b>Einzüge</b> links, rechts und für die erste Zeile, <b>Silbentrennung</b>
      Deutsch oder Englisch (im Textrahmen; die Trennstriche entstehen beim Umbruch, Ihr Text bleibt unverändert). Breite des Textrahmens.
      <kbd>↑</kbd>/<kbd>↓</kbd> springen beim Tippen in die Zeile darüber bzw. darunter – auch über automatisch umbrochene Zeilen.</li>
    <li><b>OpenType:</b> Ligaturen (Standard, bedingt, kontextbedingt), Kapitälchen, Ziffern (Versal-/Mediävalziffern, proportional/tabellarisch),
      Brüche, Null mit Schrägstrich, Ordinalzeichen, Hoch-/Tiefstellen, <b>Stilsätze</b> und <b>Zeichenvarianten</b> – angezeigt wird nur, was
      die gewählte Schrift kann, mit den Namen aus der Schrift. Kann eine Schrift kein echtes Hochstellen, wird es nachgebildet (kleiner, versetzt).
      „In Kurven umwandeln“ übernimmt alle Merkmale in die Umrisse.</li>
    <li><b>Vorlagen:</b> <b>Absatzvorlagen</b> (Schrift, Größe, Farbe, OpenType, Absatz-Einstellungen eines ganzen Textes) und
      <b>Zeichenvorlagen</b> (für markierte Zeichen) „Aus Auswahl“ anlegen, per Klick anwenden. „Aus Auswahl aktualisieren“ ändert alle
      Texte bzw. Zeichen mit dieser Vorlage auf einmal. Vorlagen gehören zur Grafik; über das Menü kommen sie in die <b>Bibliothek der
      Website</b> und stehen dann in jeder Grafik bereit.</li>
    <li id="grafik-texthintergrund"><b>Hintergrund</b> (Bereich Zeichen, ganz unten): „Fläche hinter dem Text“ legt eine Fläche hinter freie Texte,
      Texte in Formen und Beschriftungen von Verbindern – mit <b>Farbe</b>, <b>Deckkraft</b>, <b>Innenabstand</b>, <b>Eckenradius</b> und
      optional <b>Rahmen</b> (Breite 0 = ohne). Die Fläche passt sich beim Tippen, bei Schriftgröße, Textrahmen, Verschieben, Skalieren und Drehen
      selbst an; ein Klick auf sie wählt den Text. „Design-Farben und Dunkel-Farbe …“ wählt die Fläche, dann gelten die Bereiche Farbe und Kontur wie
      bei jeder Form (Design-Farben, eigene Farbe im Dunkeln). Die Kontrastanzeige und „Prüfen“ messen den Text gegen diese Fläche. Auf der Website
      ist sie ein gewöhnliches Rechteck.</li>
    <li><b>Bearbeiten auf der Fläche:</b> Schreibmarke, Markierung und der gestrichelte Textrahmen liegen genau auf dem Text – auch zentriert oder
      rechtsbündig, in Formen, in Gruppen, gedreht oder skaliert und bei jedem Zoom. Bei Text in einer Form folgt die Breite der Form (kein Griff).</li>
    <li><b>Text auf Pfad:</b> Text und Kurve wählen → „Text auf Pfad“, mit Startversatz verschieben, „Vom Pfad lösen“ macht wieder normalen Text.
      <b>In Kurven umwandeln</b> (<kbd>⌘</kbd><kbd>↵</kbd>) macht aus Text Pfade – nur mit einer Schrift, deren Datei auf der Website liegt.</li>
  </ul>
  <h3 id="grafik-diagramme">Diagramme: Verbinder, Flussdiagramm, Organigramm</h3>
  <ul>
    <li><b>Verbinder</b> (<kbd>C</kbd>): Zeigen Sie auf eine Form – ihre Ankerpunkte erscheinen (Seitenmitten; der Punkt in der Mitte bedeutet
      „automatisch“, die Seite richtet sich dann nach dem Gegenüber; bei Pfaden zusätzlich die Knoten als Magnetpunkte). Von dort zur nächsten Form
      ziehen. Verbinder bleiben angeheftet, wenn Sie Formen verschieben oder skalieren.</li>
    <li>In der Kontextleiste: <b>gerade</b>, <b>rechtwinklig</b> oder <b>gebogen</b>, Spitzen am Anfang/Ende (Pfeil, Dreieck, offenes Dreieck, Kreis,
      Raute, Strich) und ihre Größe. Mit dem Auswahlwerkzeug lassen sich die Enden an eine andere Form ziehen und beim rechtwinkligen Verbinder
      der mittlere Abschnitt verschieben.</li>
    <li id="grafik-beschriftung"><b>Verbinder beschriften:</b> Verbinder mit dem Auswahlwerkzeug anklicken, dann <b>Doppelklick auf die Linie</b> oder
      <kbd>↵</kbd> und tippen (<kbd>Esc</kbd> beendet). Alternativ das Feld <b>Beschriftung</b> in der Kontextleiste oder Rechtsklick →
      <b>Beschriftung hinzufügen</b>. Die Beschriftung sitzt in der Mitte der Linie; <b>ziehen</b> verschiebt sie entlang der Linie (sie rastet an der
      Linie ein und behält ihre Stelle, wenn Sie Formen bewegen). Rechtsklick auf die Beschriftung: <b>bearbeiten</b>, <b>in die Mitte</b>,
      <b>entfernen</b> (oder das Feld leeren). Eine helle Kontur um die Schrift hält sie auf der Linie lesbar; für eine Fläche dahinter siehe
      <a href="#grafik-texthintergrund">Hintergrund</a>. Die Statuszeile zeigt diese Tipps, sobald ein Verbinder gewählt ist.</li>
    <li>Wird eine verbundene Form gelöscht, bleibt der Verbinder an seiner Stelle stehen (das Ende ist dann frei); Rückgängig hängt ihn wieder an.</li>
    <li><b>Text in Formen:</b> mit dem Text-Werkzeug in eine gefüllte Form klicken – der Text steht mittig und bricht auf die Breite der Form um,
      auch wenn Sie die Form später verschieben oder größer ziehen. Die Formen des Flussdiagramms und die Karten des Organigramms bringen ihn mit;
      Doppelklick bearbeitet ihn.</li>
    <li><b>Organigramm:</b> Karten aus der Bibliothek ziehen, von der obersten Karte zu den übrigen verbinden, oberste Karte wählen und
      <b>Baum anordnen</b> (von oben nach unten bzw. von links nach rechts) – gleiche Abstände, Kinder mittig unter der Karte darüber.</li>
    <li><b>Barrierefreiheit:</b> Unter <b>Dokument …</b> fügt „Ablauf des Diagramms als Beschreibung einfügen“ die Reihenfolge der Verbinder als
      Text in die Beschreibung ein („1. Start → Prozess …“) – für alle, die die Grafik nicht sehen.</li>
  </ul>
  <h3 id="grafik-ansicht">Lineale, Hilfslinien, Raster</h3>
  <p>Menü <b>Ansicht</b> (Zoom-Knopf): Lineale (<kbd>⌘</kbd><kbd>R</kbd>), Hilfslinien (<kbd>⌘</kbd><kbd>;</kbd>), Raster (<kbd>⌘</kbd><kbd>'</kbd>),
    Einrasten. Hilfslinien aus dem Lineal ziehen, zum Verschieben anfassen, zurück aufs Lineal = löschen, Doppelklick = genaue
    Position; ohne Maus über „Hilfslinien …“. Abstand und Unterteilung des Rasters stehen unter <b>Dokument …</b>.
    Hilfslinien gehören nur zur Bearbeitung – auf der Website erscheinen sie nicht.</p>
  <h3 id="grafik-masken">Beschneiden und Masken</h3>
  <p>Das <b>oberste</b> Objekt der Auswahl wirkt auf die übrigen: <b>Beschneiden</b> (<kbd>⌘</kbd><kbd>7</kbd>) zeigt nur, was in der Form liegt;
    <b>Als Maske verwenden</b> blendet nach Helligkeit (weiß zeigt, schwarz verbirgt, Verläufe blenden weich aus);
    <b>Ausstanzen per Maske</b> schneidet ein Loch. Die Form erscheint in den Ebenen in der Gruppe und lässt sich dort wählen und
    bearbeiten; <b>Maske freigeben</b> (<kbd>⌥</kbd><kbd>⌘</kbd><kbd>7</kbd>) macht wieder einzelne Objekte daraus.</p>
  <h3 id="grafik-export">Speichern und Exportieren</h3>
  <ul>
    <li>Beim Speichern entsteht eine <b>für das Web optimierte</b> Fassung (der Dialog zeigt die Größe vorher/nachher und die Genauigkeit).
      Ebenennamen, Sperren, Hilfslinien und Textrahmen bleiben für den Editor erhalten, die Website lädt nur die schlanke Datei.
      Texte in Schriften der Website werden dafür auf Wunsch in Kurven umgewandelt (empfohlen – SVG-Bilder zeigen sonst eine Ersatzschrift);
      im Editor bleiben sie Text.</li>
    <li><b>Exportieren</b>: SVG für das Web, <b>SVG für Desktop-Programme</b> (Affinity, Illustrator, Inkscape, Figma – mit Ebenen und Namen),
      <b>PDF</b> als Vektor. Pixelbilder gibt es bewusst nicht: Der Editor arbeitet nur mit Vektoren.</li>
    <li><b>Importieren</b>: SVG-Dateien über „SVG-Datei importieren …“, per Ziehen auf die Fläche oder Einfügen aus der Zwischenablage.
      Pixelbilder (PNG, JPG) werden nicht übernommen – der Editor meldet das.</li>
  </ul>

  <p id="grafiken"><b>Symbole und Blockvorschauen</b> liegen nicht in der Mediathek, sondern in der <a href="<?= e(url('/admin/grafik')) ?>">Grafik-Ablage</a> (Mediathek, Seitenleiste unten)
    (Mediathek → Neu → Grafiken verwalten). Eine <b>Blockvorschau</b> startet mit der Skizze des Kits für den gewählten Block; nach dem
    Speichern zeigt die Blockauswahl („+ Block einfügen“) auf dieser Website Ihre Fassung. „Vorschau des Kits zeigen“ stellt das
    Original wieder her. Schneller geht es direkt in der Blockauswahl: Ansicht <b>Liste mit großer Vorschau</b>, Block (und ggf.
    Variante) wählen, <b>Vorschau gestalten</b> – der Editor öffnet sich im Fenster, nach dem Speichern zeigt die Auswahl sofort die neue Vorschau.
    Weil Blockvorschauen für die ganze Website gelten, brauchen Sie dafür das Recht <b>Design</b>; Symbole und eigene Bausteine
    verlangen das Recht <b>Eigene Blöcke bauen</b>. Infografiken in der Mediathek gehen mit dem Recht zum Hochladen.</p>
