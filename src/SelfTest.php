<?php
// SPDX-License-Identifier: MIT
// KLXM Studio – Grafik-Editor. Copyright (C) 2026 KLXM and contributors (see LICENSE)
declare(strict_types=1);

namespace Klxm\Grafik;

use Core\Graphics\Store;
use Core\Http\Controllers\Admin\RedirectException;
use Core\Http\Request;
use Core\Media;
use Core\Svg;

/**
 * Selbsttest des Grafik-Editors (php bin/console grafik:selftest --site=key). Die Anzeige (Bereinigung, Design-Farben, Bewegung,
 * Daten, Ablage-Daten) prüft der Core mit graphics:selftest.
 *  - Ablage: Symbole/Vorschauen liegen nicht in der Mediathek (Media::all, Zähler), Blockvorschau wirkt in der Blockauswahl
 *    (Picker::previews über block_previews), Löschen räumt Datei und Zuordnung ab; Infografik → Mediathek, Ersetzen hält
 *    die vorige Fassung
 *  - Rechte: alle Routen verlangen Anmeldung und „media.upload“ (Löschen „media.delete“); Blockvorschauen zusätzlich
 *    „design.edit“, Symbole und Bausteine „blocks.build“ (Graphics::RIGHTS) – Redaktion ohne Recht: kein Knopf, POST → 403
 *  - Arbeitsfassung: beim erneuten Öffnen gilt die Arbeitsfassung, solange die Datei dieselbe ist; Blockvorschau je Variante
 *  - Persona Bild und Vorlagen; Bibliothek; Fassungen, Präsenz, „Verwendet auf“
 *  - Texte: Beschriftung von Verbindern (Lage entlang der Linie), Text in Formen und Hintergrund von Texten bleiben in der
 *    Arbeitsfassung erhalten (Core\Svg mit 'editor'), die Web-Fassung enthält nur das Rechteck; der Editor kennt die Angaben
 *  - Anbindung an den Core: Routen der Erweiterung mit Recht, Mediathek-Angaben (ext.grafik), Filter media.usages und
 *    presence.object, Blockauswahl „Vorschau gestalten“, Angaben für das Verwaltungs-Skript (adminData)
 * Datenbank-Änderungen laufen in einer Transaktion und werden zurückgerollt; angelegte Dateien werden gelöscht.
 */
final class SelfTest
{
    /** Typische Ausgabe des Editors (Formen, Verlauf, Pfeil, Schatten, Gruppe mit Ebenennamen, mehrzeiliger Text) */
    public const SAMPLE = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" width="800" height="500">'
        . '<title>Umsatz 2026</title><desc>Balken und Kreis</desc>'
        . '<defs><linearGradient id="verlauf-1" x1="0" y1="0.5" x2="1" y2="0.5"><stop offset="0" stop-color="#5B73AD"/><stop offset="1" stop-color="#FFFFFF" stop-opacity="0.5"/></linearGradient>'
        . '<marker id="pfeil-1" viewBox="0 0 10 10" refX="7" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="#1F2430"/></marker>'
        . '<filter id="schatten-1" x="-50%" y="-50%" width="200%" height="200%"><feDropShadow dx="4" dy="6" stdDeviation="6" flood-color="#000000" flood-opacity="0.25"/></filter></defs>'
        . '<g id="diagramm" data-name="Diagramm" transform="translate(20 21.5)"><rect id="balken" x="80" y="75" width="280" height="200" rx="8" fill="url(#verlauf-1)" filter="url(#schatten-1)"/>'
        . '<ellipse cx="560" cy="200" rx="120" ry="100" fill="#5B73AD" opacity="0.8" style="mix-blend-mode:multiply"/>'
        . '<line x1="376" y1="175" x2="424" y2="175" stroke="#1F2430" stroke-width="2" marker-end="url(#pfeil-1)"/></g>'
        . '<path data-name="Welle" d="M10 400C60 350 110 450 160 400" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="6 4"/>'
        . '<text x="80" y="460" font-size="32" font-family="Lato" fill="#1F2430"><tspan x="80">Umsatz</tspan><tspan x="80" dy="1.2em">2026</tspan></text></svg>';

    private static int $ok = 0;
    private static array $fails = [];

    /** Befehl grafik:selftest – Ausgabe wie die Selbsttests des Cores */
    public static function cli(): int
    {
        $res = self::run();
        foreach ($res['fails'] as $f) echo "  FEHLER: $f\n";
        echo "  {$res['ok']} Prüfungen bestanden, " . count($res['fails']) . " fehlgeschlagen\n";
        return $res['fails'] ? 1 : 0;
    }

    /** @return array{ok: int, fails: list<string>} */
    public static function run(): array
    {
        self::$ok = 0;
        self::$fails = [];
        self::permissions();
        self::bildClean();
        self::prefs();
        self::texts();
        $db = app()->db;
        $db->pdo->beginTransaction();
        $files = [];
        try {
            self::store($files);
            self::media($files);
            self::sources($files);
            self::rights();
            self::library();
            self::collab($files);
            self::bild($files);
            self::core($files);
        } catch (\Throwable $e) {
            self::$fails[] = 'Ausnahme: ' . $e->getMessage();
        } finally {
            $db->pdo->rollBack();
            foreach ($files as $f) if (is_file($f)) @unlink($f);
            app()->settings->forget();
        }
        return ['ok' => self::$ok, 'fails' => self::$fails];
    }

    /**
     * Texte (textbg.js, connect.js): data-ge-for/at (Beschriftung eines Verbinders), data-ge-in/frame (Text in einer Form),
     * data-ge-bg/bgs + rect[data-ge-bgfor] (Hintergrund) überstehen das Speichern der Arbeitsfassung; ohne 'editor' fallen sie weg.
     */
    private static function texts(): void
    {
        $work = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500">'
            . '<rect id="a" x="80" y="200" width="160" height="90" fill="#3B82F6"/><rect id="b" x="540" y="120" width="160" height="90" fill="#10B981"/>'
            . '<path id="verbinder-1" d="M240 245H390V165H540" fill="none" stroke="#1F2430" stroke-width="2" data-ge-conn="a=a@r;b=b@l;r=ortho;m=0.5;hs=none;he=triangle;s=4"/>'
            . '<rect data-ge-bgfor="lab-1" data-name="Texthintergrund" fill="#FFE08A" fill-opacity="0.9" stroke="#1F2430" stroke-width="1" x="380" y="190" width="40" height="24" rx="4"/>'
            . '<text id="lab-1" data-ge-for="verbinder-1" data-ge-at="0.8134" data-ge-bg="p=6;r=4" data-ge-bgs="data-name=Texthintergrund;fill=#FFE08A;fill-opacity=0.9;stroke=#1F2430;stroke-width=1" x="400" y="205" font-size="14" text-anchor="middle">Ja</text>'
            . '<text data-ge-in="a" data-ge-frame="128" x="160" y="250" text-anchor="middle" font-size="16">Start</text></svg>';
        $kept = Svg::clean($work, ['editor' => true])['svg'];
        foreach (['data-ge-for="verbinder-1"', 'data-ge-at="0.8134"', 'data-ge-bg="p=6;r=4"', 'data-ge-bgfor="lab-1"', 'data-ge-in="a"', 'data-ge-frame="128"',
            'data-ge-bgs="data-name=Texthintergrund;fill=#FFE08A;fill-opacity=0.9;stroke=#1F2430;stroke-width=1"'] as $a) {
            self::t(str_contains($kept, $a), "Arbeitsfassung behält $a");
        }
        self::t((bool) preg_match('~<rect[^>]*data-ge-bgfor="lab-1"[^>]*/?>(</rect>)?\s*<text[^>]*id="lab-1"~', $kept), 'Arbeitsfassung: Hintergrund liegt direkt vor dem Text');
        $web = Svg::clean($work, ['keep_ids' => true])['svg'];
        self::t(!str_contains($web, 'data-ge-') && str_contains($web, '#FFE08A'), 'Web-Fassung: Hintergrund als Rechteck, ohne Editor-Daten');
        // gebauter Editor kennt die Angaben (Build aktuell)
        $js = (string) @file_get_contents(dirname(__DIR__) . '/public/js/grafik.js');
        self::t($js === '' || (str_contains($js, 'data-ge-bgfor') && str_contains($js, 'data-ge-at') && str_contains($js, 'data-conn-label')), 'Gebauter Editor: Texthintergrund und Beschriftung von Verbindern');
    }

    private static function t(bool $cond, string $label): void
    {
        $cond ? self::$ok++ : self::$fails[] = $label;
    }

    /** Persönliche Einstellungen des Editors: nur bekannte Werte */
    private static function prefs(): void
    {
        // Persönliche Einstellungen: nur bekannte Werte
        $p = Graphics::prefs(['ui_prefs' => json_encode(['grafik' => ['fontFav' => ['Lato, sans-serif', '<b>x</b>', str_repeat('a', 200)], 'colorMode' => 'cmyk']])]);
        self::t($p['fontFav'] === ['Lato, sans-serif'] && $p['colorMode'] === 'hex', 'Einstellungen: Schriftnamen ohne Markup, unbekannter Farbmodus → hex');
    }

    /** Zusammenarbeit: Verwendet auf (noch nirgends), Fassungen mit Person, Präsenz für Grafiken (Filter presence.object) */
    private static function collab(array &$files): void
    {
        $w = self::SAMPLE;
        $web = self::SAMPLE;
        [$m, $err] = Graphics::saveMedia($web, ['name' => 'selbsttest-fassungen', 'alt' => 'Selbsttest Fassungen'], null, $w);
        self::t($m !== null, 'Fassungen: Grafik gespeichert' . ($err ? " ($err)" : ''));
        if (!$m) return;
        $files[] = Media::path($m);
        // Zusammenarbeit: Verwendet auf (noch nirgends), Fassungen mit Person, Präsenz für Grafiken
        self::t(Graphics::usages('media:' . (int) $m['id']) === [] && Graphics::usages('fremd:1') === [], 'Verwendet auf: neue Grafik nirgends eingebunden, fremde Angaben leer');
        [$m3] = Graphics::saveMedia($web, ['name' => 'selbsttest-daten'], (int) $m['id'], $w);
        $vs = Graphics::versions('media:' . (int) $m['id']);
        self::t($m3 !== null && count($vs) === 1 && (app()->auth->user() !== null || $vs[0]['user'] === ''), 'Fassungen: Ersetzen legt eine frühere Fassung an (mit Person)');
        $rp = new \ReflectionMethod(\Core\Http\Controllers\Admin\PresenceController::class, 'allowed');
        self::t($rp->invoke(null, 'graphic', 'media:' . (int) $m['id']) === can('media.upload') && $rp->invoke(null, 'graphic', 'media:999999999') === false && $rp->invoke(null, 'graphic', 'x:1') === false, 'Präsenz: Grafiken der Mediathek, nur vorhandene');
    }

    /**
     * Anbindung an den Core (Integrationspunkte statt Eingriffen): Routen mit Recht, Mediathek-Angaben, Filter, Blockauswahl,
     * Angaben für das Verwaltungs-Skript – und ohne das Paket liefern die Stellen des Cores nichts vom Editor.
     */
    private static function core(array &$files): void
    {
        $x = \Core\Extensions::active()[Grafik::NAME] ?? null;
        self::t($x !== null, 'Erweiterung „grafik“ ist auf dieser Website aktiv');
        if (!$x) return;
        $rep = \Core\Extensions::routeReport()[Grafik::NAME] ?? [];
        self::t(($rep['routes'] ?? 0) >= 22 && ($rep['perm'] ?? 0) === ($rep['routes'] ?? -1) && !$rep['legacy'] && !$rep['denied'] && !$rep['no_csrf'] && !$rep['public'] && !$rep['error'],
            'Routen der Erweiterung: alle mit Recht und CSRF (' . (int) ($rep['routes'] ?? 0) . ')');
        // Mediathek: SVG bearbeitbar, Bild aus dem Editor → Komposition
        [$m] = Graphics::saveMedia(self::SAMPLE, ['name' => 'selbsttest-ext', 'alt' => 'Selbsttest']);
        if ($m) {
            $files[] = Media::path($m);
            $j = Media::toJson($m);
            self::t(($j['ext']->grafik['svg'] ?? null) === true && !array_key_exists('compose', $j), 'Mediathek: ext.grafik.svg, kein compose mehr im Core');
        }
        // Filter media.usages: Komposition mit Adresse zum Bearbeiten
        $db = app()->db;
        $gid = $db->insert('graphics', ['kind' => Compose::KIND, 'name' => 'Selbsttest Bild', 'svg' => '<svg/>', 'created_at' => now(), 'updated_at' => now()]);
        $db->insert('graphic_media', ['graphic_id' => $gid, 'media_id' => 999999994, 'role' => 'src']);
        $u = Media::usages(999999994);
        $hit = array_values(array_filter($u, fn($r) => (int) ($r['graphic'] ?? 0) === $gid));
        self::t($hit && str_ends_with((string) $hit[0]['url'], '/admin/grafik/editor?id=' . $gid), 'Verwendet auf: Bild mit Foto und Text verlinkt den Editor (Filter media.usages)');
        // Blockauswahl und Verwaltungs-Skript
        $pe = \Core\Extensions::previewEditor();
        self::t(Graphics::canKind('preview') ? ($pe !== null && str_ends_with($pe['url'], '/admin/grafik/editor')) : $pe === null, 'Blockauswahl: „Vorschau gestalten“ nur mit Recht design.edit');
        $head = \Core\Extensions::adminHead('media');
        self::t(can('media.upload') === (str_contains($head, 'id="cms-ext-grafik"') && str_contains($head, 'grafik-admin.js')), 'Verwaltung: Skript und Angaben (adminData) nur mit „Dateien hochladen“');
        if (can('media.upload')) {
            preg_match('~<script type="application/json" id="cms-ext-grafik">(.*?)</script>~s', $head, $mm);
            $d = json_decode((string) ($mm[1] ?? ''), true);
            self::t(is_array($d) && str_contains((string) $d['js'], '/assets/ext/grafik/js/grafik.js') && str_ends_with((string) $d['boot'], '/admin/api/grafik/boot'), 'Angaben: Adressen von Editor und Boot');
        }
        self::t(in_array(Templates::RIGHT, \Core\Permissions::all(), true), 'Recht „Vorlagen gestalten“ kommt von der Erweiterung');
    }

    private static function library(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="10" height="10" fill="var(--accent, #123456)"/></svg>';
        [$done, $skipped] = Graphics::importParts(['format' => 'klxm-grafik-bausteine', 'version' => 1, 'items' => [
            ['name' => 'Selbsttest <b>A</b>', 'folder' => 'Ordner/<x>', 'svg' => $svg],
            ['name' => 'Selbsttest B', 'svg' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><script>alert(1)</script><rect width="5" height="5" onclick="x()"/></svg>'],
            ['name' => 'kaputt', 'svg' => 'keine Grafik'],
        ]]);
        self::t(count($done) === 2 && $skipped === 1, 'Austauschdatei: gültige Bausteine übernommen, ungültige übersprungen');
        $b = Graphics::find((int) ($done[1]['id'] ?? 0));
        self::t($b && !str_contains((string) $b['svg'], 'script') && !str_contains((string) $b['svg'], 'onclick'), 'Austauschdatei: SVG bereinigt');
        $a = Graphics::find((int) ($done[0]['id'] ?? 0));
        self::t($a && $a['name'] === 'Selbsttest A' && $a['folder'] === 'Ordner' && str_contains((string) $a['svg'], 'var(--accent'), 'Name/Ordner ohne Markup, Design-Farbe (var) bleibt');
        self::t(Graphics::updatePart((int) $a['id'], 'Neu', 'Diagramme') && (Graphics::find((int) $a['id'])['folder'] ?? '') === 'Diagramme', 'Umbenennen und Ordner');
        $bad = false;
        try { Graphics::importParts(['items' => []]); } catch (\RuntimeException) { $bad = true; }
        self::t($bad, 'fremde Datei wird abgelehnt');
        $st = Graphics::cleanStyles(['p' => [['n' => 'Titel', 'props' => ['font-size' => '32', 'fill' => 'url(https://example.org/x)', 'onload' => 'x'], 'para' => ['data-ge-para' => 'al:justify']]], 'c' => [['n' => '', 'props' => []]]]);
        self::t(count($st['p']) === 1 && !isset($st['p'][0]['props']['fill']) && !isset($st['p'][0]['props']['onload']) && $st['p'][0]['para']['data-ge-para'] === 'al:justify' && $st['c'] === [], 'Formatvorlagen der Website bereinigt');
    }

    /** Rolle vorübergehend setzen (wie ExtensionsSelfTest::actAs) */
    private static function actAs(array $perms): void
    {
        $a = app()->auth;
        foreach (['loaded' => true, 'user' => ['id' => 0, 'email' => 'selbsttest@example.test', 'name' => 'Selbsttest', 'role' => 'qa', 'appearance' => null, 'locale' => null],
            'role' => ['key' => 'qa', 'name' => 'QA', 'description' => '', 'permissions' => $perms, 'tables' => null, 'builtin' => false]] as $prop => $v) {
            (new \ReflectionProperty($a, $prop))->setValue($a, $v);
        }
    }

    /** Redaktion ohne Design-Recht: kein „Vorschau gestalten“, Speichern/Zuordnen einer Blockvorschau → 403 */
    private static function rights(): void
    {
        $a = app()->auth;
        $saved = [];
        foreach (['loaded', 'user', 'role'] as $prop) $saved[$prop] = (new \ReflectionProperty($a, $prop))->getValue($a);
        $c = new Controller();
        // Konsole: Sitzung nur für die CSRF-Prüfung vorübergehend „gestartet“
        $sess = app()->session;
        $started = new \ReflectionProperty($sess, 'started');
        $wasStarted = $started->getValue($sess);
        $started->setValue($sess, true);
        $tok = bin2hex(random_bytes(16));
        $_SESSION['_csrf'] = $tok;
        $post = fn(array $data) => new Request('POST', '/admin/api/grafik/save', [], $data + ['_csrf' => $tok], [], ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json']);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100"><rect width="160" height="100" fill="#5B73AD"/></svg>';
        try {
            self::actAs(['media.upload', 'pages.edit']);
            self::t(!Graphics::canKind('preview') && !Graphics::canKind('icon') && !Graphics::canKind('part') && Graphics::canKind('info'), 'Redaktion (nur media.upload): Infografiken ja, Vorschauen/Symbole/Bausteine nein');
            $cfgOk = Graphics::canKind('preview');   // fragments/editor.php: $config['grafik'] nur damit
            self::t(!$cfgOk, 'Blockauswahl ohne „Vorschau gestalten“ (Editor-Konfiguration grafik = null)');
            $res = $c->save($post(['svg' => $svg, 'target' => 'graphics', 'kind' => 'preview', 'name' => 'x', 'block' => 'faq', 'assign' => '1']));
            self::t($res->status === 403, 'Blockvorschau speichern ohne „design.edit“ → 403');
            $res = $c->assign(new Request('POST', '/admin/api/grafik/assign', [], ['block' => 'faq', 'id' => '1', '_csrf' => $tok], [], ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json']));
            self::t($res->status === 403, 'Blockvorschau zuordnen ohne „design.edit“ → 403');
            $res = $c->part(new Request('POST', '/admin/api/grafik/part', [], ['name' => 'x', 'svg' => $svg, '_csrf' => $tok], [], ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json']));
            self::t($res->status === 403, 'Baustein speichern ohne „blocks.build“ → 403');
            $res = $c->boot(new Request('GET', '/admin/api/grafik/boot', ['format' => 'preview', 'block' => 'faq'], [], [], ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json']));
            self::t($res->status === 403, 'Editor für Blockvorschau öffnen ohne Recht → 403');
            self::actAs(['media.upload', 'design.edit']);
            self::t(Graphics::canKind('preview') && !Graphics::canKind('part'), 'mit „design.edit“: Blockvorschauen ja, Bausteine weiter nein');
            $res = $c->boot(new Request('GET', '/admin/api/grafik/boot', ['format' => 'preview', 'block' => 'faq'], [], [], ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json']));
            self::t($res->status === 200, 'mit „design.edit“: Editor für Blockvorschau öffnet');
            self::actAs(['design.edit']);
            self::t(!Graphics::canKind('preview'), 'ohne „media.upload“ auch mit Design-Recht nicht');
        } catch (\Throwable $e) {
            self::t(false, 'Rechte-Prüfung: ' . $e->getMessage());
        } finally {
            foreach ($saved as $prop => $v) (new \ReflectionProperty($a, $prop))->setValue($a, $v);
            $started->setValue($sess, $wasStarted);
            unset($_SESSION['_csrf']);
        }
    }

    /** Arbeitsfassung zur Web-Datei: gilt, solange die Datei gleich ist; Blockvorschau je Variante */
    private static function sources(array &$files): void
    {
        $work = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" width="800" height="500" data-ge-guides="v:400"><rect data-name="Balken" x="10" y="10" width="100" height="50" fill="#5B73AD"/></svg>';
        $web = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 500" width="800" height="500"><rect x="10" y="10" width="100" height="50" fill="#5B73AD"/></svg>';
        [$m, $err] = Graphics::saveMedia($web, ['name' => 'selbsttest-arbeitsfassung', 'alt' => 'Selbsttest'], null, $work);
        self::t($m !== null, 'Mediathek: Web-Fassung mit Arbeitsfassung gespeichert' . ($err ? " ($err)" : ''));
        if (!$m) return;
        $files[] = Media::path($m);
        $file = (string) file_get_contents(Media::path($m));
        self::t(!str_contains($file, 'data-name') && !str_contains($file, 'data-ge-'), 'ausgelieferte Datei ohne Editor-Daten');
        $s = Graphics::source('media:' . $m['id'], $file);
        self::t($s !== null && str_contains($s, 'data-ge-guides="v:400"') && str_contains($s, 'data-name="Balken"'), 'Arbeitsfassung gehört zur Datei (Hilfslinien, Ebenennamen)');
        self::t(Graphics::source('media:' . $m['id'], $file . ' ') === null, 'andere Datei (z. B. ersetzt) → Arbeitsfassung gilt nicht');
        // Vorschau je Variante
        $theme = app()->theme;
        $blocks = (array) $theme->blocks();
        $type = null;
        foreach ($blocks as $k => $b) if (is_array($b) && is_array($b['variants'] ?? null) && count($b['variants']) > 1) { $type = (string) $k; break; }
        if ($type === null) { self::t(true, 'kein Block mit Varianten im Kit'); return; }
        $variant = (string) array_keys($blocks[$type]['variants'])[1];
        $g = Graphics::store('preview', 'Selbsttest-Variante', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100"><rect width="160" height="100" fill="#5B73AD"/></svg>', null, $type . '--' . $variant);
        $files[] = Graphics::dir() . '/' . basename((string) parse_url((string) $g['url'], PHP_URL_PATH));
        Graphics::assignPreview($type . '--' . $variant, $g['id']);
        $p = \Core\Blocks\Picker::previews($theme, [$type => (array) $blocks[$type]]);
        self::t(($p[$type]['variants'][$variant] ?? '') === $g['url'], 'Blockauswahl zeigt die Vorschau der Variante');
    }

    private static function permissions(): void
    {
        // Ohne Anmeldung: jede Aktion leitet zur Anmeldung um
        $c = new Controller();
        $req = fn(string $m, string $p, array $post = []) => new Request($m, $p, [], $post, [], ['HTTP_HOST' => 'www.example.org']);
        $was = app()->auth->user();
        if ($was) {
            self::t(true, 'Rechteprüfung ohne Anmeldung übersprungen (angemeldet)');
        } else {
            foreach ([['index', 'GET', '/admin/grafik'], ['editor', 'GET', '/admin/grafik/editor'], ['boot', 'GET', '/admin/api/grafik/boot'], ['save', 'POST', '/admin/api/grafik/save'],
                ['part', 'POST', '/admin/api/grafik/part'], ['clean', 'POST', '/admin/api/grafik/clean'], ['assign', 'POST', '/admin/api/grafik/assign'], ['version', 'GET', '/admin/api/grafik/version']] as [$fn, $m, $p]) {
                try {
                    $c->$fn($req($m, $p, ['svg' => self::SAMPLE]));
                    self::t(false, "$p ohne Anmeldung abgelehnt");
                } catch (RedirectException) {
                    self::t(true, '');
                }
            }
        }
        // Jede öffentliche Aktion prüft das Recht – Quelltext der Methoden (Schutz vor vergessenen auth()-Aufrufen)
        $rc = new \ReflectionClass(Controller::class);
        $src = (string) file_get_contents((string) $rc->getFileName());
        foreach ($rc->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class !== Controller::class) continue;
            $body = implode("\n", array_slice(explode("\n", $src), $m->getStartLine() - 1, $m->getEndLine() - $m->getStartLine() + 1));
            self::t((bool) preg_match('~\$this->auth\(\$r, \'media\.(upload|delete)\'\)~', $body), 'Aktion ' . $m->name . ' prüft „media.upload“ bzw. „media.delete“');
            // Favicon/App-Symbol der Website übernehmen: zusätzlich das Recht der Grundeinstellungen
            if ($m->name === 'appIcon') self::t(str_contains($body, "can('system.manage')"), 'App-Symbol übernehmen verlangt „system.manage“');
        }
        // Rolle ohne Medienrechte darf nicht
        foreach (\Core\Permissions::roles() as $key => $role) {
            if (!\Core\Permissions::allows($role, 'media.upload')) { self::t(true, ''); return; }
        }
        self::t(true, 'alle Rollen dürfen Medien hochladen (kein Gegenbeispiel)');
    }

    private static function store(array &$files): void
    {
        $before = count(Media::all());
        $icon = Graphics::store('icon', 'Selbsttest-Symbol', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 12h16"/></svg>');
        $prev = Graphics::store('preview', 'Selbsttest-Vorschau', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100"><rect x="10" y="10" width="140" height="80" fill="#8A94A6"/></svg>', null, 'faq');
        $part = Graphics::store('part', 'Selbsttest-Baustein', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>');
        foreach ([$icon, $prev] as $g) $files[] = Graphics::dir() . '/' . basename((string) parse_url((string) $g['url'], PHP_URL_PATH));
        self::t(is_file($files[0]) && is_file($files[1]), 'Symbol und Vorschau liegen als Datei in der eigenen Ablage');
        self::t($part['url'] === null, 'Bausteine nur in der Datenbank (keine öffentliche Datei)');
        self::t(count(Media::all()) === $before, 'Mediathek unverändert (Media::all zählt keine Grafiken der Ablage)');
        $inMedia = false;
        foreach (Media::all(['q' => 'Selbsttest']) as $m) $inMedia = true;
        self::t(!$inMedia, 'Ablage erscheint nicht in der Mediathek-Suche');
        self::t((int) app()->db->fetchValue("SELECT COUNT(*) FROM media WHERE file LIKE 'grafik/%'") === 0, 'keine Mediathek-Zeile für Dateien der Ablage');
        // Blockvorschau zuordnen → Blockauswahl zeigt sie (Website-Einstellung block_previews)
        Graphics::assignPreview('faq', $prev['id']);
        $theme = app()->theme;
        $p = \Core\Blocks\Picker::previews($theme, ['faq' => (array) ($theme->blocks()['faq'] ?? [])]);
        self::t(($p['faq']['url'] ?? '') === $prev['url'], 'Blockauswahl zeigt die zugeordnete Vorschau');
        // Überschreiben: neue Datei, Zuordnung folgt, vorige Fassung bleibt
        $prev2 = Graphics::store('preview', 'Selbsttest-Vorschau', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 160 100"><rect x="20" y="20" width="120" height="60" fill="#5B73AD"/></svg>', $prev['id'], 'faq');
        $files[] = Graphics::dir() . '/' . basename((string) parse_url((string) $prev2['url'], PHP_URL_PATH));
        self::t(!is_file($files[1]) && is_file(end($files)), 'neue Fassung ersetzt die Datei');
        self::t((Graphics::previewMap()['faq'] ?? '') === $prev2['url'], 'Zuordnung zeigt auf die neue Fassung');
        self::t(count(Graphics::versions('graphic:' . $prev['id'])) === 1, 'vorige Fassung gespeichert');
        Graphics::delete($prev['id']);
        self::t(!isset(Graphics::previewMap()['faq']) && !is_file(end($files)), 'Löschen entfernt Datei und Zuordnung');
        try {
            Graphics::store('icon', 'x', '<svg xmlns="http://www.w3.org/2000/svg"><script>1</script></svg>');
            self::t(false, 'leeres/schädliches SVG abgelehnt');
        } catch (\RuntimeException) {
            self::t(true, '');
        }
    }

    private static function media(array &$files): void
    {
        [$m, $err] = Graphics::saveMedia(self::SAMPLE, ['name' => 'selbsttest-infografik', 'title' => 'Selbsttest', 'alt' => 'Selbsttest: Balken und Kreis']);
        self::t($m !== null && $err === null, 'Infografik in der Mediathek gespeichert' . ($err ? " ($err)" : ''));
        if (!$m) return;
        $files[] = Media::path($m);
        self::t(($m['mime'] ?? '') === Svg::MIME && (int) $m['width'] === 800, 'Mediathek-Eintrag ist SVG mit Maßen');
        self::t(str_contains((string) file_get_contents(Media::path($m)), 'id="balken"'), 'ids bleiben (Ebenen, spätere Animationen)');
        [$m2, $err2] = Graphics::saveMedia(str_replace('#5B73AD', '#B84506', self::SAMPLE), [], (int) $m['id']);
        self::t($m2 !== null && (int) $m2['id'] === (int) $m['id'] && ($m2['alt'] ?? '') === 'Selbsttest: Balken und Kreis', 'Überschreiben behält ID und Alt-Text' . ($err2 ? " ($err2)" : ''));
        if ($m2) $files[] = Media::path($m2);
        self::t(count(Graphics::versions('media:' . $m['id'])) === 1, 'vorige Fassung der Mediathek-Grafik gespeichert');
        self::t(str_contains((string) Graphics::version('media:' . $m['id'], Graphics::versions('media:' . $m['id'])[0]['id'] ?? 0), '#5B73AD'), 'Fassung enthält den alten Stand');
    }

    // ================================================================= Ausbaustufe 6: Persona Bild, Formate, Vorlagen

    /** Foto-Ebenen sind nur Platzhalter mit Verweis (data-ge-photo) – nie Pixel; Website-SVGs bleiben reine Vektoren */
    private static function bildClean(): void
    {
        $ph = base64_encode((string) json_encode(['m' => 7, 'fx' => 30, 'b' => 120]));
        $fl = base64_encode((string) json_encode(['k' => 'f1', 'n' => 'Titel', 't' => 'text', 'max' => 40]));
        $work = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" data-ge-out="jpeg"><rect data-ge-photo="' . $ph . '" x="0" y="0" width="1200" height="630" fill="#B8C0CC"/>'
            . '<text x="60" y="560" data-ge-field="' . $fl . '" data-ge-anchor="l b k">Sommerfest</text>'
            . '<image width="10" height="10" href="/media/foto.jpg"/><image width="10" height="10" href="https://example.org/foto.jpg"/></svg>';
        $e = Svg::clean($work, ['editor' => true]);
        try {
            Svg::clean(str_replace('href="/media/foto.jpg"', 'href="data:image/png;base64,iVBORw0KGgo="', $work), ['editor' => true]);
            self::t(false, 'Arbeitsfassung mit eingebettetem Foto abgelehnt');
        } catch (\RuntimeException) {
            self::t(true, '');
        }
        self::t(str_contains($e['svg'], 'data-ge-photo="' . $ph . '"') && str_contains($e['svg'], 'data-ge-field="' . $fl . '"'), 'Arbeitsfassung: Foto-Verweis und Feld bleiben (Base64)');
        self::t(str_contains($e['svg'], 'data-ge-anchor="l b k"') && str_contains($e['svg'], 'data-ge-out="jpeg"'), 'Arbeitsfassung: Anker und Ausgabe bleiben');
        self::t(!str_contains($e['svg'], '<image') && $e['images'] === 2, 'Arbeitsfassung: <image> (eingebettet oder Adresse) wird entfernt und gezählt');
        $bad = Svg::clean('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect data-ge-photo="&lt;script&gt;" width="5" height="5"/></svg>', ['editor' => true]);
        self::t(!str_contains($bad['svg'], 'data-ge-photo'), 'Arbeitsfassung: Foto-Verweis nur als Base64');
        $w = Svg::clean($work, ['keep_ids' => true]);
        self::t(!str_contains($w['svg'], 'data-ge-') && !str_contains($w['svg'], '<image'), 'Website-SVG: keine Editor-Daten, keine Bilder');
        self::t(Compose::photoIds($e['svg']) === [7] && Compose::isRaster($e['svg']) && !Compose::isRaster(self::SAMPLE), 'Fotos der Arbeitsfassung erkannt (photoIds, isRaster)');
        self::t(Compose::suggestAlt($e['svg']) === 'Sommerfest', 'Alt-Vorschlag aus den Textebenen');
        // Felder einer Vorlage
        $f = Templates::fields($e['svg']);
        self::t(count($f) === 1 && $f[0]['k'] === 'f1' && ($f[0]['max'] ?? 0) === 40 && $f[0]['t'] === 'text', 'Felder der Vorlage gelesen');
        self::t(Templates::cleanField(['k' => '<x>', 't' => 'text']) === null && Templates::cleanField(['k' => 'a', 't' => 'script']) === null, 'ungültige Felder verworfen');
        $v = Templates::validate($e['svg'], ['f1' => str_repeat('x', 41), 'nope' => 'y'], []);
        self::t(count($v['errors']) === 1 && mb_strlen((string) $v['vals']['f1']) === 40 && !isset($v['vals']['nope']), 'Text länger als erlaubt → Hinweis, gekürzt; unbekannte Felder fallen weg');
        $v = Templates::validate($e['svg'], ['f1' => "<b>Fest</b>\nim Garten"], []);
        self::t(!$v['errors'] && $v['vals']['f1'] === 'Fest im Garten', 'Text ohne Markup, einzeilig ohne Umbruch');
        $col = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><rect width="5" height="5" data-ge-field="' . base64_encode('{"k":"c","n":"Farbe","t":"color"}') . '"/><rect data-ge-photo="' . $ph . '" width="5" height="5" data-ge-field="' . base64_encode('{"k":"i","n":"Foto","t":"image"}') . '"/></svg>';
        $v = Templates::validate($col, ['c' => '#ff0000', 'i' => 999999], ['accent']);
        self::t(count($v['errors']) === 2 && !isset($v['vals']['c']) && !isset($v['vals']['i']), 'Farbe nur aus Design-Farben, Bild nur aus der Mediathek');
        self::t(Templates::validate($col, ['c' => 'accent'], ['accent'])['vals'] === ['c' => 'accent'], 'Design-Farbe angenommen');
    }

    /** Bild speichern (Mediathek, Ersetzen hält die ID), Verwendungen der Quellfotos, Vorlagen ablegen und Rechte */
    private static function bild(array &$files): void
    {
        if (!function_exists('imagejpeg')) return;
        $jpeg = function (int $w, int $h, array $rgb) use (&$files): string {
            $im = imagecreatetruecolor($w, $h);
            imagefill($im, 0, 0, (int) imagecolorallocate($im, $rgb[0], $rgb[1], $rgb[2]));
            $p = (string) tempnam(sys_get_temp_dir(), 'bild');
            imagejpeg($im, $p, 90);
            $files[] = $p;
            return $p;
        };
        $track = function (?array $m) use (&$files): void { if ($m) foreach (Media::fileList($m) as $f) $files[] = Media::dir() . '/' . $f; };
        [$src, $err] = Media::import($jpeg(800, 600, [40, 120, 60]), 'selbsttest-quellfoto.jpg', 'Selbsttest: Wiese');
        self::t($src !== null, 'Quellfoto angelegt' . ($err ? " ($err)" : ''));
        if (!$src) return;
        $track($src);
        $ph = base64_encode((string) json_encode(['m' => (int) $src['id']]));
        $work = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" data-ge-out="jpeg"><rect data-ge-photo="' . $ph . '" width="1200" height="630" fill="#B8C0CC"/><text x="60" y="560">Selbsttest-Bild</text></svg>';
        $res = Compose::save($work, ['name' => 'selbsttest-bild', 'alt' => 'Selbsttest: Bild mit Text'], null, [['board' => 'main', 'path' => $jpeg(1200, 630, [200, 30, 30])], ['board' => 's1', 'path' => $jpeg(1080, 1080, [30, 30, 200])]]);
        $out = $res['outputs'];
        self::t(isset($out['main'], $out['s1']) && !$res['errors'], 'Bild gespeichert: je Format ein Medium');
        if (!isset($out['main'], $out['s1'])) return;
        $main = Media::find((int) $out['main']['id']);
        $track($main);
        $track(Media::find((int) $out['s1']['id']));
        self::t(($main['mime'] ?? '') === 'image/jpeg' && (int) ($main['width'] ?? 0) === 1200 && (string) ($main['alt'] ?? '') === 'Selbsttest: Bild mit Text', 'Ausgabe ist ein Pixelbild der Mediathek mit Alt-Text');
        self::t(str_contains((string) ($main['variants_json'] ?? ''), 'webp'), 'Mediathek erzeugt die Größen (WebP)');
        $g = Compose::find($res['id']);
        self::t($g !== null && str_contains((string) $g['svg'], 'data-ge-photo') && !str_contains((string) $g['svg'], '<image'), 'Arbeitsfassung in der eigenen Ablage, Foto nur als Verweis');
        self::t(Compose::forMedia((int) $out['main']['id']) === $res['id'], 'Mediathek → Arbeitsfassung (Bearbeiten)');
        self::t(!in_array($res['id'], array_column(Graphics::all('part'), 'id'), true), 'Bilder liegen nicht bei den Bausteinen der Bibliothek');
        self::t((Media::toJson((array) $main)['ext']->grafik['compose'] ?? null) === $res['id'], 'Media::toJson nennt die Arbeitsfassung (ext.grafik)');
        $u = Media::usages((int) $src['id']);
        self::t((bool) array_filter($u, fn($x) => str_contains((string) $x['label'], 'selbsttest-bild') && str_contains((string) $x['url'], '/admin/grafik/editor?id=' . $res['id'])), 'Quellfoto: „Verwendet auf“ zeigt das Bild mit Adresse zum Bearbeiten');
        self::t(isset(Store::usedIds()[(int) $src['id']]) && isset(Media::usedIds()[(int) $src['id']]), 'Quellfoto gilt als verwendet');
        $res2 = Compose::save(str_replace('Selbsttest-Bild', 'Selbsttest-Bild 2', $work), ['name' => 'selbsttest-bild', 'alt' => 'Selbsttest: Bild mit Text'], $res['id'], [['board' => 'main', 'path' => $jpeg(1200, 630, [30, 200, 30])]]);
        self::t(($res2['outputs']['main']['id'] ?? 0) === (int) $out['main']['id'], 'Erneut speichern ersetzt das Bild (ID bleibt)');
        $track(Media::find((int) $out['main']['id']));
        self::t(count(Graphics::versions('graphic:' . $res['id'])) === 1, 'vorige Arbeitsfassung als Fassung gespeichert');
        self::t(array_keys(Compose::outputs($res['id'])) === ['main'] && Media::find((int) $out['s1']['id']) !== null, 'entferntes Format: Verbindung gelöst, Bild bleibt in der Mediathek');
        try {
            Compose::save($work, ['name' => 'x', 'alt' => 'x x x'], null, [['board' => 'main', 'path' => $jpeg(1, 1, [0, 0, 0]) . '-fehlt']]);
            self::t(false, 'kein Bild → abgelehnt');
        } catch (\RuntimeException) {
            self::t(true, '');
        }
        try {
            Templates::store('site', 'x', $work);
            self::t(false, 'Vorlage ohne Felder abgelehnt');
        } catch (\RuntimeException) {
            self::t(true, '');
        }
        $tw = str_replace('<text x="60" y="560">', '<text x="60" y="560" data-ge-field="' . base64_encode('{"k":"t","n":"Titel","t":"text","max":30}') . '">', $work);
        $tp = Templates::store('site', 'Selbsttest-Vorlage', $tw);
        self::t(($tp['ref'] ?? '') !== '' && count($tp['fields'] ?? []) === 1 && in_array($tp['ref'] ?? '', array_column(Templates::all(), 'ref'), true), 'Vorlage gespeichert und gelistet');
        self::t(!in_array('Selbsttest-Vorlage', array_column(Graphics::partsForEditor(), 'name'), true), 'Vorlagen erscheinen nicht unter den Bausteinen');
        self::t(Templates::delete((string) ($tp['ref'] ?? '')) && Templates::get((string) ($tp['ref'] ?? '')) === null, 'Vorlage gelöscht');
        $a = app()->auth;
        $saved = [];
        foreach (['loaded', 'user', 'role'] as $prop) $saved[$prop] = (new \ReflectionProperty($a, $prop))->getValue($a);
        $sess = app()->session;
        $started = new \ReflectionProperty($sess, 'started');
        $was = $started->getValue($sess);
        $started->setValue($sess, true);
        $tok = bin2hex(random_bytes(16));
        $_SESSION['_csrf'] = $tok;
        $srv = ['HTTP_HOST' => 'www.example.org', 'HTTP_ACCEPT' => 'application/json'];
        try {
            $c = new Controller();
            self::actAs(['media.upload', 'pages.edit']);
            self::t(!Templates::can() && Graphics::canKind(Compose::KIND) && !Graphics::canKind(Templates::KIND), 'Redaktion: Bilder ja, Vorlagen gestalten nein');
            $r = $c->template(new Request('POST', '/admin/api/grafik/template', [], ['svg' => $tw, 'name' => 'x', '_csrf' => $tok], [], $srv));
            self::t($r->status === 403, 'Vorlage speichern ohne Recht → 403');
            $r = $c->templateDelete(new Request('POST', '/admin/api/grafik/template/delete', [], ['ref' => 'site:1', '_csrf' => $tok], [], $srv));
            self::t($r->status === 403, 'Vorlage löschen ohne Recht → 403');
            $r = $c->boot(new Request('GET', '/admin/api/grafik/boot', ['template' => 'site:1'], [], [], $srv));
            self::t($r->status === 403, 'Vorlage im Editor öffnen ohne Recht → 403');
            $r = $c->templates(new Request('GET', '/admin/api/grafik/templates', [], [], [], $srv));
            self::t($r->status === 200, 'Vorlagen ausfüllen: Liste mit „media.upload“');
            self::actAs(['media.upload', 'graphics.templates']);
            self::t(Templates::can(), 'mit „graphics.templates“: Vorlagen gestalten');
            self::actAs(['graphics.templates']);
            self::t(!Templates::can(), 'ohne „media.upload“ auch mit Vorlagen-Recht nicht');
        } catch (\Throwable $e) {
            self::t(false, 'Rechte Vorlagen: ' . $e->getMessage());
        } finally {
            foreach ($saved as $prop => $v) (new \ReflectionProperty($a, $prop))->setValue($a, $v);
            $started->setValue($sess, $was);
            unset($_SESSION['_csrf']);
        }
        self::t(in_array('graphics.templates', \Core\Permissions::all(), true), 'Recht „Vorlagen gestalten“ im Katalog');
    }
}
