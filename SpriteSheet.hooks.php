<?php
/**
 * SpriteSheet
 * SpriteSheet Hooks
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class SpriteSheetHooks {
	/**
	 * Display link to invoke sprite sheet editor.
	 *
	 * @access	public
	 * @param	object	ImagePage Object
	 * @param	array	Array of table of contents links to modify.
	 * @return	boolean True
	 */
	static public function onImagePageShowTOC(ImagePage $imagePage, &$toc) {
		$toc[] = '<li><a href="#spritesheet">'.wfMessage('sprite_sheet')->escaped().'</a></li>';

		return true;
	}

	/**
	 * Display link to invoke sprite sheet editor.
	 *
	 * @access	public
	 * @param	object	ImagePage Object
	 * @param	object	OutputPage Object
	 * @return	boolean True
	 */
	static public function onImageOpenShowImageInlineBefore(ImagePage $imagePage, OutputPage $output) {
		$output->addModules('ext.spriteSheet');

		$spriteSheet = SpriteSheet::newFromTitle($imagePage->getTitle());

		$form = "
		<form>
			<fieldset>
				<label for='sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
				<input id='sprite_columns' name='sprite_columns' type='text' value='".$spriteSheet->getColumns()."'/>

				<label for='sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
				<input id='sprite_rows' name='sprite_rows' type='text' value='".$spriteSheet->getRows()."'/>

				<label for='sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
				<input id='sprite_inset' name='sprite_inset' type='text' value='".$spriteSheet->getInset()."'/>
			</fieldset>
		</form>";

		$output->addHtml($form);

		return true;
	}

	/**
	 * Setups and Modifies Database Information
	 *
	 * @access	public
	 * @param	object	[Optional] DatabaseUpdater Object
	 * @return	boolean	true
	 */
	static public function onLoadExtensionSchemaUpdates($updater = null) {
		$extDir = __DIR__;

		$updater->addExtensionUpdate(['addTable', 'spritesheet', "{$extDir}/install/sql/spritesheet_table_spritesheet.sql", true]);

		return true;
	}
}
