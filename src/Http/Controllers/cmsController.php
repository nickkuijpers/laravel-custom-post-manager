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

    public function delete($id)
    {
    	$post = Posts::where('id', $id);
    	$post->delete();
    	return response()->json('success');
    }

    public function show($id)
    {
    	$objects = Posts::where('id', $id)->first();
    	return response()->json($objects);        
    }

    public function postManagement(Request $request, $post_type, $action)
    {    	    	
    	$this->validatePost($request, $action);
    	
    	if($action == 'create'){
    		$post = new Posts;    	
    	} else if($action == 'edit') {
    		$post = Posts::find($request->get('_id'));    		
    	}    	

    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	$post->post_type = $post_type;
    	$post->post_author = Auth::user()->id;
    	$post->save();

    	return response()->json('success');        	
    }

 	protected function validatePost($request, $action)
 	{
 		$validationRules = [
	        'post_title' => 'required',
            'post_content' => 'required',            
            'status' => 'required',
	    ];

	    // Validate if new post or if its the current one	    
	    if($action == 'edit'){

	    	// Get the post by ID
	    	$post = Posts::where('id', $request->get('_id') )->select(['post_name'])->first();	    	

			if( $request->get('post_name') == $post->post_name ) {
		    	$validationRules['post_name'] = 'required';		    	
		    } else {
		    	$validationRules['post_name'] = 'required|unique:cms_posts';
		    }

	    } else {
	    	$validationRules['post_name'] = 'required|unique:cms_posts';
	    }

 		return $this->validate($request, $validationRules);
    }    

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

}
