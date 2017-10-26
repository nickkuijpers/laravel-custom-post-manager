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

		$message = $request->route('post_type') . ' post type is not supported. ';
		$authorized = false;		

		// Lets verify that we are authorized to use this post type
        if(in_array($request->route('post_type'), $acceptedPostTypes)){						
			$authorized = true;
			$message = '';

			// We need to validate access of the sub post type for the taxonomy API. Lets check if we are using this method
			if(array_key_exists('sub_post_type', $request->route()->parameters)){				

				// There is sub post type in the request, we need to authorize
				$authorized = false;
				$message .= $request->route('sub_post_type') . ' post type is not supported.';

				// Lets authorize the variable
				if(in_array($request->route('sub_post_type'), $acceptedPostTypes)){			
					$authorized = true;
				}
					
			// We can continue authorized because there is no sub post type in the request
			} else {
				$authorized = true;
			}
					
		}
		
		if(!$authorized){
			return response()->json([
				'error' => 'unsupported_post_type',
				'hint' => 'Verify if you are authorized to use this post type.',
				'message' => $message,
			], 400);
		}

        return $next($request);
	}
}
