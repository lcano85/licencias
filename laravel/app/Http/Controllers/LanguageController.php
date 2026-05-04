<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switchLang(Request $request)
    {
        $request->validate([
            'lang' => 'required|in:en,es'
        ]);

        $lang = $request->lang;

        // Store in session (for current request cycle)
        Session::put('locale', $lang);

        // Force session to be saved before redirect (avoids race where next request runs before session write)
        Session::save();

        // Store in cookie as fallback (persists across session issues, load balancers, HTTPS cookie problems)
        $cookie = Cookie::make('locale', $lang, 60 * 24 * 365); // 1 year

        return redirect()->back()->cookie($cookie);
    }
}