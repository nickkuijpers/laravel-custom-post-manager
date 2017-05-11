<?php
namespace Niku\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Niku\Cms\Http\NikuPostmeta;
use Niku\Cms\Http\NikuPosts;

class MediaController extends Controller
{
	/**
	 * Add attachment to the filesystem and add it to the database
	 */
	public function post(Request $request)
	{
		$this->validate($request, [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        ]);

		$fileName = basename($request->file->getClientOriginalName(), '.' . $request->file->getClientOriginalExtension());
		$fileExtension = $request->file->getClientOriginalExtension();
		$postName = $this->sanitizeUrl($fileName);
		$mime = $request->file->getMimeType();

		$postmeta = NikuPostmeta::where([
			['meta_key', '=', 'attachment_url'],
			['meta_value', 'LIKE', '/uploads/images/' . $fileName . '%']
		])->get();

		if($postmeta->count() != 0){
			$count = $postmeta->count() + 1;
			$fileCount = '-' . $count;
		} else {
			$fileCount = '';
		}

		$postName = $postName . $fileCount;
		$fileName = $fileName . $fileCount . '.' . $fileExtension;

		$post = new NikuPosts;
		$post->post_title = $fileName;
		$post->post_name = $postName;
		$post->post_type = 'attachment';
		$post->post_mime_type = $mime;
		$post->status = 'inherit';
		if(Auth::check()){
			$post->post_author = Auth::user()->id;
		} else {
			$post->post_author = '0';
		}
		$post->save();

		$postmeta = new NikuPostmeta;
		$postmeta->post_id = $post->id;
		$postmeta->meta_key = 'attachment_url';
		$postmeta->meta_value = '/uploads/images/' . $fileName;
		$postmeta->save();

        $request->file->move(public_path('uploads/images'), $fileName);

        $postObject = collect([
        	'status' => 0,
        	'postmeta' => [
        		0 => [
        			'meta_value' => $postmeta->meta_value,
        		]
        	],
        	'id' => $postmeta->post_id
        ]);

        return response()->json([
		    'object' => $postObject
		]);
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
}
