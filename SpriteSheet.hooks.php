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
		$parser->setFunctionHook("ifsprite", "SpriteSheetHooks::generateIfSpriteOutput");
		$parser->setFunctionHook("ifslice", "SpriteSheetHooks::generateIfSliceOutput");

		return true;
	}

	/**
	 * The #sprite parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @param	string	Page title with namespace
	 * @param	integer	Column Position
	 * @param	integer	Row Position
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	string	Wiki Text
	 */
	static public function generateSpriteOutput(&$parser, $file = null, $column = 0, $row = 0, $thumbWidth = 0) {
		$namedMode = false;
		if (!is_numeric($column) && empty($thumbWidth)) {
			//For named sprites the column will be the sprite name and row will be the optional thumb width.  Thus the actual $thumbWidth variable should be empty.
			$namedMode = true;
			$rawSpriteName = trim($column);
			$thumbWidth = intval($row);
		} else {
			$column		= abs(intval($column));
			$row		= abs(intval($row));
			$thumbWidth	= abs(intval($thumbWidth));
		}

		$title = Title::newFromDBKey($file);

		if ($title->isKnown()) {
			$spriteSheet = SpriteSheet::newFromTitle($title);

			if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
				//Either a sprite sheet does not exist or has invalid values.
				return "<div class='errorbox'>".wfMessage('no_sprite_sheet_defined', $title->getPrefixedText())->text()."</div>";
			}

			if ($namedMode) {
				$spriteName = $spriteSheet->getSpriteName($rawSpriteName);
				if (!$spriteName->exists()) {
					return "<div class='errorbox'>".wfMessage('could_not_find_named_sprite', $file, $rawSpriteName)->text()."</div>";
				}
				if ($spriteName->getType() != 'sprite') {
					return "<div class='errorbox'>".wfMessage('wrong_named_sprite_slice')->text()."</div>";
				}

				$html = $spriteSheet->getSpriteFromName($spriteName->getName(), $thumbWidth);
			} else {
				$html = $spriteSheet->getSpriteAtCoordinates($column, $row, $thumbWidth);
			}
		} else {
			return "<div class='errorbox'>".wfMessage('could_not_find_title', $file)->text()."</div>";
		}

		return [
			$html,
			'noparse'	=> true,
			'isHTML'	=> true
		];
	}

	/**
	 * The #ifsprite parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @param	string	Page title with namespace
	 * @param	string	Sprite Name
	 * @param	integer	[Optional] Thumbnail Width
	 * @param	string	Wiki Text to render if the sprite is not found.
	 * @return	string	Wiki Text
	 */
	static public function generateIfSpriteOutput(&$parser, $file = null, $name = null, $thumbWidth = 0, $wikiText = null) {
		$output = self::generateSpriteOutput($parser, $file, $name, $thumbWidth);

		if (!is_array($output)) {
			return $wikiText;
		}
		return $output;
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
		$namedMode = false;
		$pixelMode = false;
		if (!is_numeric($column) && empty($widthPercent)) {
			//For named slice the $xPercent will be the slice name and $yPercent will be the optional thumb width.  The rest should be empty.
			$namedMode = true;
			$rawSliceName = trim($xPercent);
			$thumbWidth = intval($yPercent);
		} else {
			if (!is_numeric($xPercent) && strpos($xPercent, 'px') > 0) {
				//User has specified to use pixels for measurement.
				$pixelMode = true;
			}
			$xPercent		= abs(floatval($xPercent));
			$yPercent		= abs(floatval($yPercent));
			$widthPercent	= abs(floatval($widthPercent));
			$heightPercent	= abs(floatval($heightPercent));
			$thumbWidth		= abs(intval($thumbWidth));
		}

		$title = Title::newFromDBKey($file);

		if ($title) {
			$spriteSheet = SpriteSheet::newFromTitle($title);

			if ($spriteSheet !== false) {
				if ($namedMode) {
					$sliceName = $spriteSheet->getSpriteName($rawSliceName);
					if (!$sliceName->exists()) {
						return "<div class='errorbox'>".wfMessage('could_not_find_named_slice', $file, $rawSliceName)->text()."</div>";
					}
					if ($sliceName->getType() != 'slice') {
						return "<div class='errorbox'>".wfMessage('wrong_named_sprite_slice')->text()."</div>";
					}

					$html = $spriteSheet->getSliceFromName($sliceName->getName(), $thumbWidth, $pixelMode);
				} else {
					$html = $spriteSheet->getSlice($xPercent, $yPercent, $widthPercent, $heightPercent, $thumbWidth, $pixelMode);
				}

				return [
					$html,
					'noparse'	=> true,
					'isHTML'	=> true
				];
			}
		}

		return "<div class='errorbox'>".wfMessage('could_not_find_title', $file)->text()."</div>";
	}

	/**
	 * The #ifslice parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser object passed as a reference.
	 * @param	string	Page title with namespace
	 * @param	string	Slice Name
	 * @param	integer	[Optional] Thumbnail Width
	 * @param	string	Wiki Text to render if the slice is not found.
	 * @return	string	Wiki Text
	 */
	static public function generateIfSliceOutput(&$parser, $file = null, $name = null, $thumbWidth = 0, $wikiText = null) {
		$output = self::generateSliceOutput($parser, $file, $name, $thumbWidth);

		if (!is_array($output)) {
			return $wikiText;
		}
		return $output;
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

		if (!self::$spriteSheet) {
			//This can occur if the page entry in the database becomes corrupted.  End users will have to reupload the image to fix the page entry before SpriteSheet will work on it.
			return true;
		}

		$spriteNames = self::$spriteSheet->getAllSpriteNames();

		$inputType = (self::$spriteSheet->isLocal() ? 'number' : 'hidden');

		$form = "
		<div id='spritesheet_editor'>
			<form>
				<fieldset id='spritesheet_form'>
					<legend>".wfMessage('sprite_sheet')->escaped()."</legend>
					".(!self::$spriteSheet->isLocal() ? "<pre>".wfMessage('visit_remote_repository_to_edit_sprite_sheet', $imagePage->getDisplayedFile()->getDescriptionUrl())."</pre>" : '')."
					<label for='sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
					<input id='sprite_columns' name='sprite_columns' type='number' min='0'".(!self::$spriteSheet->isLocal() ? " disabled='disabled'" : '')." value='".self::$spriteSheet->getColumns()."'/>

					<label for='sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
					<input id='sprite_rows' name='sprite_rows' type='number' min='0'".(!self::$spriteSheet->isLocal() ? " disabled='disabled'" : '')." value='".self::$spriteSheet->getRows()."'/>

					<label for='sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
					<input id='sprite_inset' name='sprite_inset' type='number' min='0'".(!self::$spriteSheet->isLocal() ? " disabled='disabled'" : '')." value='".self::$spriteSheet->getInset()."'/>

					<input name='spritesheet_id' type='hidden' value='".self::$spriteSheet->getId()."'/>
					<input name='page_title' type='hidden' value='".htmlentities(self::$spriteSheet->getTitle()->getPrefixedDBkey(), ENT_QUOTES)."'/>
					".(self::$spriteSheet->isLocal() ? "<button id='save_sheet' name='save_sheet' type='button'>".wfMessage('save_sheet')->escaped()."</button>" : '')."

					<pre id='sprite_preview'>".wfMessage('click_grid_for_preview')->escaped()."</pre>";
		if (self::$spriteSheet->isLocal()) {
			$form .= "
					<div id='named_sprite_add' class='named_sprite_popup'>
						<input id='sprite_name' name='sprite_name' type='text' value=''/>
						<button id='save_named_sprite' name='save_named_sprite' type='button'>".wfMessage('save_named_sprite')->escaped()."</button>
						<a class='close'>&nbsp;</a>
					</div>";
		} else {
			$form .= "
					<input name='isRemote' type='hidden' value='1'/>
					<input name='remoteApiUrl' type='hidden' value='".$imagePage->getDisplayedFile()->getRepo()->getApiUrl()."'/>";
		}
		$form .= "
				</fieldset>
			</form>
			<button id='show_named_sprites' name='show_named_sprites' type='button'>".wfMessage('show_named_sprites')->escaped()."</button>
			<div id='named_sprites'></div>";
		if (self::$spriteSheet->isLocal()) {
			$form .= "
			<div id='named_sprite_editor' class='named_sprite_popup'>
				<input id='update_sprite_name' name='update_sprite_name' type='text' value=''/>
				<button id='update_named_sprite' name='update_named_sprite' type='button'>".wfMessage('update_name')->escaped()."</button>
				<button id='delete_named_sprite' name='delete_named_sprite' type='button'>".wfMessage('delete_name')->escaped()."</button>
				<a class='close'>&nbsp;</a>
			</div>";
		}
		$form .= "
		</div>";

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
	 * Modify the page rendering hash when altering the output.
	 *
	 * @access	public
	 * @param	object	Old Title
	 * @param	object	New Title
	 * @param	object	User who performed the move.
	 * @param	integer	Old/Current ID of the Article/Page
	 * @param	integer	New(Redirect) ID of redirect page created, if created.
	 * @param	string	Reason given by the user performing the move.
	 * @return	boolean True
	 */
	static public function onTitleMoveComplete(Title &$oldTitle, Title &$newTitle, User &$user, $oldId, $newId, $reason = null) {
		$spriteSheet = SpriteSheet::newFromTitle($oldTitle);

		if (!$spriteSheet || !$spriteSheet->exists()) {
			//No sprite sheet to update the Title on so we can safely skip this.
			return true;
		}

		$spriteSheet->setTitle($newTitle);
		$spriteSheet->save();

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

		//2015-02-23
		$updater->addExtensionUpdate(['renameIndex', 'spritename', 'name', 'spritesheet_id_name', false, "{$extDir}/upgrade/sql/spritesheet_upgrade_spritesheet_alter_index_name.sql", true]);

		//2015-03-03
		$updater->addExtensionUpdate(['modifyField', 'spritesheet', 'page_id', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritesheet_alter_page_id.sql", true]);

		return true;
	}
}
