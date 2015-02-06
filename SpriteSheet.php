<?php
/**
 * SpriteSheet
 * SpriteSheet Mediawiki Settings
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

/******************************************/
/* Credits								  */
/******************************************/
$credits = [
	'path'				=> __FILE__,
	'name'				=> 'SpriteSheet',
	'author'			=> ['Curse Inc', 'Wiki Platform Team'],
	'descriptionmsg'	=> 'spritesheet_description',
	'version'			=> '1.0'
];
$wgExtensionCredits['other'][] = $credits;


/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;

$wgExtensionMessagesFiles['SpriteSheet']		= "{$extDir}/SpriteSheet.i18n.php";
$wgExtensionMessagesFiles['SpriteSheetMagic']	= "{$extDir}/SpriteSheet.i18n.magic.php";
$wgMessagesDirs['SpriteSheet']					= "{$extDir}/i18n";

$wgAutoloadClasses['SpriteSheetHooks']			= "{$extDir}/SpriteSheet.hooks.php";
$wgAutoloadClasses['SpriteSheet']				= "{$extDir}/classes/SpriteSheet.php";
$wgAutoloadClasses['SpriteSheetAPI']			= "{$extDir}/SpriteSheet.api.php";

$wgHooks['ParserFirstCallInit'][]				= 'SpriteSheetHooks::onParserFirstCallInit';
$wgHooks['ImagePageShowTOC'][]					= 'SpriteSheetHooks::onImagePageShowTOC';
$wgHooks['ImageOpenShowImageInlineBefore'][]	= 'SpriteSheetHooks::onImageOpenShowImageInlineBefore';
$wgHooks['PageRenderingHash'][]					= 'SpriteSheetHooks::onPageRenderingHash';
$wgHooks['LoadExtensionSchemaUpdates'][]		= 'SpriteSheetHooks::onLoadExtensionSchemaUpdates';

$wgAPIModules['spritesheet']					= 'SpriteSheetAPI';

$wgResourceModules['ext.spriteSheet'] = [
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'SpriteSheet',
	'styles'		=> ['css/spritesheet.css'],
	'scripts'		=> [
		'js/ocanvas-2.7.3.min.js',
		'js/spritesheet.js'
	],
	'dependencies'	=> [
		'jquery'
	],
	'messages'		=> [
		'save_named_sprite',
		'save_named_slice',
		'please_enter_sprite_name',
		'please_select_sprite'
	],
	'position'		=> 'top'
];
