<?php
// SPDX-License-Identifier: MIT
// KLXM Studio – Grafik-Editor. Copyright (C) 2026 KLXM and contributors (see LICENSE)
declare(strict_types=1);

namespace Klxm\Grafik;

use Core\Fields;
use Core\Hotspots as CoreHotspots;
use Core\Media;
use Core\Svg;

/**
 * Modus „Markierungen“ des Grafik-Editors für den Block „Interaktives Bild“ des Cores (Extension::hotspotEditor, extension.php).
 * Der Editor öffnet eingebettet mit ?format=hotspots&media={id}&embed=1; Daten kommen per postMessage vom Feld und gehen dorthin
 * zurück (assets/js/grafik/shell.js, hotspots.js). Gespeichert wird nichts – das macht das Formular des Cores.
 */
final class Hotspots
{
    /**
     * Modus „Markierungen“ (format=hotspots&media={id}): SVG in der Größe des Bildes mit dem Bild als gesperrter Grundlage
     * (data-hs-base). Koordinaten im Editor = Pixel des Bildes; hotspots.js rechnet beim Übernehmen in Prozent um (Core\Hotspots).
     * Felder der Seitenleiste (Titel, Text, Link, Glossar, Symbol) rendert der Core (Fields), damit Linkauswahl, Rich-Text und
     * Symbolauswahl dieselben sind wie im Formular.
     */
    public static function boot(int $mediaId): array
    {
        $m = $mediaId > 0 ? Media::find($mediaId) : null;
        if (!$m || !str_starts_with((string) ($m['mime'] ?? ''), 'image/')) return ['error' => __('Zuerst ein Bild wählen.')];
        // gleiche Maße wie Core\Hotspots::render (viewBox der Flächen): Bildgröße, ohne Angaben 1000 × 1000
        $w = (int) ($m['width'] ?? 0);
        $h = (int) ($m['height'] ?? 0);
        if ($w <= 0 || $h <= 0) [$w, $h] = [1000, 1000];
        $src = ($m['mime'] ?? '') === Svg::MIME ? Media::url($m) : Media::url($m, min(2000, max(800, $w)));
        $alt = Media::alt($m);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $w . ' ' . $h . '" width="' . $w . '" height="' . $h . '" data-ge-hotspots="1">'
            . '<image data-hs-base="1" data-locked="1" data-name="' . e(__('Bild')) . '" href="' . e($src) . '" x="0" y="0" width="' . $w . '" height="' . $h . '" preserveAspectRatio="none"/></svg>';
        $fields = [
            ['name' => 'title', 'label' => __('Titel'), 'type' => 'text', 'max' => 160],
            ['name' => 'text', 'label' => __('Text'), 'type' => 'richtext', 'max' => CoreHotspots::TEXT_MAX],
            ['name' => 'link', 'label' => __('Link (optional)'), 'type' => 'link'],
            ['name' => 'link_label', 'label' => __('Beschriftung des Links'), 'type' => 'text', 'max' => 60, 'placeholder' => __('Mehr erfahren')],
        ];
        $terms = CoreHotspots::termOptions();
        if ($terms) $fields[] = ['name' => 'term', 'label' => __('Glossar-Begriff (optional)'), 'type' => 'select', 'options' => ['' => __('– keiner –')] + $terms,
            'help' => __('Zeigt die Kurz-Erklärung des Begriffs (auch ohne eigenen Text) und verlinkt ins Glossar.')];
        $fields[] = ['name' => 'icon', 'label' => __('Symbol statt Nummer (optional)'), 'type' => 'icon'];
        $form = '';
        foreach ($fields as $f) $form .= Fields::renderField($f, null, [], 'hs', 'hs');
        $doc = ['format' => 'hotspots', 'target' => 'hotspots', 'kind' => null, 'svg' => $svg, 'name' => (string) (($m['title'] ?? '') ?: pathinfo((string) $m['file'], PATHINFO_FILENAME)),
            'alt' => $alt, 'title' => '', 'mediaId' => (int) $m['id'], 'id' => null, 'block' => null, 'ref' => null, 'versions' => []];
        return ['doc' => $doc, 'hs' => ['max' => CoreHotspots::MAX, 'maxPoints' => CoreHotspots::MAX_POINTS, 'width' => $w, 'height' => $h,
            'media' => (int) $m['id'], 'src' => $src, 'alt' => $alt, 'form' => $form, 'terms' => $terms]];
    }

}
