<?php
/**
 * @ingroup Extensions
 * @{
 * MediaWiki extension providing an API for the ShortUrl extension
 *
 * @file
 * @{
 * @copyright © 2014 Daniel Norton d/b/a WeirdoSoft - www.weirdosoft.com
 *
 * @section License
 *    - <b>CC BY-SA 3.0</b> -
 *   This work is licensed under the Creative Commons
 *   Attribution-ShareAlike 3.0 Unported License. To view a copy of
 *   this license, visit http://creativecommons.org/licenses/by-sa/3.0/
 *   or send a letter to Creative Commons, 444 Castro Street, Suite 900,
 *   Mountain View, California, 94041, USA.
 * @}
 *
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This file is an extension to MediaWiki software and is not designed for standalone use.\n" ;
	die( 1 ) ;
}

if ( defined( 'MW_EXT_SHORTURLAPI_NAME' ) ) {
	echo "Extension module already loaded: " . MW_EXT_SHORTURLAPI_NAME . "\n" ;
	die ( 1 ) ;
}

define( 'MW_EXT_SHORTURLAPI_NAME',            'ShortUrlApi' ) ;
define( 'MW_EXT_SHORTURLAPI_VERSION',         '1.0.0' ) ;
define( 'MW_EXT_SHORTURLAPI_AUTHOR',          'Daniel Norton' ) ;

define( 'MW_EXT_SHORTURLAPI_PARAM_NAME',      'shorturl' ) ;
define( 'MW_EXT_SHORTURLAPI_API_MID',         'su' ) ;

define( 'MW_EXT_SHORTURLAPI_API_CLASS',       'ApiShortUrl' ) ;
define( 'MW_EXT_SHORTURLAPI_API_QUERY_CLASS', 'ApiQueryShortUrl' ) ;

global $wgAPIModules, $wgAPIPropModules, $wgAutoloadClasses, $wgExtensionCredits ;

$wgExtensionCredits['api'][] = array(
	'path' => __DIR__ . '/' . MW_EXT_SHORTURLAPI_NAME,
	'name'         => MW_EXT_SHORTURLAPI_NAME,
	'description'  => 'Provide information about MediaWiki ShortUrl objects.',
	'version'      => MW_EXT_SHORTURLAPI_VERSION,
	'author'       => MW_EXT_SHORTURLAPI_AUTHOR,
	'license-name' => '[https://creativecommons.org/licenses/by-sa/3.0/ CC BY-SA 3.0]',
	'url'          => 'http://www.wikimedia.org/wiki/API:ShortUrl',
) ;

// API declarations

// action=query&prop=shorturl
$wgAPIPropModules[MW_EXT_SHORTURLAPI_PARAM_NAME] = MW_EXT_SHORTURLAPI_API_QUERY_CLASS ;
$wgAutoloadClasses[MW_EXT_SHORTURLAPI_API_QUERY_CLASS] =
	 __DIR__ . '/' . MW_EXT_SHORTURLAPI_API_QUERY_CLASS . '.php' ;

// action=shorturl
$wgAPIModules[MW_EXT_SHORTURLAPI_PARAM_NAME] = MW_EXT_SHORTURLAPI_API_CLASS ;
$wgAutoloadClasses[MW_EXT_SHORTURLAPI_API_CLASS] =
	 __DIR__ . '/' . MW_EXT_SHORTURLAPI_API_CLASS . '.php' ;

/** @}*/
