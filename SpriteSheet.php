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
$wgMessagesDirs['SpriteSheet']					= "{$extDir}/i18n";

$wgAutoloadClasses['SpriteSheetHooks']			= "{$extDir}/SpriteSheet.hooks.php";

$wgHooks['ImagePageShowTOC'][]					= 'SpriteSheetHooks::onImagePageShowTOC';
$wgHooks['ImageOpenShowImageInlineBefore'][]	= 'SpriteSheetHooks::onImageOpenShowImageInlineBefore';
$wgHooks['LoadExtensionSchemaUpdates'][]		= 'SpriteSheetHooks::onLoadExtensionSchemaUpdates';

$wgResourceModules['ext.spriteSheet'] = [
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'SpriteSheet',
	'styles'		=> ['css/spritesheet.css'],
	'scripts'		=> ['js/ocanvas-2.7.3.min.js', 'js/spritesheet.js'],
	'dependencies'	=> ['jquery']
];
