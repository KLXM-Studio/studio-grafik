<?php
// SPDX-License-Identifier: MIT
// KLXM Studio – Grafik-Editor. Copyright (C) 2026 KLXM and contributors (see LICENSE)
/**
 * Erweiterung „grafik“: Grafik-Editor für KLXM Studio – Infografiken, Symbole, Blockvorschauen, eigene Bausteine, Bilder mit
 * Foto und Text, Vorlagen zum Ausfüllen; Personas Vektor, Motion, Daten, Export, Bild.
 *
 * Nur der Editor lebt hier. Daten (Tabellen graphics, graphic_versions, graphic_sources, graphic_media, graphic_shared) und die
 * Anzeige auf der Website (Core\Graphics\Store, Themed, Motion, Bind) gehören zum Core – ohne dieses Paket erscheinen vorhandene
 * Grafiken unverändert, nach dem Einschalten sind sie wieder bearbeitbar.
 *
 * Einschalten je Website: Administration → Funktionen & Erweiterungen oder 'extensions' => ['grafik'].
 * Rechte: „Dateien hochladen und bearbeiten“ (media.upload) für den Editor; Blockvorschauen zusätzlich design.edit, Symbole und
 * Bausteine blocks.build, Vorlagen gestalten graphics.templates (eigenes Recht dieses Pakets). Doku: README.md, Handbuch und
 * Entwicklerhandbuch → Grafik-Editor.
 */
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'Klxm\\Grafik\\')) {
        $file = __DIR__ . '/src/' . substr($class, strlen('Klxm\\Grafik\\')) . '.php';
        if (is_file($file)) require $file;
    }
});

use Klxm\Grafik\Controller;
use Klxm\Grafik\Grafik;
use Klxm\Grafik\Graphics;
use Klxm\Grafik\Templates;

return [
    'name' => 'grafik',
    'label' => 'Grafik-Editor – Infografiken, Symbole, Bilder mit Foto und Text',
    'version' => '1.4.0',
    'requires' => '>=1.0.0',
    'description' => 'Grafiken direkt in der Verwaltung zeichnen und bearbeiten: Infografiken mit Design-Farben, Bewegung und Daten, Symbole, Blockvorschauen, eigene Bausteine, Bilder mit Foto und Text, Formate für soziale Netzwerke und Vorlagen zum Ausfüllen.',
    'author' => 'KLXM Crossmedia GmbH and contributors',
    'license' => 'MIT',
    'homepage' => 'https://studio.klxm.de',
    'provides' => ['Mediathek: Menü „Neu“ (Infografik, Bild mit Foto und Text, Aus Vorlage, Symbol, Blockvorschau), „Im Grafik-Editor bearbeiten“, Link „Grafik-Ablage“',
        'Seiten /admin/grafik (Grafik-Ablage) und /admin/grafik/editor', 'Blockauswahl: „Vorschau gestalten“', 'Interaktives Bild: „Im Grafik-Editor öffnen“ (Markierungen zeichnen)', 'Recht „Vorlagen für Bilder gestalten“', 'MCP-Werkzeuge list_graphic_templates, create_graphic, get_graphic_job, list_graphics', 'Befehl grafik:selftest'],
    'commands' => ['grafik:selftest', 'grafik:beispiele'],
    'usage' => function (): ?string {
        try {
            $n = (int) app()->db->fetchValue("SELECT COUNT(*) FROM graphics WHERE kind IN ('icon', 'preview', 'part', 'photo', 'template')");
        } catch (\Throwable) {
            return null;
        }
        return $n ? $n . ' ' . ($n === 1 ? 'Grafik in der Ablage' : 'Grafiken in der Ablage') : null;
    },
    'boot' => function (Core\Extension $x): void {
        // Vorlagen zum Ausfüllen (Persona Bild): anlegen, ändern, teilen, löschen – Standard: nur Administration
        $x->permissions('Grafik-Editor', [Templates::RIGHT => 'Vorlagen für Bilder gestalten (Grafik-Editor)']);

        // Routen wie bisher im Core (Adressen bleiben): Seite, Overlay-Daten, Speichern; eigene Ablage. Alle mit „media.upload“,
        // Löschen mit „media.delete“; feinere Rechte (Art der Grafik, Vorlagen) prüft der Controller.
        $x->routes(function (Core\Http\Router $r): void {
            $c = Controller::class;
            $up = 'media.upload';
            $r->get('/admin/grafik', [$c, 'index'], $up);
            $r->get('/admin/grafik/editor', [$c, 'editor'], $up);
            $r->get('/admin/api/grafik/boot', [$c, 'boot'], $up);
            $r->get('/admin/api/grafik/version', [$c, 'version'], $up);
            $r->post('/admin/api/grafik/save', [$c, 'save'], $up);
            $r->post('/admin/api/grafik/part', [$c, 'part'], $up);
            $r->post('/admin/api/grafik/part/update', [$c, 'partUpdate'], $up);
            $r->post('/admin/api/grafik/parts/import', [$c, 'partsImport'], $up);
            $r->post('/admin/api/grafik/prefs', [$c, 'prefs'], $up);
            $r->post('/admin/api/grafik/styles', [$c, 'styles'], $up);
            $r->post('/admin/api/grafik/clean', [$c, 'clean'], $up);
            $r->post('/admin/api/grafik/motion-svg', [$c, 'motionSvg'], $up);
            $r->post('/admin/api/grafik/data-values', [$c, 'dataValues'], $up);
            $r->post('/admin/api/grafik/app-icon', [$c, 'appIcon'], $up);
            $r->get('/admin/api/grafik/usage', [$c, 'usage'], $up);
            // Persona Bild: Fotos der Mediathek, Bilder speichern, Vorlagen zum Ausfüllen
            $r->get('/admin/api/grafik/photos', [$c, 'photos'], $up);
            $r->post('/admin/api/grafik/compose', [$c, 'compose'], $up);
            $r->get('/admin/api/grafik/templates', [$c, 'templates'], $up);
            $r->post('/admin/api/grafik/template', [$c, 'template'], $up);
            $r->post('/admin/api/grafik/template/delete', [$c, 'templateDelete'], $up);
            $r->post('/admin/api/grafik/assign', [$c, 'assign'], $up);
            $r->post('/admin/api/grafik/{id}/delete', [$c, 'delete'], 'media.delete');
            // Grafik-Aufträge (MCP): Auftrag lesen, nach dem Erstellen im Browser erledigt melden
            $r->get('/admin/api/grafik/job', [$c, 'job'], $up);
            $r->post('/admin/api/grafik/job/done', [$c, 'jobDone'], $up);
        });

        // MCP (Core ≥ Extension::mcpTool): Vorlagen lesen, Bild aus Vorlage beauftragen, Stand und letzte Bilder
        if (method_exists($x, 'mcpTool')) {
            $x->mcpTool(['name' => 'list_graphic_templates', 'title' => 'Grafik-Vorlagen', 'group' => 'media', 'perm' => 'media.upload',
                'description' => 'Vorlagen für Bilder (Grafik-Editor) mit ihren Feldern (key, label, type text|image|color|toggle, max_chars) und Formaten (main = Format der Vorlage, og, sq, pt, st = Story, hd). Vor create_graphic nutzen.',
                'examples' => ['Welche Grafik-Vorlagen gibt es für Social Media?'],
                'run' => fn(array $a): array => ['templates' => Klxm\Grafik\Jobs::templates()]]);
            $x->mcpTool(['name' => 'create_graphic', 'title' => 'Bild aus Vorlage beauftragen', 'group' => 'media', 'perm' => 'media.upload', 'write' => true,
                'description' => 'Bild aus einer Grafik-Vorlage erstellen lassen: template (ref aus list_graphic_templates), values (key => Text bzw. Medien-ID), optional entry {table, id} – füllt Titel, Datum, Ort, Text und Bild aus dem Eintrag (z. B. Termin) –, formats (z. B. ["sq","st"]), name, alt (Bildbeschreibung). Bestehendes Bild ändern: graphic (ID) statt template. Die Pixelbilder entstehen im Browser in Editor-Qualität: Gib der Person den finish_url-Link; nach dem Öffnen liegen die Bilder in der Mediathek (get_graphic_job meldet Medien-ID und Adresse).',
                'input' => ['template' => ['type' => 'string', 'description' => 'Vorlage, z. B. site:3'], 'values' => ['type' => 'object', 'description' => 'Feldwerte: key => Text bzw. Medien-ID (Bild)', 'additionalProperties' => true],
                    'entry' => ['type' => 'object', 'description' => '{table, id}: Werte aus einem Eintrag übernehmen', 'additionalProperties' => true],
                    'formats' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'main, og, sq, pt, st, hd'], 'name' => ['type' => 'string', 'description' => 'Dateiname'],
                    'alt' => ['type' => 'string', 'description' => 'Bildbeschreibung (Alt-Text)'], 'graphic' => ['type' => 'integer', 'description' => 'Bestehendes Bild (Komposition) mit neuen Werten']],
                'examples' => ['Mach aus dem Termin „Sommerfest“ eine Instagram-Grafik und eine Story mit unserer Vorlage.'],
                'run' => function (array $a, Core\Api\CmsService $s): array {
                    try { return Klxm\Grafik\Jobs::create($a, 'MCP'); } catch (\RuntimeException $e) { throw new Core\Api\ApiError(422, $e->getMessage()); }
                }]);
            $x->mcpTool(['name' => 'get_graphic_job', 'title' => 'Stand eines Grafik-Auftrags', 'group' => 'media', 'perm' => 'media.upload',
                'description' => 'Stand eines Auftrags aus create_graphic (id): waiting oder done, bei done die Bilder (media_id, url je Format).',
                'input' => ['id' => ['type' => 'string', 'description' => 'Auftrags-ID']], 'required' => ['id'],
                'run' => function (array $a): array {
                    $j = Klxm\Grafik\Jobs::find((string) ($a['id'] ?? '')) ?? throw new Core\Api\ApiError(404, 'Auftrag nicht gefunden.');
                    return Klxm\Grafik\Jobs::view($j);
                }]);
            $x->mcpTool(['name' => 'list_graphics', 'title' => 'Bilder aus dem Grafik-Editor', 'group' => 'media', 'perm' => 'media.upload',
                'description' => 'Letzte Bilder aus Vorlagen bzw. dem Grafik-Editor mit ihren Ausgaben je Format (media_id, url) und offene Aufträge.',
                'input' => ['limit' => ['type' => 'integer', 'description' => 'höchstens (Standard 20)']],
                'run' => fn(array $a): array => ['graphics' => Klxm\Grafik\Jobs::recent((int) ($a['limit'] ?? 20)),
                    'waiting' => array_map([Klxm\Grafik\Jobs::class, 'view'], Klxm\Grafik\Jobs::all('waiting'))]]);
        }

        // Verwaltung: kleines Skript auf jeder Seite (Mediathek-Einträge, Overlay, Knöpfe data-ge-open) – der Editor selbst
        // (grafik.js, grafik.css) lädt erst beim ersten Öffnen
        $x->adminAssets(fn(string $view): array => can('media.upload') ? ['js/grafik-admin.js'] : []);
        $x->adminData(fn(string $view): ?array => Grafik::adminData());

        // Mediathek: Bild aus dem Editor (Persona Bild) → Arbeitsfassung, Vorlage (ext.grafik)
        $x->mediaJson(fn(array $m): ?array => Grafik::mediaJson($m));
        // „Verwendet auf“ der Fotos: Bilder mit Foto und Text bekommen die Adresse zum Bearbeiten
        $x->filter('media.usages', function (array $list): array {
            foreach ($list as &$u) {
                if (is_array($u) && !empty($u['graphic']) && empty($u['url'])) $u['url'] = url('/admin/grafik/editor?id=' . (int) $u['graphic']);
            }
            unset($u);
            return $list;
        });
        // „Wer arbeitet gerade wo“ im Editor (Präsenz): Grafiken der Mediathek bzw. der Ablage
        $x->filter('presence.object', function (array $v, array $ctx): ?array {
            if ($ctx['type'] !== 'graphic' || !preg_match('~^(media|graphic):(\d+)$~', (string) $ctx['id'], $m) || !can('media.upload')) return null;
            if ($m[1] === 'media') $ok = \Core\Media::find((int) $m[2]) !== null;
            else { $g = Graphics::find((int) $m[2]); $ok = $g !== null && Graphics::canKind((string) $g['kind']); }
            if (!$ok) return null;
            $last = app()->db->fetch('SELECT user_id, created_at FROM graphic_versions WHERE ref = ? ORDER BY id DESC LIMIT 1', [(string) $ctx['id']]);
            return ['allowed' => true, 'last' => $last ?: null];
        });
        // Blockauswahl: „Vorschau gestalten“ (Blockvorschauen gelten für die ganze Website: design.edit)
        $x->previewEditor(fn(): ?array => Graphics::canKind('preview') ? ['url' => url('/admin/grafik/editor'), 'label' => __('Vorschau gestalten'),
            'title' => __('Vorschaugrafik dieses Blocks für diese Website zeichnen')] : null);

        // Interaktives Bild (Feld „Markierungen“): „Im Grafik-Editor öffnen“ – Punkte und Flächen auf dem Bild zeichnen (Modus
        // format=hotspots, Austausch per postMessage mit dem Feld; gespeichert wird mit dem Formular des Cores)
        $x->hotspotEditor(fn(): ?array => can('media.upload') ? ['url' => url('/admin/grafik/editor'), 'label' => __('Im Grafik-Editor öffnen'),
            'title' => __('Punkte, Kreise, Rechtecke und Flächen im Grafik-Editor auf das Bild zeichnen'), 'icon' => 'pen-nib'] : null);

        $x->docs('manual', ['grafik' => ['id' => 'grafik-editor', 'title' => 'Grafiken zeichnen und bearbeiten (Grafik-Editor)', 'after' => 'medien', 'file' => __DIR__ . '/docs/manual.php']]);
        $x->docs('technical', ['grafik' => ['title' => 'Grafik-Editor (Erweiterung)', 'part' => 2, 'after' => 'medien', 'file' => __DIR__ . '/docs/technical.php']]);

        // Zwei Beispiel-Vorlagen (examples/*.svg) anlegen, falls es sie noch nicht gibt – im Netzwerk für alle Websites
        $x->command('grafik:beispiele', 'Grafik-Editor: Beispiel-Vorlagen „Veranstaltung ankündigen“ und „Zitat“ anlegen (fehlende)',
            fn(array $args): int => Klxm\Grafik\Templates::examples());
        $x->command('grafik:selftest', 'Grafik-Editor: Ablage, Speichern, Rechte, Bausteine, Persona Bild, Vorlagen, Anbindung an den Core',
            fn(array $args): int => Klxm\Grafik\SelfTest::cli());
    },
];
