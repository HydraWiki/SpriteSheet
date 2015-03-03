<?php
/**
 * SpriteSheet
 * SpriteSheet Class
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class SpriteSheet {
	/**
	 * Title Object
	 *
	 * @var		object
	 */
	private $DB = false;

	/**
	 * Title Object
	 *
	 * @var		object
	 */
	private $title = false;

	/**
	 * Data holder for database values.
	 *
	 * @var		array
	 */
	private $data = [];

	/**
	 * Fully loaded from the database.
	 *
	 * @var		boolean
	 */
	protected $isLoaded = false;

	/**
	 * Where this object was loaded from.
	 *
	 * @var		string
	 */
	public $newFrom = null;

	/**
	 * Memory Cache for already loaded SpriteName objects.
	 *
	 * @var		array
	 */
	private $spriteNameCache = [];

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct() {
		$this->DB = wfGetDB(DB_MASTER);
	}

	/**
	 * Create a new instance of this class from a Sprite Sheet database identification number.
	 *
	 * @access	public
	 * @param	integer	Sprite Sheet database identification number.
	 * @return	mixed	SpriteSheet or false on error.
	 */
	static public function newFromId($id) {
		if ($id < 1) {
			return false;
		}

		$spriteSheet = new self();
		$spriteSheet->setId(intval($id));

		$spriteSheet->newFrom = 'id';

		$success = $spriteSheet->load();

		return ($success ? $spriteSheet : false);
	}

	/**
	 * Create a new instance of this class from a Title object.
	 *
	 * @access	public
	 * @param	object	Title
	 * @return	mixed	SpriteSheet or false on error.
	 */
	static public function newFromTitle(Title $title) {
		if ($title->getNamespace() != NS_FILE || !$title->getDBkey()) {
			return false;
		}

		$spriteSheet = new self();
		$spriteSheet->setTitle($title);

		$spriteSheet->newFrom = 'title';

		$success = $spriteSheet->load();

		if (!$spriteSheet->isLoaded && $title->isAlwaysKnown() && !$title->exists()) {
			//This could be a remote file repository title.
			$spriteSheetRemote = SpriteSheetRemote::newFromTitle($title);
			if ($spriteSheetRemote === false) {
				$success = false;
			} else {
				$success = true;
				$spriteSheet = $spriteSheetRemote;
			}
		}

		return ($success ? $spriteSheet : false);
	}

	/**
	 * Load from the database.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function load() {
		if (!$this->isLoaded) {
			switch ($this->newFrom) {
				case 'id':
					$where = [
						'spritesheet_id' => $this->getId()
					];
					break;
				case 'title':
					$where = [
						'title' => $this->title->getDBkey()
					];
					break;
			}

			$result = $this->DB->select(
				['spritesheet'],
				['*'],
				$where,
				__METHOD__
			);

			$row = $result->fetchRow();

			if (is_array($row)) {
				$this->data = $row;

				//Title was not set beforehand.
				if ($this->title === false) {
					$title = Title::newFromText($row['title'], NS_FILE);
					if (!$title) {
						return false;
					} else {
						$this->setTitle($title);
					}
				}
				$this->isLoaded = true;
			}
		}

		return true;
	}

	/**
	 * Save Sprite Sheet to the database.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function save() {
		$success = false;

		//Temporarily store and unset the spritesheet ID.
		$spriteSheetId = $this->data['spritesheet_id'];
		unset($this->data['spritesheet_id']);

		$this->DB->begin();
		if ($spriteSheetId > 0) {
			$result = $this->DB->update(
				'spritesheet',
				$this->data,
				['spritesheet_id' => $spriteSheetId],
				__METHOD__
			);
		} else {
			$result = $this->DB->insert(
				'spritesheet',
				$this->data,
				__METHOD__
			);
			$spriteSheetId = $this->DB->insertId();
		}
		if ($result !== false) {
			$success = true;
		}
		$this->DB->commit();

		$this->data['spritesheet_id'] = $spriteSheetId;

		return $success;
	}

	/**
	 * Set the Sprite Sheet ID
	 *
	 * @access	public
	 * @param	integer	Sprite Sheet ID
	 * @return	boolean	True on success, false if the ID is already set.
	 */
	public function setId($id) {
		if (!$this->data['spritesheet_id']) {
			$this->data['spritesheet_id'] = intval($id);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return the database identification number for this Sprite Sheet.
	 *
	 * @access	public
	 * @return	integer	Sprite Sheet ID
	 */
	public function getId() {
		return intval($this->data['spritesheet_id']);
	}

	/**
	 * Return if this sprite sheet exists.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function exists() {
		return $this->data['spritesheet_id'] > 0;
	}

	/**
	 * Set the Title object.
	 *
	 * @access	public
	 * @param	object	Title
	 * @return	void
	 */
	public function setTitle(Title $title) {
		$this->title = $title;

		$this->data['title'] = $this->title->getDBkey();
	}

	/**
	 * Return the current Tltle object.
	 *
	 * @access	public
	 * @return	object	Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set the number of columns.
	 *
	 * @access	public
	 * @param	integer	Columns
	 * @return	void
	 */
	public function setColumns($columns) {
		$this->data['columns'] = abs(intval($columns));
	}

	/**
	 * Return the number of columns.
	 *
	 * @access	public
	 * @return	integer	Columns
	 */
	public function getColumns() {
		return intval($this->data['columns']);
	}

	/**
	 * Set the number of rows.
	 *
	 * @access	public
	 * @param	integer	Rows
	 * @return	void
	 */
	public function setRows($rows) {
		$this->data['rows'] = abs(intval($rows));
	}

	/**
	 * Return the number of rows.
	 *
	 * @access	public
	 * @return	integer	Rows
	 */
	public function getRows() {
		return intval($this->data['rows']);
	}

	/**
	 * Set the sprite inset.
	 *
	 * @access	public
	 * @param	integer	Inset
	 * @return	void
	 */
	public function setInset($inset) {
		$this->data['inset'] = abs(intval($inset));
	}

	/**
	 * Return the sprite inset.
	 *
	 * @access	public
	 * @return	integer	Inset
	 */
	public function getInset() {
		return intval($this->data['inset']);
	}

	/**
	 * Returns false if the column or row are out of bounds.
	 *
	 * @access	public
	 * @param	integer	Column
	 * @param	integer	Row
	 * @return	boolean	Valid
	 */
	public function validateSpriteCoordindates($column, $row) {
		$column++;
		$row++;

		if ($column > $this->getColumns() || $column < 0) {
			return false;
		}
		if ($row > $this->getRows() || $row < 0) {
			return false;
		}
		return true;
	}

	/**
	 * Return sprite at coordinate position.
	 *
	 * @access	public
	 * @param	integer	Column
	 * @param	integer	Row
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	mixed	HTML or false on error.
	 */
	public function getSpriteAtCoordinates($column, $row, $thumbWidth = null) {
		$file = wfFindFile($this->getTitle());

		if (is_object($file) && $file->exists()) {
			if ($thumbWidth > 0) {
				$file = $file->transform(['width' => $thumbWidth, 'height' => $file->getHeight()]);
			}

			$spriteWidth = ($file->getWidth() / $this->getColumns());
			$spriteHeight = ($file->getHeight() / $this->getRows());

			$spriteX = ($spriteWidth * $column) + $this->getInset();
			$spriteY = ($spriteHeight * $row) + $this->getInset();

			$spriteWidth = $spriteWidth - ($this->getInset() * 2);
			$spriteHeight = $spriteHeight - ($this->getInset() * 2);

			return "<div class='sprite' style='width: {$spriteWidth}px; height: {$spriteHeight}px; overflow: hidden; position: relative;'><img src='".$file->getUrl()."' style='position: absolute; left: -{$spriteX}px; top: -{$spriteY}px;'/></div>";
		}
		return false;
	}

	/**
	 * Get the HTML representation of a named sprite.
	 *
	 * @access	public
	 * @param	string	Sprite Name
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	mixed	HTML or false on error.
	 */
	public function getSpriteFromName($name, $thumbWidth = null) {
		$spriteName = $this->getSpriteName($name);

		if ($spriteName->exists()) {
			$values = $spriteName->getValues();
			return $this->getSpriteAtCoordinates($values['xPos'], $values['yPos'], $thumbWidth);
		}
		return false;
	}

	/**
	 * Get a new SpriteName class and cache it as needed.
	 *
	 * @access	public
	 * @param	string	Name
	 * @return	object	SpriteName
	 */
	public function getSpriteName($name) {
		if (array_key_exists($name, $this->spriteNameCache)) {
			$spriteName = $this->spriteNameCache[$name];
		} else {
			$spriteName = SpriteName::newFromName($name, $this);
			if ($spriteName->exists()) {
				$this->spriteNameCache[$name] = $spriteName;
			}
		}
		return $spriteName;
	}

	/**
	 * Convenience function to get to getSpriteName().
	 *
	 * @access	public
	 * @param	string	Name
	 * @return	object	SpriteName
	 */
	public function getSliceName($name) {
		return $this->getSpriteName($name);
	}

	/**
	 * Function Documentation
	 *
	 * @access	public
	 * @return	void
	 */
	public function getAllSpriteNames() {
		$result = $this->DB->select(
			['spritename'],
			['*'],
			[
				'spritesheet_id'	=> $this->getId(),
			],
			__METHOD__
		);

		while ($row = $result->fetchRow()) {
			$spriteName = SpriteName::newFromRow($row, $this);
			if ($spriteName->exists()) {
				$this->spriteNameCache[$spriteName->getName()] = $spriteName;
			}
		}
		return $this->spriteNameCache;
	}

	/**
	 * Returns false if the slice percentages are out of bounds.
	 *
	 * @access	public
	 * @param	integer	X coordinate, percentage
	 * @param	integer	Y coordinate, percentage
	 * @param	integer	Width, percentage
	 * @param	integer	Height, percentage
	 * @return	boolean	Valid
	 */
	public function validateSlicePercentages($xPercent, $yPercent, $widthPrecent, $heightPercent) {
		$position = [
			$xPercent,
			$yPercent
		];

		foreach ($position as $value) {
			if ($value >= 100 || $value < 0) {
				return false;
			}
		}

		$dimensions = [
			$widthPrecent,
			$heightPercent
		];

		foreach ($dimensions as $value) {
			if ($value > 100 || $value <= 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Return slice from input.
	 *
	 * @access	public
	 * @param	integer	X coordinate, percentage
	 * @param	integer	Y coordinate, percentage
	 * @param	integer	Width, percentage
	 * @param	integer	Height, percentage
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	mixed	HTML or false on error.
	 */
	public function getSlice($xPercent, $yPercent, $widthPrecent, $heightPercent, $thumbWidth = null) {
		$file = wfFindFile($this->getTitle());

		if (is_object($file) && $file->exists()) {
			if ($thumbWidth > 0) {
				$file = $file->transform(['width' => $thumbWidth, 'height' => $file->getHeight()]);
			}

			$sliceX = $file->getWidth() * ($xPercent / 100);
			$sliceY = $file->getHeight() * ($yPercent / 100);

			$sliceWidth = $file->getWidth() * ($widthPrecent / 100);
			$sliceHeight = $file->getHeight() * ($heightPercent / 100);

			return "<div class='sprite' style='width: {$sliceWidth}px; height: {$sliceHeight}px; overflow: hidden; position: relative;'><img src='".$file->getUrl()."' style='position: absolute; left: -{$sliceX}px; top: -{$sliceY}px;'/></div>";
		}
		return false;
	}

	/**
	 * Get the HTML representation of a named slice.
	 *
	 * @access	public
	 * @param	string	Slice Name
	 * @param	integer	[Optional] Thumbnail Width
	 * @return	mixed	HTML or false on error.
	 */
	public function getSliceFromName($name, $thumbWidth = null) {
		$sliceName = $this->getSliceName($name);

		if ($sliceName->exists()) {
			$values = $sliceName->getValues();
			return $this->getSlice($values['xPercent'], $values['yPercent'], $values['widthPercent'], $values['heightPercent'], $thumbWidth);
		}
		return false;
	}

	/**
	 * Return if this is a local SpriteSheet.
	 *
	 * @access	public
	 * @return	boolean	True
	 */
	public function isLocal() {
		return true;
	}
}

class SpriteSheetRemote extends SpriteSheet {
	/**
	 * Last API Error Message
	 *
	 * @var		string
	 */
	private $lastApiErrorMessage = false;

	/**
	 * Create a new instance of this class from a Title object.
	 *
	 * @access	public
	 * @param	object	Title
	 * @return	mixed	SpriteSheet or false on error.
	 */
	static public function newFromTitle(Title $title) {
		if ($title->getNamespace() != NS_FILE || !$title->getDBkey()) {
			return false;
		}

		$spriteSheet = new self();
		$spriteSheet->setTitle($title);

		$spriteSheet->newFrom = 'remote';

		$success = $spriteSheet->load();

		return ($success ? $spriteSheet : false);
	}

	/**
	 * Load from the remote API.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function load() {
		if (!$this->isLoaded) {
			$image = wfFindFile($this->getTitle());

			if ($image !== false && $image->exists() && !$image->isLocal()) {
				$query = [
					'action'	=> 'spritesheet',
					'do'		=> 'getSpriteSheet',
					'title'		=> $this->getTitle()->getDBkey(), //DO NOT MOVE THIS TO THE BOTTOM.  NEVER.  Mediawiki has a dumb as fuck bug called "class IEUrlExtension" which will block all requests if the file name is at the end of the parameter list.
					'format'	=> 'json'
				];

				$data = $image->getRepo()->httpGetCached('SpriteSheet', $query);

				if ($data) {
					$spriteData = FormatJson::decode($data, true);
					var_dump($spriteData);
				}

				$this->isLoaded = true;
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * Return the last error message from the remote API if produced.
	 *
	 * @access	public
	 * @return	mixed	String error message or false if none has been set.
	 */
	public function getLastApiErrorMessage() {
		return $this->lastApiErrorMessage;
	}

	/**
	 * Dummy function to prevent attempts to save the remote SpriteSheet locally.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function save() {
		return true;
	}

	/**
	 * Return if this is a local SpriteSheet.
	 *
	 * @access	public
	 * @return	boolean	False
	 */
	public function isLocal() {
		return false;
	}
}