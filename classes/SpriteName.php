<?php
/**
 * SpriteSheet
 * SpriteName Class
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class SpriteName {
	/**
	 * Title Object
	 *
	 * @var		object
	 */
	private $DB = false;

	/**
	 * SpriteSheet Object
	 *
	 * @var		object
	 */
	private $spritesheet = false;

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
	 * Main Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct() {
		$this->DB = wfGetDB(DB_MASTER);
	}

	/**
	 * Create a new instance of this class from a Sprite Name database identification number.
	 *
	 * @access	public
	 * @param	integer	SpriteName database identification number.
	 * @return	mixed	SpriteName object or false on error.
	 */
	static public function newFromId($id) {
		if ($id < 1) {
			return false;
		}

		$spriteName = new SpriteName();
		$spriteName->setId(intval($id));

		$spriteName->newFrom = 'id';

		$success = $spriteName->load();

		return ($success ? $spriteName : false);
	}

	/**
	 * Create a new instance of this class from a sprite name.
	 *
	 * @access	public
	 * @param	string	Sprite Name
	 * @return	mixed	SpriteName object or false on error.
	 */
	static public function newFromName($name) {
		$spriteName = new SpriteName();
		$spriteName->setName($name);

		$spriteName->newFrom = 'name';

		$success = $spriteName->load();

		return ($success ? $spriteName : false);
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
						'spritename_id' => $this->getId()
					];
					break;
				case 'name':
					$where = [
						'name' => $this->getName()
					];
					break;
			}

			$result = $this->DB->select(
				['spritename'],
				['*'],
				$where,
				__METHOD__
			);

			$row = $result->fetchRow();

			if (is_array($row)) {
				$this->data = $row;

				if ($this->spritesheet === false) {
					$this->spritesheet = SpriteSheet::newFromID($row['spritesheet_id']);
					if (!$this->spritesheet) {
						return false;
					}
				}
			}
		}
		return true;
	}

	/**
	 * Save Sprite Name to the database.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function save() {
		$success = false;

		$spriteNameId = $this->getId();

		$save = [
			'spritesheet_id'	=> $this->getSpriteSheet()->getId(),
			'name'				=> $this->getName(),
			'values'			=> $this->getValues(false)
		];

		$this->DB->begin();
		if ($spriteNameId > 0) {
			$result = $this->DB->update(
				'spritename',
				$save,
				['spritename_id' => $spriteNameId],
				__METHOD__
			);
		} else {
			$result = $this->DB->insert(
				'spritename',
				$save,
				__METHOD__
			);
			$spriteNameId = $this->DB->insertId();
		}
		if ($result !== false) {
			$success = true;
		}
		$this->DB->commit();

		return $success;
	}

	/**
	 * Set the Sprite Name ID
	 *
	 * @access	public
	 * @param	integer	Sprite Name ID
	 * @return	boolean	True on success, false if the ID is already set.
	 */
	public function setId($id) {
		if (!$this->data['spritename_id']) {
			$this->data['spritename_id'] = intval($id);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Return the database identification number for this Sprite Name.
	 *
	 * @access	public
	 * @return	integer	Sprite Name ID
	 */
	public function getId() {
		return intval($this->data['spritesheet_id']);
	}

	/**
	 * Set the Sprite Name
	 *
	 * @access	public
	 * @param	string	Sprite Name
	 * @return	void
	 */
	public function setName($name) {
		$this->data['name'] = substr($name, 0, 255);
	}

	/**
	 * Return the sprite name.
	 *
	 * @access	public
	 * @return	string	Sprite Name
	 */
	public function getName() {
		return $this->data['name'];
	}

	/**
	 * Set the values.
	 *
	 * @access	public
	 * @param	array	Values
	 * @return	void
	 */
	public function setValues(array $values) {
		$this->data['values'] = @json_encode($values);
	}

	/**
	 * Return the saved values for this named sprite/slice.
	 *
	 * @access	public
	 * @param	boolean	[Optional] Return as an array by default or as the raw JSON string.
	 * @return	array	Values
	 */
	public function getValues($asArray = true) {
		return ($asArray ? @json_decode($this->data['values'], true) : $this->data['values']);
	}

	/**
	 * Set the SpriteSheet
	 *
	 * @access	public
	 * @param	object	SpriteSheet
	 * @return	void
	 */
	public function setSpriteSheet(SpriteSheet $spriteSheet) {
		$this->spriteSheet = $this->spriteSheet;
	}

	/**
	 * Return the SpriteSheet object associated with this sprite name.
	 *
	 * @access	public
	 * @return	object	SpriteSheet
	 */
	public function getSpriteSheet() {
		return $this->spriteSheet;
	}
}
