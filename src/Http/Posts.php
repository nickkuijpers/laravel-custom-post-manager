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
        return $this->hasMany('Niku\Cms\Http\Postmeta', 'post_id');
    }

}

