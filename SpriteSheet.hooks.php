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
	static private $validParameters = [
		'sprite'	=> [
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
				'default'	=> null,
				'sanitize'	=> "#^(?P<integer>\d+)$#i"
			],
			'row' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<integer>\d+)$#i"
			],
			'resize' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|$)#i"
			],
			'link' => [
				'required'	=> false,
				'default'	=> null
			],
			'wikitext' => [
				'required'	=> false,
				'default'	=> null
			]
		],
		'slice'	=> [
			'file' => [
				'required'	=> true,
				'default'	=> null
			],
			'name' => [
				'required'	=> false,
				'default'	=> null
			],
			'x' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|%$|$)#i"
			],
			'y' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|%$|$)#i"
			],
			'width' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|%$|$)#i"
			],
			'height' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|%$|$)#i"
			],
			'resize' => [
				'required'	=> false,
				'default'	=> null,
				'sanitize'	=> "#^(?P<number>\d+(?:\.\d+)?)(?P<unit>px$|$)#i"
			],
			'link' => [
				'required'	=> false,
				'default'	=> null
			],
			'wikitext' => [
				'required'	=> false,
				'default'	=> null
			]
		],
	];

	/**
	 * Any error messages that may have been triggerred.
	 *
	 * @var		array
	 */
	static private $errors = [];

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
		self::$errors = [];
		self::$tagType = "sprite";

		$parameters = self::cleanAndSetupParameters($arguments, $frame);

		//Check if any errors occurred during parameter processing and immediately alert the user to them.
		if (!empty(self::$errors)) {
			return self::makeErrorBox();
		}

		$title = Title::newFromDBKey($parameters['file']);

		if ($title !== null && $title->isKnown()) {
			$spriteSheet = SpriteSheet::newFromTitle($title, true);

			if (!$spriteSheet->getId() || !$spriteSheet->getColumns() || !$spriteSheet->getRows()) {
				//Either a sprite sheet does not exist or has invalid values.
				self::setError('no_sprite_sheet_defined', [$title->getPrefixedText()]);
				return self::makeErrorBox();
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

				$html = $spriteSheet->getSpriteHtmlFromName($spriteName->getName(), $parameters['resize']['number']);
			} else {
				if (!isset($parameters['column']['integer']) || $parameters['column']['integer'] < 0) {
					self::setError('spritesheet_error_invalid_option', ['column', $parameters['column']['integer']]);
					return self::makeErrorBox();
				}
				if (!isset($parameters['row']['integer']) || $parameters['row']['integer'] < 0) {
					self::setError('spritesheet_error_invalid_option', ['row', $parameters['row']['integer']]);
					return self::makeErrorBox();
				}
				$html = $spriteSheet->getSpriteHtml($parameters['column']['integer'], $parameters['row']['integer'], $parameters['resize']['number'], $parameters['link']);
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
	 * @param	object	Parser
	 * @param	object	PPFrame
	 * @param	array	Arguments
	 * @return	string	Wiki Text
	 */
	static public function generateIfSpriteOutput(Parser &$parser, PPFrame $frame, $arguments) {
		$output = self::generateSpriteOutput($parser, $frame, $arguments);

		$parameters = self::cleanAndSetupParameters($arguments, $frame);

		if (!is_array($output)) {
			return $parameters['wikitext'];
		}
		return $output;
	}

	/**
	 * The #slice parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @param	object	PPFrame
	 * @param	array	Arguments
	 * @return	string	Wiki Text
	 */
	static public function generateSliceOutput(Parser &$parser, PPFrame $frame, $arguments) {
		self::$errors = false;
		self::$tagType = "slice";

		$parameters = self::cleanAndSetupParameters($arguments, $frame);

		//Check if any errors occurred during parameter processing and immediately alert the user to them.
		if (!empty(self::$errors)) {
			return self::makeErrorBox();
		}

		$pixelMode = false;

		$title = Title::newFromDBKey($parameters['file']);

		if ($title !== null) {
			$spriteSheet = SpriteSheet::newFromTitle($title, true);

			if ($spriteSheet !== false) {
				if (!empty($parameters['name'])) {
					$sliceName = $spriteSheet->getSpriteName($parameters['name']);
					if (!$sliceName->exists()) {
						self::setError('could_not_find_named_slice', [$parameters['file'], $parameters['name']]);
						return self::makeErrorBox();
					}
					if ($sliceName->getType() != 'slice') {
						self::setError('wrong_named_sprite_slice');
						return self::makeErrorBox();
					}

					$html = $spriteSheet->getSliceHtmlFromName($sliceName->getName(), $parameters['resize']['number'], $pixelMode);
				} else {
					//The unit of measure is allowed to be specified, but they must match to be valid.
					$unitParams = ['x', 'y', 'width', 'height'];
					$totalUnitParams = 4;
					foreach ($unitParams as $unitParam) {
						if (!isset($parameters[$unitParam]['number']) || $parameters[$unitParam]['number'] < 0) {
							self::setError('spritesheet_error_invalid_option', [$unitParam, $parameters[$unitParam]['number']]);
						}

						if ($parameters[$unitParam]['unit'] == 'px') {
							$pixels++;
						}
						if (!$parameters[$unitParam]['unit'] || $parameters[$unitParam]['unit'] == '%') {
							$percents++;
						}
					}
					if (!empty(self::$errors)) {
						return self::makeErrorBox();
					}

					if ($pixels != $totalUnitParams && $percents != $totalUnitParams) {
						self::setError('slice_error_mixed_units');
						return self::makeErrorBox();
					} elseif ($pixels == $totalUnitParams) {
						$pixelMode = true;
					}

					$html = $spriteSheet->getSliceHtml($parameters['x']['number'], $parameters['y']['number'], $parameters['width']['number'], $parameters['height']['number'], $parameters['resize']['number'], $parameters['link'], $pixelMode);
				}

				$parser->getOutput()->addModules('ext.spriteSheet');

				return [
					$html,
					'noparse'	=> true,
					'isHTML'	=> true
				];
			}
		}

		self::setError('could_not_find_title', [$parameters['file']]);
		return self::makeErrorBox();
	}

	/**
	 * The #ifslice parser tag entry point.
	 *
	 * @access	public
	 * @param	object	Parser
	 * @param	object	PPFrame
	 * @param	array	Arguments
	 * @return	string	Wiki Text
	 */
	static public function generateIfSliceOutput(Parser &$parser, PPFrame $frame, $arguments) {
		$output = self::generateSliceOutput($parser, $frame, $arguments);

		$parameters = self::cleanAndSetupParameters($arguments, $frame);

		if (!is_array($output)) {
			return $parameters['wikitext'];
		}
		return $output;
	}

	/**
	 * Clean user supplied parameters and setup defaults.
	 *
	 * @access	private
	 * @param	array	Raw strings of 'parameter=option'.
	 * @param	object	PPFrame
	 * @return	array	Safe Parameter => Option key value pairs.
	 */
	static private function cleanAndSetupParameters($arguments, PPFrame $frame) {
		/************************************/
		/* Clean Parameters                 */
		/************************************/
		$rawParameterOptions = [];
		if (is_array($arguments)) {
			foreach ($arguments as $argument) {
				$rawParameterOptions[] = trim($frame->expand($argument));
			}
		}

		$validParameters = self::$validParameters[self::$tagType];

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

			if (isset($validParameters[$parameter])) {
				$cleanParameterOptions[$parameter] = $option;

				if (is_array($validParameters[$parameter]['values'])) {
					if (!in_array($option, $validParameters[$parameter]['values'])) {
						//Throw an error.
						unset($cleanParameterOptions[$parameter]);
						self::setError('spritesheet_error_invalid_option', [$parameter, $option]);
					} else {
						$cleanParameterOptions[$parameter] = $option;
					}
				}

				if (isset($validParameters[$parameter]['sanitize'])) {
					if (preg_match($validParameters[$parameter]['sanitize'], $option, $matches)) {
						$cleanParameterOptions[$parameter] = $matches;
					} else {
						unset($cleanParameterOptions[$parameter]);
						self::setError('spritesheet_error_invalid_option', [$parameter, $option]);
					}
				}
			} else {
				self::setError('spritesheet_error_bad_parameter', [$parameter]);
			}
		}

		//Enforce required and default.
		foreach ($validParameters as $parameter => $parameterData) {
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
