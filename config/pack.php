<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Pack configuration settings.
 *
 * @link  http://kowut.com/en/modules/pack#settings
 */
return array(
	/**
	 * Enable when in production, set it to false when developing your app.
	 * This flag decides whether to dump one optimized tag versus multiple
	 * raw tags in the target page (among other low level stuff.)
	 */
	'enabled' => Kohana::$environment <= Kohana::STAGING,
	
	/**
	 * The DocumentRoot of the website (assuming you're storing your
	 * assets there.)
	 */
	'root' => DOCROOT,

	/**
	 * Directories that will hold the concatenated and compressed files.
	 */
	'packages_dir' => array(
		'css' => 'assets/build/css/',
		'js' => 'assets/build/js/',
	),
	
	/**
	 * Define your CSS packages here.
	 */
	'css' => array(),
	
	/**
	 * Define your JS packages here.
	 */
	'js' => array(),
	
	/**
	 * Compression settings.
	 *
	 * @link  http://code.google.com/closure/compiler/docs/api-ref.html
	 * @link  http://www.julienlecomte.net/yuicompressor/README
	 */
	'compression' => array(
		'css' => array(
			'java' => 'java',
			'jar' => MODPATH.'pack/vendor/yahoo/build/yuicompressor-2.4.6.jar',
			'charset' => 'utf-8',
			'line_break' => NULL,
		),
		'js' => array(
			'java' => 'java',
			'jar' => MODPATH.'pack/vendor/google/compiler.jar',
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		),
	),
);