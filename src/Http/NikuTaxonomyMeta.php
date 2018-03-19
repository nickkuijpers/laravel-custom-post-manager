<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;
use Niku\Cms\Http\Controllers\cmsController;

class NikuTaxonomyMeta extends Model
{
    protected $table = 'cms_taxonomymeta';
    protected $fillable = ['meta_key', 'meta_value'];

    public function taxonomy()
    {
    	return $this->hasOne('Niku\Cms\Http\Taxonomymeta', 'id', 'taxonomy_id');
    }

}

