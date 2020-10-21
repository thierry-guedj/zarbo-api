<?php
namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;

class LocalizationController extends Controller {
    public function index($locale){
        App::setlocale($locale);
        session()->put('locale', $locale);
        return redirect()->back();
    }

    public function getLang() {
        return \App::getLocale();
    }

    public function setLang($lang){
        \Session::put('lang', $lang);
        \Log::error($lang);
        return redirect()->back();
    }
}