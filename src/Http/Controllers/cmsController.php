<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\NikuPosts;

class CmsController extends Controller
{
	/**
	 * Validating all functions
	 */
    protected function validateRules()
    {

    }

    /**
     * Function for sanitizing slugs
     */
    protected function sanitizeUrl($url)
    {
	    $url = $url;
	    $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url); // substitutes anything but letters, numbers and '_' with separator
	    $url = trim($url, "-");
	    $url = iconv("utf-8", "us-ascii//TRANSLIT", $url); // TRANSLIT does the whole job
	    $url = strtolower($url);
	    $url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
	    return $url;
    }

    /**
     * Validating if the post type exists and returning the model.
     */
    protected function getPostType($post_type)
    {
    	// Receive the config variable where we have whitelisted all models
    	$nikuConfig = config('niku-cms');

    	// Validating if the model exists in the array
    	if(array_key_exists($post_type, $nikuConfig['post_types'])){

    		// Setting the model class
    		$postType = new $nikuConfig['post_types'][$post_type];

    		return $postType;

    	} else {

    		return false;
    	}
    }

    /**
     * Abort the request
     */
    public function abort($message = 'Not authorized.')
    {
        return response()->json([
            'code' => 'error',
            'status' => $message,
        ], 422);
    }

}
