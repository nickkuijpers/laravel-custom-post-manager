<?php

namespace App\Cms\Mutators;

use Niku\Cms\Http\Controllers\MutatorController;

class PostNameMutator extends MutatorController
{
 	public function in($value, $post)
 	{
 		$value = 'test';

 		return $value;
 	}

 	public function out($customField, $post)
 	{
 		$customField['value'] = 'test';

 		return $customField;
 	}
}
