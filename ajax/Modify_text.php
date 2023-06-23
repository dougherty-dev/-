<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

isset($_POST['type'], $_POST['table'], $_POST['value']) or exit;

class Modify_text {
	use Common, Ajax_common;

	private string $table = '', $type = '';

	public function __construct() {
		$this->init();
		$this->table = (string) filter_var($_POST['table'], FILTER_SANITIZE_SPECIAL_CHARS);
		$this->type = (string) filter_var($_POST['type'], FILTER_SANITIZE_SPECIAL_CHARS);

		$this->check_post_id() and match ($this->type) {
			'title', 'name' => $this->modify_text(),
			'slug' => $this->modify_slug(),
			'description' => $this->modify_description(),
			default => NULL
		};
	}

	private function modify_text(): void {
		$this->db_modify_text($this->table, $this->type, $text = trim_title($_POST['value']));
		echo $text;
	}

	private function modify_slug(): void {
		$slug = trim_slug($_POST['value']);

		match ($this->table) {
			'images' => $this->rename_image($slug),
			'themes' => $this->rename_theme($slug),
			default => NULL
		};

		$this->db_modify_text($this->table, $this->type, $slug);
		echo $slug;
	}

	private function modify_description(): void {
		$description = (string) encode_text($_POST['value']);
		$this->db_modify_text($this->table, $this->type, $description);
		echo decode_text((string) $description);
	}

	private function rename_image(string $new_slug): void {
		$this->get_image_data();
		foreach (IMAGE_TYPES as $image_type) {
			$old_path = imagepath($this->dir, $this->id, $this->slug, $image_type, $this->ext, USERIMAGES);
			$new_path = imagepath($this->dir, $this->id, $new_slug, $image_type, $this->ext, USERIMAGES);
			is_real_file($old_path) and rename($old_path, $new_path);
		}

		if ($this->attachment) {
			$old_path = imagepath($this->dir, $this->id, $this->slug, 'a', $this->attachment, USERIMAGES);
			$new_path = imagepath($this->dir, $this->id, $new_slug, 'a', $this->attachment, USERIMAGES);
			is_real_file($old_path) and rename($old_path, $new_path);
		}
	}

	private function rename_theme(string $new_slug): void {
		$stmt = $this->db->instance->prepare("SELECT `slug` FROM `themes` WHERE `id`=:id LIMIT 1");
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
		$old_slug = ($stmt !== FALSE && is_array($r = $stmt->fetch(PDO::FETCH_ASSOC))) ? $r['slug'] : '';

		$old_slug !== '' and rename(USERTHEMES . "/{$this->post_id}-$old_slug", USERTHEMES . "/{$this->post_id}-$new_slug");
	}
}

new Modify_text;
