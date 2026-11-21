<?php
// SPDX-License-Identifier: MIT
// KLXM Studio – Grafik-Editor. Copyright (C) 2026 KLXM and contributors (see LICENSE)
declare(strict_types=1);

namespace Klxm\Grafik;

use Core\Http\Response;
use Core\Theme;

/**
 * Erweiterung „grafik“: Anbindung an den Core – Adressen der eigenen Dateien, Seiten der Verwaltung im Layout des Cores,
 * Angaben für das Skript in der Verwaltung (Extension::adminData) und für die Mediathek (Extension::mediaJson).
 */
final class Grafik
{
    public const NAME = 'grafik';

    /** Öffentliche Adresse einer gebauten Datei (public/assets/ext/grafik/…) */
    public static function asset(string $path): string
    {
        $x = \Core\Extensions::active()[self::NAME] ?? null;
        return $x ? $x->asset($path) : '';
    }

    /** Seite der Verwaltung: Ansicht aus views/ im Layout des Cores (Stile über css) */
    public static function page(string $view, array $vars = [], int $status = 200): Response
    {
        if (!array_key_exists('user', $vars)) $vars['user'] = app()->auth->user();
        $vars['flash'] = app()->session->takeFlash();
        $head = '';
        foreach ((array) ($vars['css'] ?? []) as $c) $head .= '<link rel="stylesheet" href="' . e(self::asset((string) $c)) . '">';
        unset($vars['css']);
        $content = $head . Theme::capture(dirname(__DIR__) . '/views/' . $view . '.php', $vars);
        if (!isset($vars['title']) && preg_match('~<h1[^>]*>(.*?)</h1>~s', $content, $m)) {
            $vars['title'] = trim((string) preg_replace('~\s+~', ' ', html_entity_decode(strip_tags($m[1]))));
        }
        $html = Theme::capture(ROOT . '/app/Admin/views/layout.php', $vars + ['content' => $content, 'view' => 'grafik/' . $view]);
        return \Core\Http\Controllers\Admin\AdminController::secure(new Response($html, $status));
    }

    /** Angaben für assets/js/grafik-admin.js (Mediathek, Overlay, Blockvorschauen) – nur mit „Dateien hochladen“ */
    public static function adminData(): ?array
    {
        if (!can('media.upload')) return null;
        return [
            'js' => self::asset('js/grafik.js'), 'css' => self::asset('css/grafik.css'),
            'boot' => url('/admin/api/grafik/boot'), 'store' => url('/admin/grafik'), 'page' => url('/admin/grafik/editor'),
            // Symbole/Blockvorschauen nur mit dem Recht der Art (Graphics::RIGHTS)
            'kinds' => array_values(array_filter(['icon', 'preview', 'part'], [Graphics::class, 'canKind'])),
        ];
    }

    /** Mediathek-API je Datei (ext.grafik): Bild aus dem Editor (Persona Bild) → Komposition und Vorlage; SVG → bearbeitbar */
    public static function mediaJson(array $m): ?array
    {
        if (!empty($m['_pool'])) return null;
        if (($m['mime'] ?? '') === \Core\Svg::MIME) return ['svg' => true];
        if (!str_starts_with((string) ($m['mime'] ?? ''), 'image/')) return null;
        $cg = Compose::forMedia((int) $m['id']);
        return $cg ? ['compose' => $cg, 'tpl' => Compose::templateOf($cg)] : null;
    }
}
