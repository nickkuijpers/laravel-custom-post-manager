<?php
namespace Niku\Cms;

use App\Http\Controllers\Controller;

class Controller extends Controller
{

    public function index($post_type)
    {
        return view('niku-cms::post_type');
    }

}
