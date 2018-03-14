<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Niku\Cms\Http\NikuTaxonomies;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class TaxonomyController extends CmsController
{
    public function init(Request $request, $postType, $id)
    {
        $postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
        }

        // Validate the required fields
        $this->validate($request, [
			'action' => 'required',
            'id' => 'required',
            'taxonomy_post_id' => 'required',
        ]);

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
		}

		// Disable editting of form
		if($postTypeModel->disableEdit){
        	$errorMessages = 'The post type does not support editting.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_support_edit')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_support_edit'];
    		}
    		return $this->abort($errorMessages);
		}

		switch($request->action){

			// Creating a taxonomy
			case 'attach':

				$taxonomyInstance = new NikuTaxonomies;
				$taxonomyInstance->post_id = $request->post_id;
				$taxonomyInstance->taxonomy_post_id = $request->taxonomy_post_id;
				$taxonomyInstance->taxonomy = $postType;

				if($request->has('custom')){
					$taxonomyInstance->custom = $request->custom;
				}

				if($request->has('menu_order')){
					$taxonomyInstance->menu_order = $request->menu_order;
				}

				$taxonomyInstance->save();

			break;

			// Deleting the taxonomy
			case 'detach':

				$taxonomyInstance = NikuTaxonomies::where([
					['id', '=', $request->id],
					['taxonomy_post_id', '=', $request->taxonomy_post_id],
					['post_id', '=', $request->post_id],
				])->first();

				if($taxonomyInstance){
					$taxonomyInstance->delete();
				}

			break;

			// Editting the taxonomy
			case 'edit':

				$taxonomyInstance = NikuTaxonomies::where([
					['id', '=', $request->id],
					['taxonomy_post_id', '=', $request->taxonomy_post_id],
					['post_id', '=', $request->post_id],
				])->first();

				if(!$taxonomyInstance){
					$errorMessages = 'Taxonomy does not exist.';
					if(array_has($postTypeModel->errorMessages, 'taxonomy_does_not_exist')){
						$errorMessages = $postTypeModel->errorMessages['taxonomy_does_not_exist'];
					}
					return $this->abort($errorMessages);
				}

				if($request->has('custom')){
					$taxonomyInstance->custom = $request->custom;
				}

				if($request->has('menu_order')){
					$taxonomyInstance->menu_order = $request->menu_order;
				}

				if($request->has('taxonomymeta')){
					$toSave = [];
					foreach($request->taxonomymeta as $key => $value){
						$toSave[$key] = $value;
					}

					$taxonomyInstance->saveMetas($toSave);
				}

				$taxonomyInstance->save();

			break;
			case 'sync':

			break;
		}

        $successMessage = 'Taxonomy succesfully updated.';
		if(array_has($postTypeModel->successMessage, 'taxonomy_updated')){
			$successMessage = $postTypeModel->successMessage['taxonomy_updated'];
		}

        // Lets return the response
    	return response()->json([
    		'code' => 'success',
    		'message' => $successMessage,
    		'action' => 'taxonomy_edit',
    	], 200);
    }

}
