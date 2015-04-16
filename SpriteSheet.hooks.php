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
	 * Valid parameters.
	 *
	 * @var		array
	 */
	static private $parameters = [
		'file' => [
			'required'	=> true,
			'default'	=> null
		],
		'name' => [
			'required'	=> false,
			'default'	=> null
		],
		'column' => [
			'required'	=> false,
			'default'	=> null
		],
		'row' => [
			'required'	=> false,
			'default'	=> null
		],
		'width' => [
			'required'	=> false,
			'default'	=> null,
		],
		'link' => [
			'required'	=> false,
			'default'	=> null
		],
	];

	/**
	 * Any error messages that may have been triggerred.
	 *
	 * @var		array
	 */
	static private $errors = false;

	/**
	 * The tag type being processed, either sprite or slice.
	 *
	 * @var		array
	 */
	static private $tagType = false;

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
		$parser->setFunctionHook("sprite", "SpriteSheetHooks::generateSpriteOutput", SFH_OBJECT_ARGS);
		$parser->setFunctionHook("slice", "SpriteSheetHooks::generateSliceOutput", SFH_OBJECT_ARGS);
		$parser->setFunctionHook("ifsprite", "SpriteSheetHooks::generateIfSpriteOutput", SFH_OBJECT_ARGS);
		$parser->setFunctionHook("ifslice", "SpriteSheetHooks::generateIfSliceOutput", SFH_OBJECT_ARGS);

		return true;
	}

	/**
	 * The #sprite parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @param	object	PPFrame
	 * @param	array	Arguments
	 * @return	string	Wiki Text
	 */
	static public function generateSpriteOutput(Parser &$parser, PPFrame $frame, $arguments) {
		self::$errors = false;
		self::$tagType = "sprite";

		/************************************/
		/* Clean Parameters                 */
		/************************************/
		$rawParameterOptions = [];
		if (is_array($arguments)) {
			foreach ($arguments as $argument) {
				$rawParameterOptions[] = trim($frame->expand($argument));
			}
		}
		$parameters = self::cleanAndSetupParameters($rawParameterOptions);

		//Check if any errors occurred during parameter processing and immediately alert the user to them.
		if (!empty(self::$errors)) {
			return self::makeErrorBox();
		}

		$title = Title::newFromDBKey($parameters['file']);

		if ($title->isKnown()) {
			$spriteSheet = SpriteSheet::newFromTitle($title, true);

			if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
				//Either a sprite sheet does not exist or has invalid values.
				return self::makeError('no_sprite_sheet_defined', [$title->getPrefixedText()]);
			}

			if (!empty($parameters['name'])) {
				$spriteName = $spriteSheet->getSpriteName($parameters['name']);
				if (!$spriteName->exists()) {
					self::setError('could_not_find_named_sprite', [$parameters['file'], $parameters['name']]);
					return self::makeErrorBox();
				}
				if ($spriteName->getType() != 'sprite') {
					self::setError('wrong_named_sprite_slice');
					return self::makeErrorBox();
				}

				$html = $spriteSheet->getSpriteFromName($spriteName->getName(), $parameters['width']);
			} else {
				if (!isset($parameters['column']) || !is_numeric($parameters['column']) || $parameters['column'] < 0) {
					self::setError('invalid_column_parameter');
					return self::makeErrorBox();
				}
				if (!isset($parameters['row']) || !is_numeric($parameters['row']) || $parameters['row'] < 0) {
					self::setError('invalid_row_parameter');
					return self::makeErrorBox();
				}
				$html = $spriteSheet->getSpriteAtCoordinates($parameters['column'], $parameters['row'], $parameters['width']);
			}
		} else {
			self::setError('could_not_find_title', [$parameters['file']]);
			return self::makeErrorBox();
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
		self::$errors = false;
		self::$tagType = "slice";

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
	 * Clean user supplied parameters and setup defaults.
	 *
	 * @access	private
	 * @param	array	Raw strings of 'parameter=option'.
	 * @return	array	Safe Parameter => Option key value pairs.
	 */
	static private function cleanAndSetupParameters($rawParameterOptions) {
		//Check user supplied parameters.
		$cleanParameterOptions = [];
		foreach ($rawParameterOptions as $raw) {
			$equals = strpos($raw, '=');
			if ($equals === false || $equals === 0 || $equals === strlen($raw) - 1) {
				continue;
			}

			list($parameter, $option) = explode('=', $raw);
			$parameter = strtolower(trim($parameter));
			$option = trim($option);

			if (isset(self::$parameters[$parameter])) {
				if (is_array(self::$parameters[$parameter]['values'])) {
					if (!in_array($option, self::$parameters[$parameter]['values'])) {
						//Throw an error.
						self::setError('spritesheet_error_invalid_option', [$parameter, $option]);
					} else {
						$cleanParameterOptions[$parameter] = $option;
					}
				} else {
					$cleanParameterOptions[$parameter] = $option;
				}
			} else {
				self::setError('spritesheet_error_bad_parameter', [$parameter]);
			}
		}

		foreach (self::$parameters as $parameter => $parameterData) {
			if ($parameterData['required'] && !array_key_exists($parameter, $cleanParameterOptions)) {
				self::setError('spritesheet_error_parameter_required', [$parameter]);
			}
			//Assign the default if not supplied by the user and a default exists.
			if (!$parameterData['required'] && !array_key_exists($parameter, $cleanParameterOptions) && $parameterData['default'] !== null) {
				$cleanParameterOptions[$parameter] = $parameterData['default'];
			}
		}

		return $cleanParameterOptions;
	}

	/**
	 * Set a non-fatal error to be returned to the end user later.
	 *
	 * @access	private
	 * @param	string	Message language string.
	 * @param	array	[Optional] Message replacements.
	 * @return	void
	 */
	static private function setError($message, $replacements = []) {
		self::$errors[] = wfMessage($message, $replacements)->escaped();
	}

	/**
	 * Make a standard error box.
	 *
	 * @access	private
	 * @return	string	HTML Error
	 */
	static private function makeErrorBox() {
		return "
		<div class='errorbox'>
			<strong>SpriteSheet ".SPRITESHEET_VERSION."</strong><br/>
			".implode("<br/>\n", self::$errors)."
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
		global $wgUser;

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

		if (self::checkAndDoRollbacks()) {
			$output->redirect(self::$spriteSheet->getTitle()->getFullURL());
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
	 * Pefrom any rollbacks as necessary.
	 *
	 * @access	private
	 * @return	boolean	If rollbacks were performed.
	 */
	static private function checkAndDoRollbacks() {
		global $wgRequest, $wgUser;

		$action = $wgRequest->getVal('sheetAction', false);

		if (($action == 'diff' || $action == 'rollback')) {
			//Handle sheet roll backs.
			if ($wgRequest->getInt('sheetPreviousId') > 0) {
				self::$oldSpriteSheet = self::$spriteSheet->getRevisionById($wgRequest->getInt('sheetPreviousId'));

				if ($action == 'rollback' && $wgUser->isAllowed('spritesheet_rollback') && self::$oldSpriteSheet !== false) {
					//Perform the rollback then redirect to this page with a success message and the editor opened.
					self::$spriteSheet->setColumns(self::$oldSpriteSheet->getColumns());
					self::$spriteSheet->setRows(self::$oldSpriteSheet->getRows());
					self::$spriteSheet->setInset(self::$oldSpriteSheet->getInset());

					return self::$spriteSheet->save();
				}
			}

			//Handle individual sprite roll backs.
			if ($wgRequest->getInt('spritePreviousId') > 0) {
				$oldSpriteName = SpriteName::newFromRevisionId($wgRequest->getInt('spritePreviousId'));

				if ($action == 'rollback' && $wgUser->isAllowed('spritesheet_rollback') && $oldSpriteName !== false && $oldSpriteName->getId()) {
					$spriteName = SpriteName::newFromId($oldSpriteName->getId(), self::$spriteSheet);
					if ($spriteName !== false && $spriteName->getId()) {
						$spriteName->setName($oldSpriteName->getName());
						$spriteName->setType($oldSpriteName->getType());
						$spriteName->setValues($oldSpriteName->getValues());
						$spriteName->setDeleted($oldSpriteName->isDeleted());

						return $spriteName->save();
					}
				}
			}
		}

		return false;
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
		$updater->addExtensionUpdate(['addTable', 'spritesheet_rev', "{$extDir}/install/sql/spritesheet_table_spritesheet_rev.sql", true]);
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
