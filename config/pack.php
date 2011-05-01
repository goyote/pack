<?php defined('SYSPATH') or die('No direct script access.');

return array(

	'enabled' => Kohana::$environment <= Kohana::STAGING,

	'root' => DOCROOT,

	'build_dir' => array(
		'css' => 'assets/build/css/',
		'js' => 'assets/build/js/',
	),

	'temp_dir' => 'assets/temp/',

	'css' => array(),

	'js' => array(),

	'compression' => array(
		'css' => array(
			'java' => 'java',
			'jar' => MODPATH.'pack/vendor/yuicompressor/build/yuicompressor-2.4.6.jar',
			'charset' => 'utf-8',
			'line_break' => NULL,
		),
		'js' => array(
			'java' => 'java',
			'jar' => MODPATH.'pack/vendor/google/compiler-20110405.jar',
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		),
	),
);