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
	 * SpriteSheet Object - Used to hold a SpriteSheet when viewing an image page.
	 *
	 * @var		object
	 */
	static private $spriteSheet = null;

	/**
	 * Sets up this extension's parser functions.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @return	boolean	true
	 */
	static public function onParserFirstCallInit(Parser &$parser) {
		$parser->setFunctionHook("sprite", "SpriteSheetHooks::generateSpriteOutput");
		$parser->setFunctionHook("slice", "SpriteSheetHooks::generateSliceOutput");

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
	 * @param	integer	[Optional] Thumbnail Width
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
			$spriteName = SpriteName::newFromName($file);
			if (!$spriteName || $spriteName->getSpriteSheet() === false) {
				return "<div class='errorbox'>".wfMessage('could_not_find_title', $file)->text()."</div>";
			} else {
				if ($spriteName->getType() != 'sprite') {
					return "<div class='errorbox'>".wfMessage('wrong_named_sprite_slice')->text()."</div>";
				}
				$spriteSheet = $spriteName->getSpriteSheet();
				$values = $spriteName->getValues();
				$thumbWidth = $column;
				$column = $values['xPos'];
				$row = $values['yPos'];
			}
		}

		if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
			//Either a sprite sheet does not exist or has invalid values.
			return "<div class='errorbox'>".wfMessage('no_sprite_sheet_defined', $title->getPrefixedText())->text()."</div>";
		}

		$html = $spriteSheet->getSpriteAtCoordinates($column, $row, $thumbWidth);

		return [
			$html,
			'noparse'	=> true,
			'isHTML'	=> true
		];
	}
	/**
	 * The #slice parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @param	string	Page title with namespace
	 * @param	integer	X coordinate, percentage
	 * @param	integer	Y coordinate, percentage
	 * @param	integer	Width, percentage
	 * @param	integer	Height, percentage
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	string	Wiki Text
	 */
	static public function generateSliceOutput(&$parser, $file = null, $xPercent = 0, $yPercent = 0, $widthPercent = 0, $heightPercent = 0, $thumbWidth = 0) {
		$xPercent		= abs(floatval($xPercent));
		$yPercent		= abs(floatval($yPercent));
		$widthPercent	= abs(floatval($widthPercent));
		$heightPercent	= abs(floatval($heightPercent));
		$thumbWidth		= abs(intval($thumbWidth));

		$title = Title::newFromDBKey($file);

		if ($title->exists()) {
			//Does not need to be a valid/saved SpriteSheet.  A new SpriteSheet will still give us slices.
			$spriteSheet = SpriteSheet::newFromTitle($title);
		} else {
			$spriteName = SpriteName::newFromName($file);
			if (!$spriteName || $spriteName->getSpriteSheet() === false) {
				return "<div class='errorbox'>".wfMessage('could_not_find_title', $file)->text()."</div>";
			} else {
				if ($spriteName->getType() != 'slice') {
					return "<div class='errorbox'>".wfMessage('wrong_named_sprite_slice')->text()."</div>";
				}
				$spriteSheet = $spriteName->getSpriteSheet();
				$values = $spriteName->getValues();

				$thumbWidth = $xPercent;
				$xPercent = $values['xPercent'];
				$yPercent = $values['yPercent'];
				$widthPercent = $values['widthPercent'];
				$heightPercent = $values['heightPercent'];
			}
		}

		$html = $spriteSheet->getSlice($xPercent, $yPercent, $widthPercent, $heightPercent, $thumbWidth);

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

		self::$spriteSheet = SpriteSheet::newFromTitle($imagePage->getTitle());

		$form = "
		<form id='spritesheet_editor'>
			<fieldset id='spritesheet_form'>
				<legend>".wfMessage('sprite_sheet')->escaped()."</legend>
				<label for='sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
				<input id='sprite_columns' name='sprite_columns' type='text' value='".self::$spriteSheet->getColumns()."'/>

				<label for='sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
				<input id='sprite_rows' name='sprite_rows' type='text' value='".self::$spriteSheet->getRows()."'/>

				<label for='sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
				<input id='sprite_inset' name='sprite_inset' type='text' value='".self::$spriteSheet->getInset()."'/>

				<input name='spritesheet_id' type='hidden' value='".self::$spriteSheet->getId()."'/>
				<input name='page_id' type='hidden' value='".self::$spriteSheet->getTitle()->getArticleId()."'/>
				<input name='page_title' type='hidden' value='".htmlentities(self::$spriteSheet->getTitle()->getPrefixedDBkey(), ENT_QUOTES)."'/>
				<button id='save_sheet' name='save_sheet' type='button'>".wfMessage('save_sheet')->escaped()."</button>

				<pre id='sprite_preview'>".wfMessage('click_grid_for_preview')->escaped()."</pre>
				<div id='named_sprite_popup'>
					<input id='sprite_name' name='sprite_name' type='text' value=''/>
					<button id='save_named_sprite' name='save_named_sprite' type='button'>".wfMessage('save_named_sprite')->escaped()."</button>
				</div>
			</fieldset>
		</form>";

		$output->addHtml($form);

		return true;
	}

	/**
	 * Modify the page rendering hash when altering the output.
	 *
	 * @access	public
	 * @param	string	Page rendering hash
	 * @param	object	User Object
	 * @param	array	Options used to generate the initial hash.
	 * @return	boolean True
	 */
	static public function onPageRenderingHash(&$hash, User $user, &$forOptions) {
		if (self::$spriteSheet instanceOf SpriteSheet && self::$spriteSheet->getId()) {
			$hash .= '!'.self::$spriteSheet->getId().'-'.self::$spriteSheet->getColumns().'-'.self::$spriteSheet->getRows().'-'.self::$spriteSheet->getInset();
		}

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
		$updater->addExtensionUpdate(['addTable', 'spritename', "{$extDir}/install/sql/spritesheet_table_spritename.sql", true]);

		return true;
	}
}
