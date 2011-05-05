<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pack controller in charge of build operations.
 *
 * @package    Pack
 * @category   Controller
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @see        http://kowut.com/en/modules/pack
 * @license    MIT
 */
class Controller_Pack extends Controller {

	/**
	 * Builds the packages only if needed, throws a 404 in production.
	 *
	 * @throws  HTTP_Exception_404
	 * @return  void
	 */
	public function action_index()
	{
		// Generate the packages
		if ( ! Pack::package())
		{
			// Packaging is disabled in production
			throw new HTTP_Exception_404;
		}
	}
	
} // Controller_Pack