<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $table = 'cms_posts';

    protected $attributes = array(
	  'template' => 'default'
	);

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

}

