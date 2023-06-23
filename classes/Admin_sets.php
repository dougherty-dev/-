<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Admin_sets {
	use Admin_common;

	public function __construct() {
		$this->secure();
		$this->get_sets();
		$this->reorder_sets();
		$this->get_front_images();
		$this->show_admin();
	}

	private function show_admin_folio(): string {
		$sets = '';
		$n = count($this->set_id) - 1;
		foreach ($this->set_id as $k => $set_id) {
			$image_count = $this->count_images($set_id);
			$delete_span = $image_count ? '<span class="dimmed">🚫</span>' : '<span class="delete">❌</span>';
			$img = $image_count && isset($this->image_filepath[$k]) ? '<img class="thumb" src="' . $this->image_filepath[$k] . '"></td>' : '';
			$sets .= <<< EOT
					<tr class="edit-sets" data-id="$set_id">
						<td class="edit-thumb center">$img</td>
						<td>
							<input type="text" size="25" maxlength="25" name="title" placeholder="Title" value="{$this->set_title[$k]}"><br>
							<input type="text" size="25" maxlength="25" name="slug" placeholder="Slug [a-z0-9\-]" value="{$this->set_slug[$k]}"><br>
							<textarea cols="25" rows="3" name="description" placeholder="Description">{$this->set_description[$k]}</textarea>
						</td>
						<td>
							$delete_span
							<span class="up">⬆️</span>
							<span class="down">⬇️</span>
						</td>
					</tr>

EOT;
		}

		$set_text = <<< EOT
			<div class="grid-folio">
				<h1>Sets</h1>
				<table id="current-sets">
$sets				</table>
				<form id="new-set">
					<table class="new-set">
						<tr class="edit-sets">
							<td>
								<input type="text" size="25" maxlength="25" name="title" id="new_set_title" placeholder="Title"><br>
								<input type="text" size="25" maxlength="25" name="slug" id="new_set_slug" placeholder="Slug"><br>
								<textarea cols="25" rows="3" name="description" placeholder="Description (HTML)"></textarea>
							</td>
							<td><button id="create_new_set">➕</button></td>
						</tr>
					</table>
				</form>
			</div> <!-- grid-folio -->
EOT;
		return $set_text;
	}
}
