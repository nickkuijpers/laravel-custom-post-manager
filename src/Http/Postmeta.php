<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;

class Postmeta extends Model
{
    protected $table = 'cms_postmeta';

    public function post()
    {
    	return $this->hasOne('Niku\Cms\Http\Posts', 'post_id');
    }

}

