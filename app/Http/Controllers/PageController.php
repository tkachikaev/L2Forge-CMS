<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Services\Localization\LanguageManager;
use Illuminate\View\View;

final class PageController
{
    public function show(Page $page): View
    {
        abort_unless($page->isLive(), 404);
        $page->loadMissing('translations');

        return view('theme::pages.show', compact('page'));
    }

    public function showLocalized(string $locale, string $slug, LanguageManager $languages): View
    {
        abort_unless($languages->isEnabled($locale), 404);
        $locale = $languages->normalizeCode($locale) ?? $languages->default();
        $candidates = array_values(array_unique([
            $locale,
            $languages->fallback(),
            $languages->default(),
            'ru',
        ]));

        $translation = null;
        foreach ($candidates as $candidate) {
            $translation = PageTranslation::query()
                ->where('locale', $candidate)
                ->where('slug', $slug)
                ->first();

            if ($translation !== null) {
                break;
            }
        }

        abort_if($translation === null, 404);

        $page = $translation->page()->with('translations')->firstOrFail();
        abort_unless($page->isLive(), 404);

        return view('theme::pages.show', compact('page'));
    }
}
