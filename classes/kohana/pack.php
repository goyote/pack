<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Asset packaging & management for the Kohana framework.
 *
 * @package    Pack
 * @category   Base
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @see        http://kowut.com/en/modules/pack
 * @license    MIT
 */
class Kohana_Pack {

	/**
	 * @var  array  configuration settings
	 */
	protected static $config;

	/**
	 * @var  array  method mappings
	 */
	protected static $methods = array(
		'css' => 'style',
		'js' => 'script',
	);

	/**
	 * @var  array  cached timestamps
	 */
	protected static $timestamps;

	/**
	 * @var  array  cached files
	 */
	protected static $files;

	/**
	 * Renders the <link> elements.
	 *
	 *     echo Pack::css('package1');
	 *
	 * @uses    Pack::render
	 * @param   string|array
	 * @return  string
	 */
	public static function css($packages)
	{
		return Pack::render(__FUNCTION__, (array) $packages);
	}

	/**
	 * Renders the <script> elements.
	 *
	 *     echo Pack::js('package1');
	 *
	 * @uses    Pack::render
	 * @param   string|array
	 * @return  string
	 */
	public static function js($packages)
	{
		return Pack::render(__FUNCTION__, (array) $packages);
	}

	/**
	 * Renders the asset paths found in the package into HTML elements.
	 *
	 * @uses    Pack::timestamp
	 * @uses    Arr::path
	 * @param   string
	 * @param   array
	 * @return  string
	 */
	protected static function render($language, $packages)
	{
		if (Pack::$config === NULL)
		{
			// Load the configuration settings once
			Pack::$config = Kohana::config('pack');
		}

		$html = '';
		foreach ($packages as $package)
		{
			$assets = Pack::$config['enabled']
				? array(Pack::$config['packages_dir'][$language].$package.'.'.$language)
				: Arr::path(Pack::$config[$language], $package);

			foreach ($assets as $asset)
			{
				$html .= call_user_func(
					array('HTML', Pack::$methods[$language]),
					$asset.'?'.Pack::timestamp(Pack::$config['root'].$asset)
				)."\n";
			}
		}
		
		return $html;
	}
	
	/**
	 * Gets the file modification time.
	 *
	 * @param   string
	 * @return  string
	 */
	protected static function timestamp($asset)
	{
		if ( ! isset(Pack::$timestamps[$asset]))
		{
			// Cache the timestamp
			Pack::$timestamps[$asset] = filemtime($asset);
		}

		return Pack::$timestamps[$asset];
	}

	/**
	 * Builds the packages that are missing or outdated.
	 *
	 * @uses    Pack::package_outdated
	 * @uses    Pack::build_package
	 * @return  bool
	 */
	public static function package()
	{
		// Load the configuration settings once
		$config = Pack::$config = Kohana::config('pack');

		if ($config['enabled'])
		{
			// Disable packaging in production
			return FALSE;
		}

		foreach(array('css', 'js') as $language)
		{
			$packages = $config[$language];

			foreach ($packages as $package => $assets)
			{
				$file = $config['root'].$config['packages_dir'][$language].$package.'.'.$language;

				if ( ! is_file($file) OR Pack::package_outdated($assets, $file))
				{
					// Save developer time by building only when necessary
					Pack::build_package($language, $assets, $file);
				}
			}
		}

		// Packaging completed successfully
		return TRUE;
	}

	/**
	 * Determines if a package or asset is outdated.
	 *
	 * @uses    Pack::find_file
	 * @uses    Pack::timestamp
	 * @param   array
	 * @param   string	
	 * @return  bool
	 */
	protected static function package_outdated($assets, $build_file)
	{
		$outdated = FALSE;
		$latest = 0;

		foreach ($assets as $asset)
		{
			$file = Pack::find_file($asset);

			if (($timestamp = Pack::timestamp($file)) > $latest)
			{
				// We're looking for the newest asset
				$latest = $timestamp;
			}
		}

		if ($latest > Pack::timestamp($build_file))
		{
			// At least one asset is newer, need to rebuild
			$outdated = TRUE;
		}
		
		return $outdated;
	}

	/**
	 * Builds a package from a set fo files.
	 * 
	 * @uses    Pack::find_file
	 * @uses    Pack::create_file
	 * @uses    Pack::compress_css
	 * @uses    Pack::compress_js
	 * @param   string
	 * @param   array
	 * @param   string
	 */
	protected static function build_package($language, $assets, $build_file)
	{
		$data = '';
		foreach ($assets as $asset)
		{
			$file = Pack::find_file($asset);

			// Concatenated code === less HTTP requests
			$data .= file_get_contents($file);
		}

		if ( ! is_file($build_file))
		{
			// Dev forgot to create build file, no worries :)
			Pack::create_file($build_file);
		}

		file_put_contents($build_file, $data, LOCK_EX);

		$method = 'compress_'.$language;
		Pack::$method($build_file);
	}

	/**
	 * Looks for a file in the RCFS (Root + Cascading Filesystem.)
	 *
	 * @uses    Kohana::find_file
	 * @throws  Kohana_Exception
	 * @param   string
	 * @return  string
	 */
	protected static function find_file($asset)
	{
		if ( ! isset(Pack::$files[$asset]))
		{
			// Look for the file in the "root" directory
			$file = Pack::$config['root'].$asset;

			if ( ! is_file($file))
			{
				$info = pathinfo($asset);

				// Look for the file in the CFS
				if (($file = Kohana::find_file($info['dirname'], $info['filename'], $info['extension'])) === FALSE)
				{
					throw new Kohana_Exception('Can\'t find :asset in the RCFS.',
						array(':asset' => $asset));
				}
			}

			Pack::$files[$asset] = $file;
		}

		return Pack::$files[$asset];
	}

	/**
	 * Creates the full path to a file.
	 *
	 * @param  string
	 */
	protected static function create_file($file)
	{
		$info = pathinfo($file);

		if ( ! is_dir($info['dirname']))
		{
			// Create the full directory structure
			mkdir($info['dirname'], 0777, TRUE);

			// chmod to solve potential umask issues
			chmod($info['dirname'], 0777);
		}

		// Create a blank file
		touch($file);
	}

	/**
	 * Compresses the CSS file path with the YUI compressor.
	 *
	 * @param  string
	 */
	protected static function compress_css($file)
	{
		$exec = escapeshellarg(Pack::$config['compression']['css']['java']);

		// Specify the path to the jar file
		$exec .= sprintf(' -jar %s ', escapeshellarg(Pack::$config['compression']['css']['jar']));

		// Hard code CSS compression
		$exec .= ' --type css ';

		// utf-8 is good, otherwise override in the config
		$exec .= sprintf(' --charset %s ', escapeshellarg(Pack::$config['compression']['css']['charset']));

		if ( ! empty(Pack::$config['compression']['css']['line_break']))
		{
			// Generate line-breaks for long files
			$exec .= sprintf(' --line-break %s ', escapeshellarg(Pack::$config['compression']['css']['line_break']));
		}

		// The input and output file are the same
		$exec .= sprintf(' -o %s ', escapeshellarg($file));
		$exec .= sprintf(' %s ', escapeshellarg($file));

		// Compress the asset file
		exec($exec);
	}

	/**
	 * Compresses the JS file path with the Google Closure Compiler.
	 *
	 * @param   string
	 */
	protected static function compress_js($file)
	{
		$exec = escapeshellarg(Pack::$config['compression']['js']['java']);

		// Specify the path to the jar file
		$exec .= sprintf(' -jar %s ', escapeshellarg(Pack::$config['compression']['js']['jar']));

		// Specify the input file
		$exec .= sprintf(' --js %s ', escapeshellarg($file));

		// Compress the asset file
		$code = shell_exec($exec);

		// Save the file to hard disk
		file_put_contents($file, $code, LOCK_EX);
	}

} // Kohana_Pack
