# Changelog – Grafik-Editor (`klxm/studio-grafik`)

## 1.3.0 (2026-11-23)

- Neu: **Beispiel-Vorlagen** „Veranstaltung ankündigen“ (1200 × 630, Foto, Akzentfarbe, Datum, Titel, Hinweis ein/aus) und „Zitat“
  (1080 × 1080, Hintergrundfarbe, Zitat, Name, Funktion, Anführungszeichen ein/aus) – `php bin/console grafik:beispiele` legt
  fehlende im Ordner „Beispiele“ an (im Netzwerk für alle Websites). Dateien: `examples/*.svg`.

## 1.2.0 (2026-11-22)

- Neu: **Markierungen für das interaktive Bild.** Im Feld „Markierungen“ des Core-Blocks „Interaktives Bild“ (und in Datentabellen mit
  Markierungen) öffnet **Im Grafik-Editor öffnen** das gewählte Bild im Editor (Andockstelle `Extension::hotspotEditor` des Cores, Recht
  „Dateien hochladen und bearbeiten“). Das Bild ist die gesperrte Grundlage; Werkzeuge **Punkt**, **Kreis**, **Rechteck** und **Fläche (Vieleck)** mit
  den Mitteln des Editors (Ziehen, Griffe, Eckpunkte mit dem Knoten-Werkzeug, Pfeiltasten, Tab, Entf, Rückgängig). Die Seitenleiste zeigt alle
  Markierungen in der Reihenfolge ihrer Nummer (umsortieren mit den Pfeilen) und für die gewählte **Titel**, **Text** (Rich-Text), **Link** (Linkauswahl
  des Cores), **Glossar-Begriff** und **Symbol**. **Übernehmen** schreibt die Markierungen ins Formular (gespeichert wird mit dem Formular),
  **Abbrechen** verwirft sie. Ohne Bild erscheint ein Hinweis.
- Austausch mit dem Feld per `postMessage` (gleiche Herkunft, nur mit dem Fenster darüber): `ready` → `init` → `apply`/`close`, Format
  `Core\Hotspots` Version 1 in Prozent; Hin- und Rückweg sind verlustfrei (`init` → `apply` ohne Änderung ergibt dieselben Daten).
- Selbsttest `grafik:selftest`: Knopf nur mit Recht, Bild als gesperrte Grundlage in Bildgröße, Felder des Cores, Hinweis ohne Bild, Format stabil für
  `Core\Hotspots::normalize`.

English: new hotspot mode for the core “Interactive image” block – “Open in graphics editor” in the hotspots field draws points, circles,
rectangles and polygons on the fixed image with the editor's tools; sidebar with order (= number), title, text, link picker, glossary term and
icon; “Apply” writes the hotspots back to the form (lossless percent conversion), “Cancel” discards.

## 1.1.0 (2026-11-22)

- Behoben: Beim Bearbeiten eines zentrierten oder rechtsbündigen Texts mit Textrahmen – vor allem bei Text in Formen („Start“ in einem
  Rechteck) – begann der gestrichelte Rahmen in der Mitte des Texts und ragte rechts aus der Form. Der Rahmen liegt jetzt je nach Ausrichtung
  links, mittig oder rechts (`frameSpan()`), die Breite zieht sich bei „Mitte“ zu beiden Seiten, bei „Rechts“ am linken Griff; Text in Formen
  hat keinen Breitengriff mehr (die Breite folgt der Form). Klicks im Rahmen gelten auch links vom Ankerpunkt.
- Behoben: Das unsichtbare Eingabefeld (Wortvorschläge, Bildschirmtastatur auf Telefon/Tablet) blieb nach Klick in den Text, Zoom oder
  Verschieben der Ansicht an der alten Stelle – es folgt jetzt bei jedem Zeichnen der Schreibmarke.
- Behoben: Ebenenname eines umbrochenen Texts ohne Leerzeichen zwischen den Zeilen („mitUmbruch“).
- Neu: **Verbinder beschriften** leichter auffindbar und vollständig: ↵ auf einem gewählten Verbinder, Doppelklick auf die Linie,
  Feld „Beschriftung“ in der Kontextleiste, Kontextmenü „Beschriftung hinzufügen/bearbeiten/in die Mitte/entfernen“, Tipp in der Statuszeile.
  Die Beschriftung lässt sich **entlang der Linie ziehen** (rastet ein, `data-ge-at`) und bleibt an ihrer Stelle, wenn Formen sich bewegen.
- Neu: **Hintergrund für Texte** (Bereich Zeichen → Hintergrund): Fläche hinter freiem Text, Text in Formen und Beschriftungen mit Farbe,
  Deckkraft, Innenabstand, Eckenradius und Rahmen; passt sich Text, Schriftgröße, Textrahmen, Verschieben, Skalieren und Drehen an, wandert
  beim Kopieren/Einfügen mit, wird mit dem Text gelöscht. Auf der Website ein gewöhnliches Rechteck; Kontrastanzeige und „Prüfen“ messen gegen
  die Fläche; Design- und Dunkel-Farben über „Design-Farben und Dunkel-Farbe …“.
- Selbsttest `grafik:selftest`: Beschriftung, Text in Formen und Texthintergrund überstehen die Arbeitsfassung (`Core\Svg` mit `editor`),
  die Web-Fassung enthält nur das Rechteck.

English: fixed the offset text frame when editing centred/right-aligned text (notably text inside shapes); the hidden input now follows the
caret (IME/on-screen keyboard). New: connector labels via ↵, double-click, context bar field and context menu, draggable along the line; text
background (colour, opacity, padding, corner radius, border) that follows the text and is checked for contrast.

## 1.0.1 (2026-11-21)

- Behoben: Alt-Text-Vorschlag (Vorlage ausfüllen, Prüfen) verband umbrochene Textzeilen ohne Leerzeichen („Werkstattfestam See“).

## 1.0.0

Erste Fassung als eigenes Paket. Bis dahin war der Grafik-Editor Teil des Cores von KLXM Studio (Ausbaustufen 1–6, siehe
CHANGELOG des Cores); Funktionen, Adressen, Rechte und gespeicherte Daten sind unverändert.

- **Herausgelöst aus dem Core:** Editor (`assets/js/grafik.js`, `assets/js/grafik/*`, `assets/css/grafik.css`), Bibliotheken auf Abruf
  (Paper.js mit paperjs-offset, fontkit, jsPDF mit svg2pdf.js, hyphen mit Trennmustern – `THIRD-PARTY-NOTICES.md`), Seiten und JSON-API
  (`Klxm\Grafik\Controller`: `/admin/grafik`, `/admin/grafik/editor`, `/admin/api/grafik/*`), Ablage und Speichern (`Graphics`), Bilder mit
  Foto und Text (`Compose`), Vorlagen zum Ausfüllen (`Templates`), Bibliothek (`Library`), Bausteine für alle Websites (`Shared`).
- **Andocken nur über Integrationspunkte** des Cores: Routen mit Recht (`media.upload`, Löschen `media.delete`), `adminAssets()` +
  `adminData()` (Skript `grafik-admin.js` mit Overlay-Lader, `CMSAdmin.graphic.open/fill`, Knöpfe `data-ge-open`), Mediathek-Haken
  `create` (Menü „Neu“), `edit` („Im Grafik-Editor bearbeiten“, „Felder der Vorlage ändern“), `side` („Grafik-Ablage“), `mediaJson()`
  (`ext.grafik`), Filter `media.usages` (Bild mit Foto und Text verlinkt den Editor) und `presence.object` (wer arbeitet gerade an der
  Grafik), `previewEditor()` („Vorschau gestalten“ in der Blockauswahl, Nachricht `klxm-preview-editor`), `permissions()`
  (`graphics.templates`), `docs()` (Handbuch, Entwicklerhandbuch), `command()` (`grafik:selftest`).
- **Daten bleiben im Core** (Tabellen `graphics`, `graphic_versions`, `graphic_sources`, `graphic_media`, `graphic_shared`; Einstellungen
  `block_previews`, `graphic_text_styles`) – ohne Paket erscheinen alle Grafiken unverändert, nach dem Einschalten sind sie wieder
  bearbeitbar. Keine eigenen Tabellen, keine Migrationen.
- **Build** über den Build des Cores mit `build.mjs` (Lizenzen, Sonderfälle jsPDF/Paper.js, Spiegeln nach `public/` für
  `extensions:publish`), eigene `package.json`; Kern-Helfer über `klxm-studio/…`.
- Selbsttest `grafik:selftest` (Ablage, Speichern, Rechte, Bausteine, Persona Bild, Vorlagen, Fassungen, Präsenz, Anbindung an den Core).
  Die Anzeige prüft der Core mit `graphics:selftest`.
