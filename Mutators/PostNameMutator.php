<?php

namespace App\Cms\Mutators;

use Niku\Cms\Http\Controllers\MutatorController;

class PostNameMutator extends MutatorController
{
 	public function in($value, $collection)
 	{
 		$value = 'test';

 		return $value;
 	}

 	public function out($value, $collection)
 	{
 		$value = 'test';

 		return $value;
 	}
}
