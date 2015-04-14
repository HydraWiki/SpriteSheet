<?php
/**
 * SpriteSheet
 * SpriteSheet Editor Template
 *
 * @author		Alexia E. Smith
 * @license		LGPL v3.0
 * @package		SpriteSheet
 * @link		https://github.com/CurseStaff/SpriteSheet
 *
 **/

class TemplateSpriteSheetEditor {
	/**
	 * Sprite Sheet Editor Form
	 *
	 * @access	public
	 * @param	object	ImagePage
	 * @param	object	SpriteSheet
	 * @param	object	[Optional] SpriteSheet - Old Revision
	 * @param	string	[Optional] Log Page Link
	 * @param	string	[Optional] Disabled HTML Bit
	 * @param	string	[Optional] Read Only HTML Bit
	 * @return	string	Built HTML
	 */
	public function spriteSheetForm($imagePage, $spriteSheet, $oldSpriteSheet = false, $logLink = null, $disabled = '', $readOnly = '') {
		$html = "
		<div id='spritesheet_editor' style='display: none;'>
			<form>
				<fieldset id='spritesheet_form'>
					<legend>".wfMessage('sprite_sheet')->escaped()." [{$logLink}]</legend>
					".(!$spriteSheet->isLocal() ? "<pre>".wfMessage('visit_remote_repository_to_edit_sprite_sheet', $imagePage->getDisplayedFile()->getDescriptionUrl())."</pre>" : '');
		if ($oldSpriteSheet instanceOf SpriteSheet) {
			$html .= "
					<fieldset id='old_spritesheet_form'>
						<span class='previous_revision'>".wfMessage('previous_values')->escaped()."</span><br/>
						<label for='old_sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
						<input id='old_sprite_columns' name='old_sprite_columns' type='number' min='0' disabled='disabled' value='".$oldSpriteSheet->getColumns()."'/>

						<label for='old_sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
						<input id='old_sprite_rows' name='old_sprite_rows' type='number' min='0' disabled='disabled' value='".$oldSpriteSheet->getRows()."'/>

						<label for='old_sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
						<input id='old_sprite_inset' name='old_sprite_inset' type='number' min='0' disabled='disabled' value='".$oldSpriteSheet->getInset()."'/>

						<input name='old_spritesheet_id' type='hidden' disabled='disabled' value='".$oldSpriteSheet->getOldId()."'/>
					</fieldset>

					<span class='current_revision'>".wfMessage('current_values')->escaped()."</span><br/>";
		}

		$html .= "
					<label for='sprite_columns'>".wfMessage('sprite_columns')->escaped()."</label>
					<input id='sprite_columns' name='sprite_columns' type='number' min='0'{$readOnly} value='".$spriteSheet->getColumns()."'/>

					<label for='sprite_rows'>".wfMessage('sprite_rows')->escaped()."</label>
					<input id='sprite_rows' name='sprite_rows' type='number' min='0'{$readOnly} value='".$spriteSheet->getRows()."'/>

					<label for='sprite_inset'>".wfMessage('sprite_inset')->escaped()."</label>
					<input id='sprite_inset' name='sprite_inset' type='number' min='0'{$readOnly} value='".$spriteSheet->getInset()."'/>

					<input name='spritesheet_id' type='hidden'{$readOnly} value='".$spriteSheet->getId()."'/>
					<input name='page_title' type='hidden'{$readOnly} value='".htmlentities($spriteSheet->getTitle()->getPrefixedDBkey(), ENT_QUOTES)."'/>
					".($spriteSheet->isLocal() ? "<button id='save_sheet' name='save_sheet'{$disabled} type='button'>".wfMessage('save_sheet')->escaped()."</button>" : '')."

					<pre id='sprite_preview'>".wfMessage('click_grid_for_preview')->escaped()."</pre>";
		if ($spriteSheet->isLocal()) {
			$html .= "
					<div id='named_sprite_add' class='named_sprite_popup'>
						<input id='sprite_name' name='sprite_name'{$readOnly} type='text' value=''/>
						<button id='save_named_sprite' name='save_named_sprite'{$disabled} type='button'>".wfMessage('save_named_sprite')->escaped()."</button>
						<a class='close'>&nbsp;</a>
					</div>";
		} else {
			$html .= "
					<input name='isRemote' type='hidden' value='1'/>
					<input name='remoteApiUrl' type='hidden' value='".$imagePage->getDisplayedFile()->getRepo()->getApiUrl()."'/>";
		}
		$html .= "
				</fieldset>
			</form>
			<button id='show_named_sprites' name='show_named_sprites' type='button'>".wfMessage('show_named_sprites')->escaped()."</button>
			<div id='named_sprites'></div>";
		if ($spriteSheet->isLocal()) {
			$html .= "
			<div id='named_sprite_editor' class='named_sprite_popup'>
				<input id='update_sprite_name' name='update_sprite_name'{$readOnly} type='text' value=''/>
				<button id='update_named_sprite' name='update_named_sprite'{$disabled} type='button'>".wfMessage('update_name')->escaped()."</button>
				<button id='delete_named_sprite' name='delete_named_sprite'{$disabled} type='button'>".wfMessage('delete_name')->escaped()."</button>
				<a class='close'>&nbsp;</a>
			</div>";
		}
		$html .= "
		</div>";

		return $html;
	}
}
