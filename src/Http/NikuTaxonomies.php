<?php

namespace Niku\Cms\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\cmsController;

class NikuTaxonomies extends Model
{
    protected $table = 'cms_taxonomy';

    /**
	 * Has Many connection to the post meta table
	 */
    public function taxonomymeta()
    {
        return $this->hasMany('Niku\Cms\Http\NikuTaxonomyMeta', 'taxonomy_id', 'id');
    }

    /**
     * Retrieve the meta value of a certain key
     */
    public function getMeta($key)
    {
    	$taxonomymeta = $this->taxonomymeta;
    	$taxonomymeta = $taxonomymeta->keyBy('meta_key');
		if(array_has($taxonomymeta, $key . '.meta_value')){
			$returnValue = $taxonomymeta[$key]['meta_value'];
			return $returnValue;
		}
    }

    public function saveMetas($metas)
    {
        foreach($metas as $key => $value){

            if(is_array($value)){
            	$value = json_encode($value);
            }

            // Saving it to the database based on key value array
            $object = [
                'meta_key' => $key,
                'meta_value' => $value,
            ];

            // Update or create the meta key of the post
            $this->taxonomymeta()->updateOrCreate([
                'meta_key' => $key
            ], $object);
        }
    }
}
