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
	 * Old SpriteSheet Object
	 *
	 * @var		object
	 */
	static private $oldSpriteSheet = null;

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
			$spriteSheet = SpriteSheet::newFromTitle($title, true);

			if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
				//Either a sprite sheet does not exist or has invalid values.
				return self::makeError('no_sprite_sheet_defined', [$title->getPrefixedText()]);
			}

			if ($namedMode) {
				$spriteName = $spriteSheet->getSpriteName($rawSpriteName);
				if (!$spriteName->exists()) {
					return self::makeError('could_not_find_named_sprite', [$file, $rawSpriteName]);
				}
				if ($spriteName->getType() != 'sprite') {
					return self::makeError('wrong_named_sprite_slice');
				}

				$html = $spriteSheet->getSpriteFromName($spriteName->getName(), $thumbWidth);
			} else {
				$html = $spriteSheet->getSpriteAtCoordinates($column, $row, $thumbWidth);
			}
		} else {
			return self::makeError('could_not_find_title', [$file]);
		}

		$parser->getOutput()->addModules('ext.spriteSheet');

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
			$spriteSheet = SpriteSheet::newFromTitle($title, true);

			if ($spriteSheet !== false) {
				if ($namedMode) {
					$sliceName = $spriteSheet->getSpriteName($rawSliceName);
					if (!$sliceName->exists()) {
						return self::makeError('could_not_find_named_slice', [$file, $rawSliceName]);
					}
					if ($sliceName->getType() != 'slice') {
						return self::makeError('wrong_named_sprite_slice');
					}

					$html = $spriteSheet->getSliceFromName($sliceName->getName(), $thumbWidth, $pixelMode);
				} else {
					$html = $spriteSheet->getSlice($xPercent, $yPercent, $widthPercent, $heightPercent, $thumbWidth, $pixelMode);
				}

				$parser->getOutput()->addModules('ext.spriteSheet');

				return [
					$html,
					'noparse'	=> true,
					'isHTML'	=> true
				];
			}
		}

		return self::makeError('could_not_find_title', [$file]);
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
	 * Make a standard error box.
	 *
	 * @access	private
	 * @param	string	Language String Message
	 * @param	array	[Optional] Array of extra parameters.
	 * @return	string	HTML Error
	 */
	static private function makeError($message, $parameters = []) {
		return "
		<div class='errorbox'>
			<strong>SpriteSheet ".SPRITESHEET_VERSION."</strong><br/>
			".wfMessage($message, $parameters)->text()."
		</div>";
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
		if (strpos($imagePage->getDisplayedFile()->getMimeType(), 'image/') === false) {
			//Can not sprite sheet non-images.
			return true;
		}

		$toc[] = '<li><a id="spritesheet_toc" href="#">'.wfMessage('sprite_sheet')->escaped().'</a></li>';

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
		global $wgRequest, $wgUser;

		$output->addModules('ext.spriteSheet');

		if (strpos($imagePage->getDisplayedFile()->getMimeType(), 'image/') === false) {
			//Can not sprite sheet non-images.
			return true;
		}

		self::$spriteSheet = SpriteSheet::newFromTitle($imagePage->getTitle());

		if (!self::$spriteSheet) {
			//This can occur if the page entry in the database becomes corrupted.  End users will have to reupload the image to fix the page entry before SpriteSheet will work on it.
			return true;
		}

		$logLink = Linker::link(SpecialPage::getTitleFor('Log'), wfMessage('sprite_sheet_log')->escaped(), [], ['page' => self::$spriteSheet->getTitle()->getPrefixedText()]);

		//Permission checks.
		$canEdit = true;
		if (!$wgUser->isAllowed('edit_sprites') || !$imagePage->getTitle()->userCan('edit')) {
			$canEdit = false;
		}

		$disabled = (!self::$spriteSheet->isLocal() || !$canEdit ? " disabled='disabled'" : '');
		$readOnly = (!self::$spriteSheet->isLocal() || !$canEdit ? " readonly='readonly'" : '');

		$templates = new TemplateSpriteSheetEditor();
		$form = $templates->spriteSheetForm($imagePage, self::$spriteSheet, self::$oldSpriteSheet, $logLink, $disabled, $readOnly);

		$output->addHtml($form);

		return true;
	}

	/**
	 * Function Documentation
	 *
	 * @access	private
	 * @return	void
	 */
	private function checkAndDoRollbacks() {
		$action = $wgRequest->getVal('sheetAction', false);

		if (($action == 'diff' || $action == 'rollback')) {
			if ($wgRequest->getInt('sheetPreviousId') > 0) {
				self::$oldSpriteSheet = self::$spriteSheet->getRevisionByOldId($wgRequest->getInt('sheetPreviousId'));
			}
			if ($wgRequest->getInt('spritePreviousId') > 0) {
				self::$oldSpriteSheet = self::$spriteSheet->getRevisionByOldId($wgRequest->getInt('sheetPreviousId'));
			}
		}

		if ($action == 'rollback' && $wgUser->isAllowed('spritesheet_rollback') && self::$oldSpriteSheet !== false) {
			//Perform the rollback then redirect to this page with a success message and the editor opened.
			self::$spriteSheet->setColumns(self::$oldSpriteSheet->getColumns());
			self::$spriteSheet->setRows(self::$oldSpriteSheet->getRows());
			self::$spriteSheet->setInset(self::$oldSpriteSheet->getInset());
			self::$spriteSheet->save();

			$output->redirect(self::$spriteSheet->getTitle()->getFullURL());
			return true;
		}
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
		$updater->addExtensionUpdate(['addTable', 'spritesheet_old', "{$extDir}/install/sql/spritesheet_table_spritesheet_old.sql", true]);
		$updater->addExtensionUpdate(['addTable', 'spritename', "{$extDir}/install/sql/spritesheet_table_spritename.sql", true]);
		$updater->addExtensionUpdate(['addTable', 'spritename_rev', "{$extDir}/install/sql/spritesheet_table_spritename_rev.sql", true]);

		//2015-02-23
		$updater->addExtensionUpdate(['renameIndex', 'spritename', 'name', 'spritesheet_id_name', false, "{$extDir}/upgrade/sql/spritesheet_upgrade_spritesheet_alter_index_name.sql", true]);

		//2015-03-03
		$updater->addExtensionUpdate(['modifyField', 'spritesheet', 'page_id', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritesheet_alter_page_id.sql", true]);

		//2015-04-07
		$updater->addExtensionUpdate(['addField', 'spritesheet', 'edited', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritesheet_add_edited.sql", true]);

		//2015-04-13
		$updater->addExtensionUpdate(['addField', 'spritename', 'edited', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritename_add_edited.sql", true]);
		$updater->addExtensionUpdate(['addField', 'spritename', 'deleted', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritename_add_deleted.sql", true]);
		$updater->addExtensionUpdate(['addField', 'spritename_rev', 'deleted', "{$extDir}/upgrade/sql/spritesheet_upgrade_spritename_rev_add_deleted.sql", true]);

		return true;
	}
}
