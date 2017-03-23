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
     * Determine if the user is authorized to make this request.
     */
    public function authorized()
    {
        return true;
    }

	/**
	 * Has Many connection to the post meta table
	 */
    public function postmeta()
    {
        return $this->hasMany('Niku\Cms\Http\NikuPostmeta', 'post_id', 'id');
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
}

