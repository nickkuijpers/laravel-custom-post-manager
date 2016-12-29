<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\Posts;

class cmsController extends Controller
{
    /**
     * Display a list based on the post type
     */
    public function index($post_type, $orderName = 'id', $orderType = 'desc', $take = 9999, $skip = '-1')
    {
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized or post type is not registered.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($post_type)) {
            return $this->abort('User email is not whitelisted.');
        }

        // Check if the post type is whitelisted
        if( !array_key_exists($post_type, config('niku-cms.post_types') ) ) {
        	return $this->abort('Post type ' . $post_type . ' is not whitelisted.');
        }

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }


        // Where sql to get all posts by post_Type
        $where[] = ['post_type', '=', $post_type];


		// Returning the view data like the page label
		$objects['label'] = config("niku-cms.post_types.{$post_type}.view.label");

		// Type of sort
		if($orderType == 'desc'){
			$orderValue = 'desc';
		} elseif ($orderType == 'asc'){
			$orderValue = 'asc';
		}

		if($take == '-1'){
			$take = 9999999999;
		}

		if($skip == '-1'){
			$skip = 0;
		}

		$posts = Posts::where($where)->select([
			'id',
    		'post_title',
    		'post_name',
    		'status',
    		'post_type',
		])
		->orderBy($orderName, $orderValue)
		->skip($skip)
		->take($take)
		->with('postmeta')
		->get();

		// Returning the objects
    	$objects['objects'] = $posts;

    	return response()->json($objects);
    }

    /**
     * Delete a single post
     */
    public function delete($post_type, $id)
    {
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($post_type)) {
            return $this->abort('User email is not whitelisted.');
        }

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }

    	// Where sql to get all posts by post_Type
    	$where[] = ['id', '=', $id];

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }

    	$post = Posts::where($where);
    	$post->delete();

    	return response()->json('success');
    }

    /**
     * Display a single post
     */
    public function show($post_type, $id)
    {
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($post_type)) {
            return $this->abort('User email is not whitelisted.');
        }

        // If the user can only see his own posts
        if($this->userCanOnlySeeHisOwnPosts($post_type)) {
            $where[] = ['post_author', '=', Auth::user()->id];
        }

        // Where sql to get all posts by post_Type
        $where[] = ['id', '=', $id];

        $post = Posts::where($where)->first();
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
        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($post_type)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($post_type)) {
            return $this->abort('User email is not whitelisted.');
        }

        $validationRules = [
            'post_title' => 'required',
            'status' => 'required',
            'post_name' => 'required',
        ];

        // Creating and cleaning up the request so we get all custom fields
        $postmeta = $request->all();
        $unsetValues = ['_token', '_posttype', '_id', 'post_title', 'post_name', 'post_content', 'template', 'status'];
        foreach($unsetValues as $value){
            unset($postmeta[$value]);
        }

        foreach ($postmeta as $key => $value) {
            $rule = config("niku-cms.post_types.{$post_type}.view.templates.{$request->template}.customFields.{$key}.validation");

            if (! empty($rule)) {
                $validationRules[$key] = $rule;
            }
        }

    	// Validate the post
    	$this->validatePost($request, $action, $validationRules);

    	if($action == 'create'){
    		$post = new Posts;
    	} else if($action == 'edit') {

            // If the user can only see his own posts
            if($this->userCanOnlySeeHisOwnPosts($post_type)) {
                $where[] = ['post_author', '=', Auth::user()->id];
            }

    		$where[] = ['id', '=', $request->get('_id')];

    		$post = Posts::where($where)->first();

    	}

        // Saving the post data
    	$post->post_title = $request->get('post_title');
    	$post->post_name = $this->sanitizeUrl($request->get('post_name'));
    	$post->post_content = $request->get('post_content');
    	$post->status = $request->get('status');
    	$post->post_type = $post_type;

        // Check if user is logged in to set the author id
        if(Auth::check()){
            $post->post_author = Auth::user()->id;
        } else {
            $post->post_author = 0;
        }

        $post->template = $request->get('template');
    	$post->save();

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
    protected function validatePost($request, $action, $validationRules)
    {
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

        // Validate if the user is logged in
        if(! $this->userIsLoggedIn($postType)){
            return $this->abort('User not authorized.');
        }

        // User email validation
        if ($this->userHasWhitelistedEmail($postType)) {
            return $this->abort('User email is not whitelisted.');
        }

        $nikuConfig = config("niku-cms.post_types.{$postType}");
        // Validate if the post type exists
        if(empty($nikuConfig)){
            return collect([
                'code' => 'doesnotexist',
                'status' => 'Post type does not exist'
            ]);
        }

        // Returning the view
        $view = $nikuConfig['view'];

        // Lets now fill the custom fields with data out of database
        $post = Posts::find($id);
        if(!empty($post)){
            $postmeta = $post->postmeta()->select(['meta_key', 'meta_value'])->get()->keyBy('meta_key')->toArray();
        }

        // Appending the key added in the config to the array
        // so we can use it very easliy in the component.
        foreach($view['templates'] as $key => $template){
        	if(!empty($template['customFields'])){
	            foreach($template['customFields'] as $ckey => $customField){
	                $view['templates'][$key]['customFields'][$ckey]['id'] = $ckey;
	                if(!empty($post)){
	                	if(!empty($postmeta[$ckey])){
	                		$view['templates'][$key]['customFields'][$ckey]['value'] = $postmeta[$ckey]['meta_value'];
	                	}
	                }
	            }
	        }
        }

        return $view;
    }

    public function userIsLoggedIn($post_type)
    {
        if(config("niku-cms.post_types.{$post_type}.authorization.userMustBeLoggedIn")){
            return Auth::check();
        } else {
            return true;
        }
    }

    protected function abort($message = 'Not authorized.')
    {
        return response()->json([
            'code' => 'error',
            'status' => $message,
        ]);
    }

    protected function userHasWhitelistedEmail($post_type)
    {
        $emailAddresses = config("niku-cms.post_types.{$post_type}.authorization.allowedUserEmailAddresses");
        return (!empty($emailAddresses) && !in_array( Auth::user()->email, $emailAddresses));
    }

    protected function userCanOnlySeeHisOwnPosts($post_type)
    {
        return config("niku-cms.post_types.{$post_type}.authorization.userCanOnlySeeHisOwnPosts") == 1;
    }
}
