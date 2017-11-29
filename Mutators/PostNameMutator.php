<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\Controllers\MutatorController;

class PostNameMutator extends MutatorController
{
 	public function handle($value)
 	{
 		$value['mutator_value'] = 'test';

 		return $value;
 	}
}
