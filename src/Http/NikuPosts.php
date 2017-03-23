<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NikuPosts extends Model
{
    protected $table = 'cms_posts';

    protected $attributes = array(
	  'template' => 'default'
	);

    /**
	 * Validating all registered validations and validate them by the
	 * given post type. We will respond a 422 code if it falis.
	 */
    public function authorizationCheck()
	{
		// Validate if the user is logged in
        // if(!$this->userMustBeLoggedIn()){
        //     return $this->abort('User not authorized.');
        // }

        // User email validation
        if ($this->userHasWhitelistedEmail([
        	'info@niku-solutions.nl'
        ])) {
            return $this->abort('User email is not whitelisted.');
        }

		return true;
	}

    protected function userCanOnlySeeHisOwnPosts($post_type)
    {
        return config("niku-cms.post_types.{$post_type}.authorization.userCanOnlySeeHisOwnPosts") == 1;
    }

    public function postmeta()
    {
        return $this->hasMany('Niku\Cms\Http\Postmeta', 'post_id', 'id');
    }

    /**
     * Retrieve the meta value of a certain key
     */
    public function getMeta($key)
    {
    	$postmeta = $this->postmeta;
    	$postmeta = $postmeta->keyBy('meta_key');
    	$returnValue = $postmeta[$key]['meta_value'];
    	return $returnValue;
    }

    /**
     * Abort the request
     */
    public function abort($message = 'Not authorized.')
    {
        return (object) [
            'code' => 422,
            'message' => $message,
        ];
    }
}

