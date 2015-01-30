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
	 * Sets up this extension's parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit(Parser &$parser) {
		$parser->setFunctionHook("sprite", "SpriteSheetHooks::generateSpriteOutput");

		return true;
	}

	/**
	 * The #sprite parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @param	string	Page title with namespace
	 * @param	integer Column Position
	 * @param	integer Row Position
	 * @param	integer Inset in pixels
	 * @return	string	Wiki Text
	 */
	static public function generateSpriteOutput(&$parser, $file = null, $column = 0, $row = 0, $thumbWidth = 0) {
		$column		= abs(intval($column));
		$row		= abs(intval($row));
		$thumbWidth	= abs(intval($thumbWidth));

		$title = Title::newFromDBKey($file);

		if ($title->exists()) {
			$spriteSheet = SpriteSheet::newFromTitle($title);
		} else {
			return "<div class='errorbox'>".wfMessage('could_not_find_title', $file)->text()."</div>";
		}

		if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
			//Either a sprite sheet does not exist or has invalid values.
			return "<div class='errorbox'>".wfMessage('no_sprite_sheet_defined', $title->getPrefixedText())->text()."</div>";
		}

		$html = $spriteSheet->getSpriteAtPos($column, $row, $thumbWidth);

		return [
			$html,
			'noparse'	=> true,
			'isHTML'	=> true
		];
	}

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
		<form id='spritesheet_editor'>
			<fieldset id='spritesheet_form'>
				<legend>".wfMessage('sprite_sheet')->escaped()."</legend>
				<label for='sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
				<input id='sprite_columns' name='sprite_columns' type='text' value='".$spriteSheet->getColumns()."'/>

				<label for='sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
				<input id='sprite_rows' name='sprite_rows' type='text' value='".$spriteSheet->getRows()."'/>

				<label for='sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
				<input id='sprite_inset' name='sprite_inset' type='text' value='".$spriteSheet->getInset()."'/>

				<input name='sid' type='hidden' value='".$spriteSheet->getId()."'/>
				<input name='page_id' type='hidden' value='".$spriteSheet->getTitle()->getArticleId()."'/>
				<input name='page_title' type='hidden' value='".htmlentities($spriteSheet->getTitle()->getPrefixedDBkey(), ENT_QUOTES)."'/>
				<button id='sprite_save' name='sprite_save' type='button'>".wfMessage('save')->escaped()."</button>

				<pre id='sprite_preview'>".wfMessage('click_grid_for_preview')->escaped()."</pre>
				<pre id='slice_preview'>".wfMessage('click_grid_for_preview')->escaped()."</pre>
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
