<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Handle_image {
	use Common;

	public function __construct() {
		$this->init();
		match (TRUE) {
			isset($_POST['image_format']) => $this->handle_image_format(),
			isset($_POST['image_quality']) => $this->handle_image_quality(),
			isset($_POST['current_set_id']) => $this->handle_current_set_id(),
			default => NULL
		};
	}

	private function handle_image_format(): void {
		$image_format = (string) filter_var($_POST['image_format'], FILTER_SANITIZE_SPECIAL_CHARS);
		$this->db->save_preference('image_format', $image_format);
	}

	private function handle_image_quality(): void {
		$image_quality = (int) filter_var($_POST['image_quality'], FILTER_VALIDATE_INT);
		in_range($image_quality, 10, 95) or $image_quality = 30;
		$this->db->save_preference('image_quality', (string) $image_quality);
	}

	private function handle_current_set_id(): void {
		$current_set_id = (int) filter_var($_POST['current_set_id'], FILTER_VALIDATE_INT);
		$current_set_id > 0 and $this->db->save_preference('current_set_id', (string) $current_set_id);
	}
}

new Handle_image;
