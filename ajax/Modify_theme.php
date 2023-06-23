<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Modify_theme {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and match(TRUE) {
			isset($_POST['select_theme']) => $this->select_theme(),
			isset($_POST['edit_theme']) => $this->edit_theme(),
			isset($_POST['update_theme']) => $this->update_theme(),
			default => NULL
		};
	}

	private function select_theme(): void {
		$select_theme = (int) filter_var($_POST['select_theme'], FILTER_VALIDATE_INT);
		$this->db->save_preference('current_theme_id', "$select_theme");
	}

	private function edit_theme(): void {
		isset($_POST['post_slug']) and $post_slug = (string) filter_var($_POST['post_slug'], FILTER_SANITIZE_SPECIAL_CHARS) or exit;
		$post_slug !== '' or exit;
		$themefile = USERTHEMES . "/{$this->post_id}-$post_slug" . '/' . THEME_FILENAME;
		is_real_file($themefile) and $content = file_get_contents($themefile) or exit;
		echo <<< EOT
				<div id="edit-theme-area" class="edit-modal">
					<textarea id="theme-content" placeholder="(HTML)">$content</textarea><br>
					<button id="update-theme" data-id="{$this->post_id}" data-slug="$post_slug">✅</button>
				</div>
EOT;
	}

	private function update_theme(): void {
		isset($_POST['content']) and $content = (string) $_POST['content'] or exit;
		$content !== '' or exit;

		isset($_POST['post_slug']) and $post_slug = (string) filter_var($_POST['post_slug'], FILTER_SANITIZE_SPECIAL_CHARS) or exit;
		$post_slug !== '' or exit;

		$themefile = USERTHEMES . "/{$this->post_id}-$post_slug" . '/' . THEME_FILENAME;
		file_put_contents($themefile, $content);

		$stmt = $this->db->instance->prepare('UPDATE `themes` SET `time`=:time WHERE `id`=:id');
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->bindValue(':time', time(), PDO::PARAM_INT);
		$stmt->execute();
	}
}

new Modify_theme;
