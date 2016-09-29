<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\Posts;

class cmsController extends Controller
{
    /**
     * Display a list based on the post type
     */
    public function index($post_type)
    {
    	$objects = Posts::where('post_type', $post_type)->select([
    		'id',
    		'post_title',
    		'post_name',
    		'status',
    		'post_type',
    	])->get();
    	return response()->json($objects);
    }

    /**
     * Delete a single post
     */
    public function delete($id)
    {
    	$post = Posts::where('id', $id);
    	$post->delete();
    	return response()->json('success');
    }

    /**
     * Display a single post
     */
    public function show($id)
    {
        $post = Posts::find($id);
    	$postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get();
        $postmeta = $postmeta->keyBy('meta_key');
        $postmeta = $postmeta->toArray();
        $post = $post->toArray();

        $collection = collect([
            'post' => $post,
            'postmeta' => $postmeta
        ]);

    	return response()->json($collection);
    }

    /**
     * The manager of the database communication for adding and manipulating posts
     */
    public function postManagement(Request $request, $post_type, $action)
    {
    	$this->validatePost($request, $action);

    	if($action == 'create'){
    		$post = new Posts;
    	} else if($action == 'edit') {
    		$post = Posts::find($request->get('_id'));
    	}

        // Saving the post data
    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	$post->post_type = $post_type;
        // Check if user is logged in
        if(Auth::check()){
            $post->post_author = Auth::user()->id;
        } else {
            $post->post_author = 0;
        }
        $post->template = $request->get('template');
    	$post->save();

        // Creating and cleaning up the request so we get all custom fields
        $postmeta = $request->all();
        $unsetValues = ['_token', '_posttype', '_id', 'post_title', 'post_name', 'post_content', 'template', 'status'];
        foreach($unsetValues as $value){
            unset($postmeta[$value]);
        }

        // Deleting all current postmeta rows
        $post->postmeta()->delete();

        // Saving the custom fields to the database as post meta
        foreach($postmeta as $key => $value){
            $object = [
                'meta_key' => $key,
                'meta_value' => $value,
            ];
            $post->postmeta()->create($object);
        }

    	return response()->json('success');
    }

    /**
     * Validating the creation and change of a post
     */
 	protected function validatePost($request, $action)
 	{
 		$validationRules = [
	        'post_title' => 'required',
            'status' => 'required',
	    ];

	    // Validate if we are creating a new post or that we are editting one
	    if($action == 'edit'){

            // Validating the postname of the given ID to make sure it can be
            // updated and it is not overriding a other duplicated postname.
	    	$post = Posts::where([
                ['id', '=', $request->get('_id')],
                ['post_type', '=', $request->get('_posttype')]
            ])->select(['post_name'])->first();

			if( $request->get('post_name') == $post->post_name ) {
		    	$validationRules['post_name'] = 'required';
		    } else {
		    	$validationRules['post_name'] = 'required|unique:cms_posts';
		    }

        // Creating a new post
	    } else {

            // Validate if the post_name is a duplicate in the current post_type
            $post = Posts::where([
                ['post_name', '=', $request->get('post_name')],
                ['post_type', '=', $request->get('_posttype')]
            ])->select(['post_name'])->first();
            if($post){
                $validationRules['post_name'] = 'required|unique:cms_posts';
            } else {
                $validationRules['post_name'] = 'required';
            }

	    }

 		return $this->validate($request, $validationRules);
    }

    /**
     * Function for sanitizing slugs
     */
    protected function sanitizeUrl($url)
    {
	    $url = $url;
	    $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url); // substitutes anything but letters, numbers and '_' with separator
	    $url = trim($url, "-");
	    $url = iconv("utf-8", "us-ascii//TRANSLIT", $url); // TRANSLIT does the whole job
	    $url = strtolower($url);
	    $url = preg_replace('~[^-a-z0-9_]+~', '', $url); // keep only letters, numbers, '_' and separator
	    return $url;
    }

    /**
     * Test route
     */
    public function test($post_type)
    {
        return view('niku-cms::post_type', compact('post_type'));
    }

    /**
     * Return the custom fields based on the config
     */
    public function receiveView(Request $request)
    {
        $postType = $request->get('_post_type');
        $id = $request->get('_id');

        $nikuConfig = config('niku-cms');

        // Validate if the post type exists
        if(empty($nikuConfig['post_types'][$postType])){
            return collect([
                'code' => 'doesnotexist',
                'status' => 'Post type does not exist'
            ]);
        }

        // Returning the view
        $view = $nikuConfig['post_types'][$postType]['view'];

        // Lets now fill the custom fields with data out of database
        $post = Posts::find($id);
        if(!empty($post)){
            $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get()->keyBy('meta_key')->toArray();
        }

        // Appending the key added in the config to the array
        // so we can use it very easliy in the component.
        foreach($view['templates'] as $key => $template){
            foreach($template['customFields'] as $ckey => $customField){
                $view['templates'][$key]['customFields'][$ckey]['id'] = $ckey;
                if(!empty($post)){
                    $view['templates'][$key]['customFields'][$ckey]['value'] = $postmeta[$ckey]['meta_value'];
                }
            }
        }

        return $view;
    }

}
