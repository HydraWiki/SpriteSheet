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
	 * Mediawiki Database Object
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
	 * Where this object was loaded from.
	 *
	 * @var		string
	 */
	public $newFrom = null;

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
	public function __construct(SpriteSheet $spriteSheet) {
		$this->DB = wfGetDB(DB_MASTER);

		if (!$spriteSheet->exists()) {
			throw new MWException(__METHOD__." was called with an invalid SpriteSheet.");
		}

		$this->spriteSheet = $spriteSheet;
	}

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @param	string	Sprite/Slice Name
	 * @param	object	Valid SpriteSheet that exists.
	 * @return	void
	 */
	static public function newFromName($name, SpriteSheet $spriteSheet) {
		$spriteName = new self($spriteSheet);

		$spriteName->newFrom = 'name';

		$spriteName->setName($name);

		$spriteName->load();

		return $spriteName;
	}

	/**
	 * Load a new SpriteName object from a database row.
	 *
	 * @access	public
	 * @param	array	Database Row
	 * @param	object	Valid SpriteSheet that exists.
	 * @return	mixed	SpriteName or false on error.
	 */
	static public function newFromId($id, SpriteSheet $spriteSheet) {
		$spriteName = new self($spriteSheet);

		$spriteName->newFrom = 'id';

		$spriteName->setId($id);

		return $spriteName;
	}

	/**
	 * Load a new SpriteName object from a database row.
	 *
	 * @access	public
	 * @param	array	Database Row
	 * @param	object	Valid SpriteSheet that exists.
	 * @return	mixed	SpriteName or false on error.
	 */
	static public function newFromRow($row, SpriteSheet $spriteSheet) {
		$spriteName = new self($spriteSheet);

		$spriteName->newFrom = 'row';

		$spriteName->load($row);

		return $spriteName;
	}

	/**
	 * Load from the database.
	 *
	 * @access	public
	 * @param	array	[Optional] Database row to load from.
	 * @return	void
	 */
	public function load($row = null) {
		if (!$this->isLoaded) {
			switch ($this->newFrom) {
				case 'name':
					$result = $this->DB->select(
						['spritename'],
						['*'],
						[
							'spritesheet_id'	=> $this->getSpriteSheet()->getId(),
							'name' 				=> $this->getName()
						],
						__METHOD__
					);

					$row = $result->fetchRow();
					break;
				case 'id':
					$result = $this->DB->select(
						['spritename'],
						['*'],
						[
							'spritename_id'		=> $this->getId()
						],
						__METHOD__
					);

					$row = $result->fetchRow();
					break;
			}

			if (is_array($row)) {
				$this->data = $row;
				$this->isLoaded = true;
			}
		}
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
			'`values`'			=> $this->getValues(false),
			'`edited`'			=> time()
		];

		$this->DB->begin();
		if ($spriteNameId > 0) {
			if (!$this->saveOldVersion()) {
				$this->DB->rollback();
				throw new MWException(__METHOD__.': Could not save an old version while attempting to save.');
				return false;
			}

			//Do the update.
			$result = $this->DB->update(
				'spritename',
				$save,
				['spritename_id' => $spriteNameId],
				__METHOD__
			);
		} else {
			//Do the insert.
			$result = $this->DB->insert(
				'spritename',
				$save,
				__METHOD__
			);
			$spriteNameId = $this->DB->insertId();
		}

		if ($result !== false) {
			global $wgUser;

			$this->DB->commit();

			//Enforce sanity on data.
			$this->data['spritename_id']	= $spriteNameId;
			$this->data['edited']			= $save['edited'];

			$this->logChanges();

			$success = true;
		} else {
			$this->DB->rollback();
		}

		return $success;
	}

	/**
	 * Delete this Sprite Name from the database.
	 *
	 * @access	public
	 * @return	boolean	Success
	 */
	public function delete() {
		$success = false;

		$spriteNameId = $this->getId();

		$this->DB->begin();
		if ($spriteNameId > 0) {
			$this->data['deleted'] = 1;

			if (!$this->saveOldVersion()) {
				$this->DB->rollback();
				throw new MWException(__METHOD__.': Could not save an old version while attempting to delete.');
				return false;
			}

			//Do the delete.
			$result = $this->DB->update(
				'spritename',
				['deleted' => 1],
				['spritename_id' => $spriteNameId],
				__METHOD__
			);
		}

		if ($result !== false) {
			global $wgUser;

			$this->DB->commit();

			//Enforce sanity on data.
			$this->data['spritename_id']	= $spriteNameId;
			$this->data['edited']			= $save['edited'];

			$this->logChanges();

			$success = true;
		} else {
			$this->data['deleted'] = 0;
			$this->DB->rollback();
		}

		return $success;
	}

	/**
	 * Save an old version of this sprite name.
	 *
	 * @access	private
	 * @return	boolean	Success
	 */
	private function saveOldVersion() {
		$success = false;

		$oldResult = $this->DB->select(
			['spritename'],
			['*'],
			['spritename_id' => $this->getId()],
			__METHOD__
		);
		$oldRow = $oldResult->fetchRow();
		if (is_array($oldRow)) {
			//Sorry.
			$oldValues = $oldRow['values'];
			unset($oldRow['values']);
			$oldRow['`values`'] = $oldValues;

			$result = $this->DB->insert(
				'spritename_old',
				$oldRow,
				__METHOD__
			);
		}

		if ($result !== false) {
			$success = true;
		}

		return $success;
	}

	/**
	 * Log changes in the logging table.
	 *
	 * @access	private
	 * @return	void
	 */
	private function logChanges() {
		$extra = [$this->getName()];
		$oldSpriteName = $this->getPreviousRevision();
		$type = $this->getType();

		if ($oldSpriteName instanceOf SpriteName && $oldSpriteName->getOldId() !== false) {
			$extra['spritename_old_id'] = $oldSpriteName->getOldId();
			if ($oldSpriteName->getName() != $this->getName()) {
				$type .= "-rename";
				$extra['old_name'] = $oldSpriteName->getName();
				$extra['new_name'] = $this->getName();
			}
		}

		if ($this->isDeleted()) {
			$type = $this->getType().'-deleted';
		}

		$log = new LogPage('sprite');
		$log->addEntry(
			$type,
			$this->getSpriteSheet()->getTitle(),
			null,
			$extra,
			$wgUser
		);
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
	 * Set the database identification number for this Sprite Name.
	 *
	 * @access	public
	 * @param	integer	Sprite Name ID
	 * @return	void
	 */
	public function setId($id) {
		$this->data['spritename_id'] = intval($id);
	}

	/**
	 * Return if this sprite name exists.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function exists() {
		return ($this->data['spritename_id'] > 0 && !$this->isDeleted() && $this->isLoaded);
	}

	/**
	 * Set the Sprite Name
	 *
	 * @access	public
	 * @param	string	Sprite Name
	 * @return	void
	 */
	public function setName($name) {
		$this->data['name'] = substr(trim($name), 0, 255);
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

	/**
	 * Is thie sprite name deleted?
	 *
	 * @access	public
	 * @return	boolean	Deleted
	 */
	public function isDeleted() {
		return (bool) $this->data['deleted'];
	}

	/**
	 * Is this an old revision?
	 *
	 * @access	public
	 * @return	boolean	Is Old Revision
	 */
	public function isOldRevision() {
		return (bool) $this->data['spritename_old_id'];
	}

	/**
	 * Get the previous revision for this spritename.
	 *
	 * @access	public
	 * @return	mixed	SpriteName or false for no previous revision.
	 */
	public function getPreviousRevision() {
		$where['spritename_id'] = $this->getId();
		if ($this->isOldRevision()) {
			$where[] = "spritename_old_id < ".intval($this->data['spritename_old_id']);
		}

		$oldResult = $this->DB->select(
			['spritename_old'],
			['*'],
			$where,
			__METHOD__,
			[
				'ORDER BY'	=> 'spritename_old_id DESC'
			]
		);

		$oldRow = $oldResult->fetchRow();

		$spriteName = false;
		if (is_array($oldRow)) {
			$spriteName = SpriteName::newFromRow($oldRow, $this->spriteSheet);
		}

		return $spriteName;
	}

	/**
	 * Get a previous revision for this spritename by its old ID.
	 *
	 * @access	public
	 * @return	mixed	SpriteName or false for no previous revision.
	 */
	public function getRevisionByOldId($oldId) {
		$oldResult = $this->DB->select(
			['spritename_old'],
			['*'],
			[
				'spritename_old_id'	=> $oldId,
				'spritename_id'		=> $this->getId()
			],
			__METHOD__
		);

		$oldRow = $oldResult->fetchRow();

		$spriteName = false;
		if (is_array($oldRow)) {
			$spriteName = SpriteName::newFromRow($oldRow, $this->spriteSheet);
		}

		return $spriteName;
	}

	/**
	 * Return the old revision ID if this is an old revision.
	 *
	 * @access	public
	 * @return	mixed	Old Revision ID or false if this is the current revision.
	 */
	public function getOldId() {
		if ($this->isOldRevision()) {
			return $this->data['spritename_old_id'];
		}
		return false;
	}

	/**
	 * Return the old ID that comes after the supplied old ID.
	 *
	 * @access	public
	 * @param	integer	Old ID
	 * @return	mixed	Next old ID or false if it is the most current.
	 */
	static public function getNextOldId($oldId) {
		$DB = wfGetDB(DB_MASTER);

		$oldResult = $DB->select(
			['spritename_old'],
			['*'],
			["spritename_old_id > ".intval($oldId)],
			__METHOD__,
			[
				'ORDER BY'	=> 'spritename_old_id ASC'
			]
		);

		$oldRow = $oldResult->fetchRow();
		if (is_array($oldRow)) {
			return intval($oldRow['spritename_old_id']);
		}
		return false;
	}

	/**
	 * Return a set of revision links(diff, revert) for the change log.
	 *
	 * @access	public
	 * @param	integer	[Optional] The previous ID to use.  This will automatically populate if not provided.
	 * @return	mixed	Array of links for performing actions against revisions.  Returns false if none are created.
	 */
	public function getRevisionLinks($previousId = false) {
		global $wgUser;

		$links = false;
		if ($wgUser->isAllowed('spritesheet_rollback')) {
			if ($previousId === false) {
				$previousRevision = $this->getPreviousRevision();
				$arguments['spritePreviousId'] = $previousRevision->getId();
			} else {
				$arguments['spritePreviousId'] = intval($previousId);
			}

			$links['rollback'] = Linker::link($this->getSpriteSheet()->getTitle(), wfMessage('rollbacklink')->escaped(), [], array_merge($arguments, ['spriteAction' => 'rollback']));
		}

		return $links;
	}
}
