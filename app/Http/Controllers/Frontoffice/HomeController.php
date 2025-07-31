<?php

namespace App\Http\Controllers\Frontoffice;



use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Contact;


class HomeController extends Controller
{
    public function index()
    {

        if (!app()->getLocale()) {
            app()->setLocale('pt');
        }

        $brands = Brand::All();
        $banners = Banner::All();
        $categories = Category::all();
        $contacts = Contact::all();
        return view('frontoffice.home.index', compact('brands', 'banners', 'categories', 'contacts'));
    }
}
