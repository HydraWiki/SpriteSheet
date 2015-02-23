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
	private $spriteSheet = false;

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
	 * Valid named sprite types.
	 *
	 * @var		array
	 */
	public $types = [
		'sprite',
		'slice'
	];

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @param	string	Sprite/Slice Name
	 * @param	object	Valid SpriteSheet that exists.
	 * @return	void
	 */
	public function __construct($name, SpriteSheet $spriteSheet) {
		$this->DB = wfGetDB(DB_MASTER);

		if (!$spriteSheet->exists()) {
			throw new MWException(__METHOD__." was called with an invalid SpriteSheet.");
		}

		$this->spriteSheet = $spriteSheet;
		$this->setName($name);

		$this->load();
	}

	/**
	 * Load from the database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function load() {
		if (!$this->isLoaded) {
			$result = $this->DB->select(
				['spritename'],
				['*'],
				[
					'spritesheet_id'	=> $this->spriteSheet->getId(),
					'name' 				=> $this->getName()
				],
				__METHOD__
			);

			$row = $result->fetchRow();

			if (is_array($row)) {
				$this->data = $row;
			}
		}

		$this->isLoaded = true;
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
			'`spritesheet_id`'	=> $this->getSpriteSheet()->getId(),
			'`name`'			=> $this->getName(),
			'`type`'			=> $this->getType(),
			'`values`'			=> $this->getValues(false)
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
	 * Return the database identification number for this Sprite Name.
	 *
	 * @access	public
	 * @return	integer	Sprite Name ID
	 */
	public function getId() {
		return intval($this->data['spritename_id']);
	}

	/**
	 * Return if this sprite name exists.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function exists() {
		return $this->data['spritename_id'] > 0;
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
	 * Is the sprite name valid?
	 *
	 * @access	public
	 * @return	boolean	Valid
	 */
	public function isNameValid() {
		$valid = true;

		if (empty($this->data['name'])) {
			$valid = false;
		}

		if (strlen($this->data['name']) > 255) {
			$valid = false;
		}

		return $valid;
	}

	/**
	 * Set the Sprite Type
	 *
	 * @access	public
	 * @param	string	Sprite Type
	 * @return	boolean	Success
	 */
	public function setType($type) {
		if (!in_array($type, $this->types)) {
			return false;
		}

		$this->data['type'] = $type;

		return true;
	}

	/**
	 * Return the sprite Type.
	 *
	 * @access	public
	 * @return	string	Sprite Type
	 */
	public function getType() {
		return $this->data['type'];
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
	 * Return the SpriteSheet object associated with this sprite name.
	 *
	 * @access	public
	 * @return	object	SpriteSheet
	 */
	public function getSpriteSheet() {
		return $this->spriteSheet;
	}

	/**
	 * Return a parser tag for this named sprite.
	 *
	 * @access	public
	 * @return	string	Parser Tag
	 */
	public function getParserTag() {
		return "{{#".$this->getType().":".$this->getSpriteSheet()->getTitle()->getPrefixedDBkey()."|".$this->getName()."}}";
	}
}
