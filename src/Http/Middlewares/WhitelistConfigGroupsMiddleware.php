<?php

namespace Niku\Cms\Http\Middlewares;

use Closure;

class WhitelistConfigGroupsMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $acceptedGroups)
	{
		$acceptedGroups = explode('|', $acceptedGroups);		
        
		// Lets verify that we are authorized to use this post type
        if(!in_array($request->route('group'), $acceptedGroups)){
            return response()->json([
                'error' => 'unsupported_config_group',
                'hint' => 'Verify if you are authorized to use this config group.',
                'message' => $request->route('post_type') . ' config group is not supported.'
            ], 400);
        }

        return $next($request);
	}
}
