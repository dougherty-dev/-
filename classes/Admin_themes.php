<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Admin_themes {
	use Admin_common;

	public function __construct() {
		$this->secure();
		$this->show_admin();
	}

	private function show_admin_folio(): string {
		$zip = extension_loaded('zip');

		$themes = $selected_theme = '';
		$selected = ($this->current_theme_id === 0) ? ' selected="selected"' : '';
		$selected_theme = <<< EOT
					<option value="0"$selected>{$this->echo(DEFAULT_THEME)}</option>

EOT;

		foreach ($this->theme_id as $k => $theme_id) {
			$selected = ($this->current_theme_id === $theme_id) ? ' selected="selected"' : '';
			$selected_theme .= <<< EOT
					<option value="$theme_id"$selected>{$this->theme_name[$k]}</option>

EOT;

			$themes .= <<< EOT
					<tr class="edit-themes" data-id="$theme_id" data-slug="{$this->theme_slug[$k]}">
						<td>
							<input type="text" size="25" maxlength="25" name="name" placeholder="Name" value="{$this->theme_name[$k]}"><br>
							<input type="text" size="25" maxlength="25" name="slug" placeholder="Slug [a-z0-9\-]" value="{$this->theme_slug[$k]}"><br>
						</td>
						<td>
							<span class="delete">❌</span><br>
							<span class="edit-theme">🛠️</span><br>
						</td>
					</tr>

EOT;
		}

		return <<< EOT
			<div class="grid-folio">
				<h1>Themes</h1>
				<div>
					<p>Zip: {$this->echo($zip ? '✅' : '⚠️')}</p>
				</div>
{$this->echo($zip ? $this->get_droptheme() : '')}
				<p><select id="select_theme">
$selected_theme				</select></p>
				<table id="current-themes">
$themes				</table>
				<div id="edit-theme-area"></div>
			</div> <!-- grid-folio -->
EOT;
	}

	private function get_droptheme(): string {
		return <<< EOT
				<form id="droptheme" class="dropzone">
					<div class="dz-message" data-dz-message>
						<p class="emoji">⬇️</p>
						<p>zip</p>
					</div>
				</form>
EOT;
	}

}
