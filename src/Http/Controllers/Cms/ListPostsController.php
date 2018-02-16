<?php

namespace Niku\Cms\Http\Controllers\Cms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Niku\Cms\Http\Controllers\CmsController;

class ListPostsController extends CmsController
{
	public function init(Request $request, $postType)
    {
    	// Lets validate if the post type exists and if so, continue.
    	$postTypeModel = $this->getPostType($postType);
    	if(!$postTypeModel){
    		$errorMessages = 'You are not authorized to do this.';
    		return $this->abort($errorMessages);
    	}

    	// Check if the post type has a identifier
    	if(empty($postTypeModel->identifier)){
    		$errorMessages = 'The post type does not have a identifier.';
    		if(array_has($postTypeModel->errorMessages, 'post_type_identifier_does_not_exist')){
    			$errorMessages = $postTypeModel->errorMessages['post_type_identifier_does_not_exist'];
    		}
    		return $this->abort($errorMessages);
    	}

		$where = [];

        // If the user can only see his own posts
        if($postTypeModel->userCanOnlySeeHisOwnPosts){
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Lets check if we have configured a custom post type identifer
        if(!empty($postTypeModel->identifier)){
        	$postType = $postTypeModel->identifier;
        }
		
		$postTypeAliases = [];
		$postTypeIsArray = false;
		if(!empty($postTypeModel->postTypeAliases)){
			if(is_array($postTypeModel->postTypeAliases)){
				if(count($postTypeModel->postTypeAliases) > 0){
					$postTypeIsArray = true;
					$postTypeAliases = $postTypeModel->postTypeAliases;
					$postTypeAliases[] = $postType;
				}
			}
		}

        // Adding a custom query functionality so we can manipulate the find by the config
		if($postTypeModel->appendCustomWhereQueryToCmsPosts){
			foreach($postTypeModel->appendCustomWhereQueryToCmsPosts as $key => $value){
				$where[] = [$value[0], $value[1], $value[2]];
			}
		}

		$appendQuery = false;
		if(method_exists($postTypeModel, 'append_list_query')){
			$appendQuery = true;
		}
		
		// Query the database
		$posts = $postTypeModel::where($where)
			->when($appendQuery, function ($query) use ($postTypeModel, $request){
				return $postTypeModel->append_list_query($query, $postTypeModel, $request);
			})

			// When there are multiple post types
			->when($postTypeIsArray, function ($query) use ($postTypeAliases){
				return $query->whereIn('post_type', $postTypeAliases);
			}, function($query) use ($postType) {
				return $query->where('post_type', $postType);
			})
			
			->select([
				'id',
				'post_title',
				'post_name',
				'status',
				'post_type',
				'created_at',
				'updated_at',
			])
			->with('postmeta')
			->orderBy('id', 'desc')
			->get();
	
		if(method_exists($postTypeModel, 'on_list_check')){	
			$onCheck = $postTypeModel->on_list_check($postTypeModel, $posts, []);			
			if($onCheck['continue'] === false){
				$errorMessages = 'You are not authorized to do this.';
				if(array_key_exists('message', $onCheck)){
					$errorMessages = $onCheck['message'];
				}
				return $this->abort($errorMessages);
			}
		}

		// Lets fire events as registered in the post type
        $this->triggerEvent('on_browse_event', $postTypeModel, $posts, []);

		$config = $this->getConfig($postTypeModel);

		// Return the response
    	return response()->json([
			'config' => $config,
			'label' => $postTypeModel->label,
			'objects' => $posts,
		]);
	}
	
	public function getConfig($postTypeModel)
	{
		// Merge the configuration values
		$config = [];
		if($postTypeModel->config){
			$config = $postTypeModel->config;
		}

        $config = $config;

        // Adding public config
        if($postTypeModel->skipCreation){
			$config['skip_creation'] = $postTypeModel->skipCreation;
			if($postTypeModel->skipToRouteName){
				$config['skip_to_route_name'] = $postTypeModel->skipToRouteName;
			}
        } else {
			$config['skip_creation'] = false;
			$config['skip_to_route_name'] = '';
		}
		
		// Adding public config
        if($postTypeModel->disableEditOnlyCheck){
        	$config['disable_edit_only_check'] = $postTypeModel->disableEditOnlyCheck;
        } else {
        	$config['disable_edit_only_check'] = false;
		}
  
		if($postTypeModel->disableEdit){
        	$config['disable_edit'] = $postTypeModel->disableEdit;
        } else {
        	$config['disable_edit'] = false;
		}

		if($postTypeModel->disableDelete){
        	$config['disable_delete'] = $postTypeModel->disableDelete;
        } else {
        	$config['disable_delete'] = false;
		}

		if($postTypeModel->disableCreate){
        	$config['disable_create'] = $postTypeModel->disableCreate;
        } else {
        	$config['disable_create'] = false;
		}

		if($postTypeModel->getPostByPostName){
        	$config['get_post_by_postname'] = $postTypeModel->getPostByPostName;
        } else {
        	$config['get_post_by_postname'] = false;
		}

		return $config;
	}
}
