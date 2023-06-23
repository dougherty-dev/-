<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Modify_page {
	use Common, Upload_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and match (TRUE) {
			isset($_POST['edit_page']) => $this->edit_page(),
			isset($_POST['update_page']) => $this->update_page(),
			default => NULL
		};
	}

	private function edit_page(): void {
		$content = '';
		$stmt = $this->db->instance->prepare('SELECT `content` FROM `pages` WHERE `id`=:id');
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$content = decode_text((string) $r['content']);
		}

		echo <<< EOT
				<div id="edit-page-area" class="edit-modal">
					<textarea id="page-content" placeholder="(HTML)">$content</textarea><br>
					<button id="update-page" data-id="{$this->post_id}">✅</button>
				</div>
EOT;
	}

	private function update_page(): void {
		$content = isset($_POST['content']) ? (string) $_POST['content'] : '';
		if ($content === '') return;

		$this->db_modify_text('pages', 'content', encode_text($content));
	}

}

new Modify_page;
