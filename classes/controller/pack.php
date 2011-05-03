<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Pack extends Controller {
	
	public function action_index()
	{
		// Load the configuration settings
		$config = Pack::$config = Kohana::config('pack');
		
		if ( ! $config['enabled'])
		{
			// Generate the packages
			Pack::package();
		}
		else
		{
			throw new HTTP_Exception_404;
		}
	}
	
} // Controller_Pack