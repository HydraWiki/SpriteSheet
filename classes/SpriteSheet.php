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
	 * Main Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct() {
		$this->DB = wfGetDB(DB_MASTER);
	}

	/**
	 * Create a new instance of this class from a Title object.
	 *
	 * @access	public
	 * @param	object	Title
	 * @return	mixed	SpriteSheet or false on error.
	 */
	static public function newFromTitle(Title $title) {
		if (!$title->getArticleID()) {
			return false;
		}

		$spriteSheet = new SpriteSheet();
		$spriteSheet->setTitle($title);

		$spriteSheet->newFrom = 'title';

		$spriteSheet->load();

		return $spriteSheet;
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
						'sid' => $this->getId()
					];
					break;
				case 'title':
					$where = [
						'page_id' => $this->title->getArticleID()
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
			}
		}
		return true;
	}

	/**
	 * Return the database identification number for this Sprite Sheet.
	 *
	 * @access	public
	 * @return	integer	Sprite Sheet ID
	 */
	public function getId() {
		return intval($this->data['sid']);
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

		$this->data['page_id'] = $this->title->getArticleID();
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
}
