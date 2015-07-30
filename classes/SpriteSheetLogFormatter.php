<?php
/**
 * SpriteSheet
 * SpriteSheet Log Formatter
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class SpriteSheetLogFormatter extends LogFormatter {
	/**
	 * Sheet created or edited string holder.
	 *
	 * @var		string
	 */
	private $sheetCreatedEdited = "created";

	/**
	 * Handle custom log parameters for SpriteSheet class.
	 *
	 * @access	public
	 * @return	array	Extract and parsed parameters.
	 */
	protected function getMessageParameters() {
		$parameters = parent::getMessageParameters();

		$title = $this->entry->getTarget();
		$spriteSheet = SpriteSheet::newFromTitle($title, true);

		$this->sheetCreatedEdited = "created";
		if ($spriteSheet !== false) {
			$lastRevision = $spriteSheet->getPreviousRevision();
			//Handle old revision ID.
			if ($parameters[3] > 0) {
				$this->sheetCreatedEdited = "edited";
				$links = $spriteSheet->getRevisionLinks($parameters[3]);
				$parameters[3] = ['raw' => implode(" | ", $links)];
			}
		}

		return $parameters;
	}

	/**
	 * Override to change the order in which functions are called.
	 *
	 * @access	protected
	 * @return	mixed	Message object or string of escaped HTML.
	 */
	protected function getActionMessage() {
		$parameters = $this->getMessageParameters();
		$messageKey = $this->getMessageKey();
		$message = $this->msg($messageKey);
		$message->params($parameters);
		var_dump($messageKey);
		return $message;
	}

	/**
	 * Override this so that "created" or "edited" can be appended.
	 *
	 * @access	protected
	 * @return	string	Message Key
	 */
	protected function getMessageKey() {
		$type = $this->entry->getType();
		$subtype = $this->entry->getSubtype();

		return "logentry-{$type}-{$subtype}-{$this->sheetCreatedEdited}";
	}
}
