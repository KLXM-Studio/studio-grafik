# Changelog – Grafik-Editor (`klxm/studio-grafik`)

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
