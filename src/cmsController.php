<?php
namespace Niku\Cms;

use App\Http\Controllers\Controller;
use Carbon\Carbon;

class cmsController extends Controller
{

    public function index($timezone)
    {
        echo Carbon::now($timezone)->toDateTimeString();
    }

}
