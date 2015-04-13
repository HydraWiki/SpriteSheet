<?php
/**
 * SpriteSheet
 * SpriteName Log Formatter
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class SpriteNameLogFormatter extends LogFormatter {
	/**
	 * Handle custom log parameters for SpriteName class.
	 *
	 * @access	public
	 * @return	array	Extract and parsed parameters.
	 */
	protected function getMessageParameters() {
		$parameters = parent::getMessageParameters();

		$title = $this->entry->getTarget();
		$type = $this->entry->getSubtype();
		$spriteSheet = SpriteSheet::newFromTitle($title, true);

		if ($spriteSheet !== false) {
			if (!empty($parameters[3])) {
				$spriteName = $spriteSheet->getSpriteName($parameters[3]);

				if ($spriteName != false && $parameters[4] > 0) {
					$links = $spriteName->getRevisionLinks($parameters[4]);
					$parameters[4] = ['raw' => implode(" | ", $links)];
				}
			}
		}

		return $parameters;
	}
}
