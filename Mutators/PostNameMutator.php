<?php

namespace App\Cms\PostTypes;

use Niku\Cms\Http\Controllers\MutatorController;

class PostNameMutator extends MutatorController
{
 	public function in($value, $collection)
 	{
 		$value['mutator_value'] = 'test';

 		return $value;
 	}

 	public function out($value, $collection)
 	{
 		$value = 'test';

 		return $value;
 	}
}
