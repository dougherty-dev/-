<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Admin_pages {
	use Admin_common;

	public function __construct() {
		$this->secure();
		$this->get_pages();
		$this->show_admin();
	}

	private function show_admin_folio(): string {
		$pages = '';
		foreach ($this->page_id as $k => $page_id) {
			$pages .= <<< EOT
					<tr class="edit-pages" data-id="$page_id" data-page-slug="{$this->page_slug[$k]}">
						<td>
							<input type="text" size="25" maxlength="25" name="title" placeholder="Title" value="{$this->page_title[$k]}"><br>
							<input type="text" size="25" maxlength="25" name="slug" placeholder="Slug [a-z0-9\-]" value="{$this->page_slug[$k]}"><br>
						</td>
						<td>
							<span class="delete">❌</span><br>
							<span class="edit-page">🛠️</span><br>
						</td>
						<td>
							<span class="up">⬆️</span>
							<span class="down">⬇️</span>
						</td>
					</tr>

EOT;
		}

		return <<< EOT
			<div class="grid-folio">
				<h1>Pages</h1>
				<table id="current-pages">
$pages				</table>
				<form id="new-page">
					<table class="new-page">
						<tr class="edit-pages">
							<td>
								<input type="text" size="25" maxlength="25" name="title" id="new_page_title" placeholder="Title"><br>
								<input type="text" size="25" maxlength="25" name="slug" id="new_page_slug" placeholder="Slug"><br>
							</td>
							<td><button id="create_new_page">➕</button></td>
						</tr>
					</table>
				</form>
				<div id="edit-page-area"></div>
			</div> <!-- grid-folio -->
EOT;
	}

}
