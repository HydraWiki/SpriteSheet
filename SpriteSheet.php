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
define('SPRITESHEET_VERSION', '1.1.1');
$credits = [
	'path'				=> __FILE__,
	'name'				=> 'SpriteSheet',
	'author'			=> ['Curse Inc. Wiki Platform Team', 'Alexia E. Smith'],
	'descriptionmsg'	=> 'spritesheet_description',
	'url'				=> 'https://github.com/CurseStaff/SpriteSheet',
	'version'			=> SPRITESHEET_VERSION
];
$wgExtensionCredits['other'][] = $credits;


/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;

$wgAvailableRights[] = 'edit_sprites';
$wgAvailableRights[] = 'spritesheet_rollback';
$wgGroupPermissions['autoconfirmed']['edit_sprites'] = true;
$wgGroupPermissions['sysop']['spritesheet_rollback'] = true;

$wgExtensionMessagesFiles['SpriteSheet']		= "{$extDir}/SpriteSheet.i18n.php";
$wgExtensionMessagesFiles['SpriteSheetMagic']	= "{$extDir}/SpriteSheet.i18n.magic.php";
$wgMessagesDirs['SpriteSheet']					= "{$extDir}/i18n";

$wgAutoloadClasses['SpriteSheetHooks']			= "{$extDir}/SpriteSheet.hooks.php";
$wgAutoloadClasses['SpriteSheet']				= "{$extDir}/classes/SpriteSheet.php";
$wgAutoloadClasses['SpriteSheetRemote']			= "{$extDir}/classes/SpriteSheet.php";
$wgAutoloadClasses['SpriteName']				= "{$extDir}/classes/SpriteName.php";
$wgAutoloadClasses['SpriteSheetLogFormatter']	= "{$extDir}/classes/SpriteSheetLogFormatter.php";
$wgAutoloadClasses['SpriteNameLogFormatter']	= "{$extDir}/classes/SpriteNameLogFormatter.php";
$wgAutoloadClasses['SpriteSheetAPI']			= "{$extDir}/SpriteSheet.api.php";
$wgAutoloadClasses['TemplateSpriteSheetEditor']	= "{$extDir}/templates/TemplateSpriteSheetEditor.php";

$wgHooks['ParserFirstCallInit'][]				= 'SpriteSheetHooks::onParserFirstCallInit';
$wgHooks['ImagePageShowTOC'][]					= 'SpriteSheetHooks::onImagePageShowTOC';
$wgHooks['ImageOpenShowImageInlineBefore'][]	= 'SpriteSheetHooks::onImageOpenShowImageInlineBefore';
$wgHooks['PageRenderingHash'][]					= 'SpriteSheetHooks::onPageRenderingHash';
$wgHooks['TitleMoveComplete'][]					= 'SpriteSheetHooks::onTitleMoveComplete';
$wgHooks['LoadExtensionSchemaUpdates'][]		= 'SpriteSheetHooks::onLoadExtensionSchemaUpdates';

$wgAPIModules['spritesheet']					= 'SpriteSheetAPI';

$wgLogTypes['sprite']							= 'sprite';
$wgLogNames['sprite']							= 'sprite_log_name';
$wgLogHeaders['sprite']							= 'sprite_log_description';
$wgLogActionsHandlers['sprite/sheet']			= 'SpriteSheetLogFormatter';
$wgLogActionsHandlers['sprite/sprite']			= 'SpriteNameLogFormatter';
$wgLogActionsHandlers['sprite/sprite-deleted']	= 'SpriteNameLogFormatter';
$wgLogActionsHandlers['sprite/sprite-rename']	= 'SpriteNameLogFormatter';
$wgLogActionsHandlers['sprite/slice']			= 'SpriteNameLogFormatter';
$wgLogActionsHandlers['sprite/slice-deleted']	= 'SpriteNameLogFormatter';
$wgLogActionsHandlers['sprite/slice-rename']	= 'SpriteNameLogFormatter';

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
		'please_select_sprite_type',
		'show_named_sprites',
		'hide_named_sprites',
		'click_to_edit',
		'click_grid_for_preview',
		'no_results_named_sprites'
	],
	'position'		=> 'top'
];
