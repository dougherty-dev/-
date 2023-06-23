<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Delete_item {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and $this->delete_item();
	}

	private function delete_item(): void {
		$table = (string) filter_var($_POST['table'], FILTER_SANITIZE_SPECIAL_CHARS);
		$table === '' and exit;
		match ($table) {
			'images' => $this->remove_image(),
			'themes' => $this->remove_theme(),
			default => NULL
		};

		$stmt = $this->db->instance->prepare("DELETE FROM `$table` WHERE `id`=:id");
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
	}

	private function remove_image(): void {
		$this->get_image_data();
		foreach (IMAGE_TYPES as $image_type) {
			$path = imagepath($this->dir, $this->post_id, $this->slug, $image_type, $this->ext, USERIMAGES);
			is_real_file($path) and unlink($path);
		}

		if ($this->attachment) {
			$path = imagepath($this->dir, $this->post_id, $this->slug, 'a', $this->attachment, USERIMAGES);
			is_real_file($path) and unlink($path);
		}
	}

	private function remove_theme(): void {
		$old_slug = $this->get_slug('themes');
		empty_folder(USERTHEMES . "/{$this->post_id}-$old_slug");
	}

	private function get_slug(string $table): string {
		$stmt = $this->db->instance->prepare("SELECT `slug` FROM `$table` WHERE `id`=:id LIMIT 1");
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
		return ($stmt !== FALSE && is_array($r = $stmt->fetch(PDO::FETCH_ASSOC))) ? $r['slug'] : '';
	}
}

new Delete_item;
