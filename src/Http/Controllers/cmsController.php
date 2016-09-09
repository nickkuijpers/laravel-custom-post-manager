<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;

class cmsController extends Controller
{
    public function index($post_type)
    {
        return view('niku-cms::post_type');
    }

}
