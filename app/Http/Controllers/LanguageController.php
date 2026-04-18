<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;


class LanguageController extends Controller
{
    public function languageSwitch(Request $request)
    {
        $supportedLocales = ['pt', 'en', 'de'];
        $language = strtolower((string) $request->input('language', config('app.locale')));

        if (! in_array($language, $supportedLocales, true)) {
            $language = config('app.fallback_locale', 'pt');
        }

        session([
            'language' => $language,
            'locale' => $language,
        ]);

        app()->setLocale($language);

        return redirect()->back()->with(['language_switched' => $language]);
    }
}
