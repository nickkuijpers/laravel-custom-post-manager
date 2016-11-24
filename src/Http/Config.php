<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'cms_config';
    protected $fillable = ['option_name', 'option_value', 'group'];
    protected $hidden = ['created_at', 'updated_at'];
}

