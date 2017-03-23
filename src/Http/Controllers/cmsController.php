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
    		$postTypeModel = new $nikuConfig['post_types'][$post_type];

    		// Lets validate if the request has got the correct authorizations set
    		if(!$this->authorizations($postTypeModel)){
    			return false;
    		}

    		return $postTypeModel;

    	} else {
    		return false;
    	}
    }

    protected function authorizations($postTypeModel)
    {
    	// If the user needs to be authenticated, we need to make
    	// sure we are not allowing the user to view the posts.
    	if(!$this->userMustBeLoggedIn($postTypeModel)){
    		return false;
    	}

    	// If users can only view their own posts, we need to make
    	// sure that the users are logged in before continueing.
    	if(!$this->userCanOnlySeeHisOwnPosts($postTypeModel)){
    		return false;
    	}

    	return true;
    }

    /**
     * The user must be logged in to view the post(s)
     */
    protected function userMustBeLoggedIn($postTypeModel)
    {
        if($postTypeModel->userMustBeLoggedIn){
            if(!Auth::check()){
        		return false;
        	} else {
        		return true;
        	}
        } else {
        	return true;
        }
    }

    /**
     * If the user can only see his own post(s)
     */
    protected function userCanOnlySeeHisOwnPosts($postTypeModel)
    {
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
        	if(!Auth::check()){
        		return false;
        	} else {
        		return true;
        	}
        } else {
        	return true;
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
