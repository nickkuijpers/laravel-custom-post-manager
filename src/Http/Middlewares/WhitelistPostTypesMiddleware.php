<?php

namespace Niku\Cms\Http\Middlewares;

use Closure;

class WhitelistPostTypesMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $acceptedPostTypes)
	{
		$acceptedPostTypes = explode('|', $acceptedPostTypes);		

		// Lets verify that we are authorized to use this post type
        if(!in_array($request->route('post_type'), $acceptedPostTypes)){
            return response()->json([
                'error' => 'unsupported_post_type',
                'hint' => 'Verify if you are authorized to use this post type.',
                'message' => $request->route('post_type') . ' post type is not supported.'
            ], 400);
        }

        return $next($request);
	}
}
