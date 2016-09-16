<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;

class Posts extends Model
{
    protected $table = 'cms_posts';    

    public function postmeta()
    {
        return $this->hasOne('Niku\Cms\Http\Postmeta', 'post_id');
    }

}

