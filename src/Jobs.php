<?php
// SPDX-License-Identifier: MIT
// KLXM Studio – Grafik-Editor. Copyright (C) 2026 KLXM and contributors (see LICENSE)
declare(strict_types=1);

namespace Klxm\Grafik;

use Core\Data\Calendar;
use Core\Data\Entries;
use Core\Data\Tables;

/**
 * Grafik-Aufträge (MCP): Ein KI-Agent wählt eine Vorlage, füllt Texte und Bild – direkt oder aus einem Eintrag (z. B. Termin →
 * Titel, Datum, Ort, Bild) – und die Formate. Die Pixelbilder entstehen wie beim „Aus Vorlage erstellen“ im Browser (Schriften,
 * Einpassen, Fotos genau wie im Editor): Wer den Link des Auftrags öffnet, sieht die Vorschau; die Bilder werden automatisch
 * erstellt und landen in der Mediathek. Danach meldet der Auftrag Medien-IDs und Adressen.
 *
 * Ablage: storage/grafik-jobs/{website}-{id}.json (keine Datenbankänderung), Aufräumen nach 30 Tagen.
 * Formate: main (Format der Vorlage), og, sq, pt, st, hd (wie formats.js) oder Zeichenflächen der Vorlage (Name).
 */
final class Jobs
{
    public const FORMATS = ['main' => 'Format der Vorlage', 'og' => 'Link-Vorschau 1200 × 630', 'sq' => 'Beitrag quadratisch 1080 × 1080',
        'pt' => 'Beitrag hoch 1080 × 1350', 'st' => 'Story 1080 × 1920', 'hd' => 'Kopfbild 1920 × 1080'];
    private const TTL = 30 * 86400;

    private static function dir(): string
    {
        return ROOT . '/storage/grafik-jobs';
    }

    private static function file(string $id): string
    {
        return self::dir() . '/' . site()->key . '-' . (preg_match('~^[a-f0-9]{12}$~', $id) ? $id : 'x') . '.json';
    }

    public static function find(string $id): ?array
    {
        $f = self::file($id);
        if (!is_file($f)) return null;
        $d = json_decode((string) file_get_contents($f), true);
        return is_array($d) ? $d : null;
    }

    private static function put(array $job): void
    {
        if (!is_dir(self::dir())) @mkdir(self::dir(), 0775, true);
        file_put_contents(self::file($job['id']), json_encode($job, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /** Aufträge dieser Website, neueste zuerst (räumt alte auf) @return list<array> */
    public static function all(?string $status = null): array
    {
        $out = [];
        foreach (glob(self::dir() . '/' . site()->key . '-*.json') ?: [] as $f) {
            if (filemtime($f) < time() - self::TTL) { @unlink($f); continue; }
            $d = json_decode((string) file_get_contents($f), true);
            if (is_array($d) && ($status === null || ($d['status'] ?? '') === $status)) $out[] = $d;
        }
        usort($out, fn($a, $b) => strcmp((string) $b['created'], (string) $a['created']));
        return $out;
    }

    /** Vorlagen für Agenten: ref, Name, Größe, Felder, Formate */
    public static function templates(): array
    {
        return array_map(fn($t) => ['ref' => $t['ref'], 'name' => $t['name'], 'width' => $t['width'] ?? null, 'height' => $t['height'] ?? null,
            'scope' => $t['scope'] ?? 'site', 'fields' => array_map(fn($f) => array_filter(['key' => $f['k'], 'label' => $f['n'], 'type' => $f['t'],
                'max_chars' => $f['max'] ?? null, 'multiline' => $f['ml'] ?? null]), Templates::fields((string) $t['svg'])),
            'formats' => self::FORMATS], Templates::all(true));
    }

    /**
     * Werte aus einem Eintrag: Felder der Vorlage nach Schlüssel/Name zugeordnet – Titel, Datum, Ort, Text, Bild.
     * @return array<string, mixed>
     */
    public static function fromEntry(array $fields, array $t, array $e): array
    {
        $title = Entries::title($t, $e);
        $date = $place = '';
        if (Calendar::enabled($t) && ($span = Calendar::span($t, $e))) {
            $date = $span['all_day'] ? fmt()->date($span['start']->format('Y-m-d')) : fmt()->datetime($span['start']->format('Y-m-d H:i:s'));
            $cfg = Calendar::config($t);
            if (!empty($cfg['location']) && is_scalar($e[$cfg['location']] ?? null)) $place = trim(strip_tags((string) $e[$cfg['location']]));
        }
        if ($date === '') {
            foreach ($t['fields'] as $f) if (in_array($f['type'], ['date', 'datetime'], true) && !empty($e[$f['name']])) { $date = $f['type'] === 'date' ? fmt()->date((string) $e[$f['name']]) : fmt()->datetime((string) $e[$f['name']]); break; }
        }
        if ($place === '') foreach (['ort', 'location', 'veranstaltungsort', 'place'] as $k) if (is_scalar($e[$k] ?? null) && trim((string) $e[$k]) !== '') { $place = trim(strip_tags((string) $e[$k])); break; }
        $df = (string) ($t['settings']['description_field'] ?? '');
        $text = $df !== '' && is_scalar($e[$df] ?? null) ? trim(html_entity_decode(strip_tags((string) $e[$df]))) : '';
        $imgF = (string) ($t['settings']['image_field'] ?? '');
        if ($imgF === '') foreach ($t['fields'] as $f) if ($f['type'] === 'media') { $imgF = $f['name']; break; }
        $img = $imgF !== '' ? (int) ($e[$imgF] ?? 0) : 0;
        $out = [];
        foreach ($fields as $f) {
            $id = mb_strtolower($f['k'] . ' ' . $f['n']);
            if ($f['t'] === 'image') { if ($img) $out[$f['k']] = $img; continue; }
            if ($f['t'] !== 'text') continue;
            $v = match (true) {
                (bool) preg_match('~titel|title|überschrift|headline|name|thema~u', $id) => $title,
                (bool) preg_match('~datum|date|wann|zeit|termin|when~u', $id) => $date,
                (bool) preg_match('~\bort\b|location|place|wo\b|adresse~u', $id) => $place,
                (bool) preg_match('~text|beschreibung|teaser|unter|sub|info|details~u', $id) => $text,
                default => null,
            };
            if ($v !== null && $v !== '') $out[$f['k']] = mb_substr($v, 0, (int) ($f['max'] ?? 200));
        }
        return $out;
    }

    /**
     * Auftrag anlegen. $in: template (ref), values (key => Wert), entry {table, id}, formats (list), name, alt, graphic (bestehendes Bild ändern).
     * @return array Auftrag mit finish_url
     * @throws \RuntimeException verständliche Meldung
     */
    public static function create(array $in, string $by): array
    {
        $graphic = (int) ($in['graphic'] ?? 0) ?: null;
        $ref = (string) ($in['template'] ?? '');
        if ($graphic) {
            $ref = Compose::templateOf($graphic) ?? throw new \RuntimeException(__('Dieses Bild wurde nicht aus einer Vorlage erstellt.'));
        }
        $tp = Templates::get($ref) ?? throw new \RuntimeException(__('Vorlage nicht gefunden – list_graphic_templates nennt die vorhandenen (ref, z. B. site:3).'));
        $fields = Templates::fields((string) $tp['svg']);
        $vals = [];
        if (!empty($in['entry']['table']) && !empty($in['entry']['id'])) {
            $t = Tables::findContent((string) $in['entry']['table']) ?? throw new \RuntimeException(__('Tabelle nicht gefunden.'));
            $e = Entries::find($t, (int) $in['entry']['id']) ?? throw new \RuntimeException(__('Eintrag nicht gefunden.'));
            $vals = self::fromEntry($fields, $t, $e);
        }
        foreach ((array) ($in['values'] ?? []) as $k => $v) $vals[(string) $k] = $v;
        $chk = Templates::validate((string) $tp['svg'], $vals, Templates::tokenNames());
        if ($chk['errors']) throw new \RuntimeException(implode(' ', $chk['errors']));
        $fmts = array_values(array_unique(array_filter(array_map('strval', (array) ($in['formats'] ?? ['main'])), fn($f) => preg_match('~^[a-z0-9_-]{1,40}$~i', $f) === 1)));
        $job = ['id' => bin2hex(random_bytes(6)), 'status' => 'waiting', 'template' => $ref, 'template_name' => (string) $tp['name'], 'values' => $chk['vals'],
            'formats' => $fmts ?: ['main'], 'name' => mb_substr(trim((string) ($in['name'] ?? '')) ?: self::firstText($fields, $chk['vals']) ?: (string) $tp['name'], 0, 120),
            'alt' => mb_substr(trim(strip_tags((string) ($in['alt'] ?? ''))), 0, 250), 'graphic' => $graphic, 'entry' => $in['entry'] ?? null,
            'by' => $by, 'created' => date('c'), 'outputs' => []];
        self::put($job);
        return self::view($job);
    }

    /** Erster ausgefüllter Text (Titel bevorzugt) – Dateiname */
    private static function firstText(array $fields, array $vals): string
    {
        foreach ($fields as $f) if ($f['t'] === 'text' && preg_match('~titel|title~i', $f['k'] . $f['n']) && trim((string) ($vals[$f['k']] ?? '')) !== '') return trim((string) $vals[$f['k']]);
        foreach ($fields as $f) if ($f['t'] === 'text' && trim((string) ($vals[$f['k']] ?? '')) !== '') return trim((string) $vals[$f['k']]);
        return '';
    }

    /** Erledigt melden (Browser nach dem Speichern) */
    public static function done(string $id, int $graphic, array $outputs): ?array
    {
        $job = self::find($id);
        if (!$job) return null;
        $job['status'] = 'done';
        $job['graphic'] = $graphic;
        $job['done'] = date('c');
        $job['outputs'] = [];
        foreach ($outputs as $board => $m) {
            if (is_array($m) && isset($m['id'])) $job['outputs'][(string) preg_replace('~^f-~', '', (string) $board)] = ['media_id' => (int) $m['id'], 'url' => absolute_url((string) ($m['url'] ?? '')), 'width' => $m['width'] ?? null, 'height' => $m['height'] ?? null];
        }
        self::put($job);
        return $job;
    }

    /** Für Agent und Oberfläche */
    public static function view(array $job): array
    {
        return $job + ['finish_url' => absolute_url('/admin/grafik?auftrag=' . $job['id']),
            'hint' => ($job['status'] ?? '') === 'done' ? __('Fertig – die Bilder liegen in der Mediathek.')
                : __('Die Bilder entstehen im Browser in Editor-Qualität: Link öffnen (angemeldet), die Bilder werden automatisch erstellt und in der Mediathek gespeichert. Danach liefert get_graphic_job die Medien.')];
    }

    /** Letzte Bilder aus Vorlagen bzw. dem Editor (Kompositionen) mit ihren Ausgaben */
    public static function recent(int $limit = 20): array
    {
        $out = [];
        foreach (app()->db->fetchAll('SELECT id, name, tpl, updated_at FROM graphics WHERE kind = ? ORDER BY updated_at DESC LIMIT ' . max(1, min(100, $limit)), [Compose::KIND]) as $g) {
            $outs = [];
            foreach (Compose::outputs((int) $g['id']) as $board => $mid) {
                $m = \Core\Media::find((int) $mid);
                if ($m) $outs[$board] = ['media_id' => (int) $m['id'], 'url' => absolute_url(\Core\Media::url($m))];
            }
            $out[] = ['id' => (int) $g['id'], 'name' => (string) $g['name'], 'template' => $g['tpl'], 'updated' => (string) $g['updated_at'], 'outputs' => $outs];
        }
        return $out;
    }
}
