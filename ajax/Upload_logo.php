<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Upload_logo {
	use Common, Upload_common;

	public function __construct() {
		$this->init();
		function_exists('imagewebp') and $this->handle_upload();
	}

	private function handle_upload(): void {
		$this->upload_exists($_FILES['file']) and $this->file_is_moved($_FILES['file']) and
			$this->file_is_image() and $this->handle_images();

		is_real_file($this->uploaded_file) and unlink($this->uploaded_file);
	}

	private function handle_images(): void {
		$i = imagecreatefromstring((string) file_get_contents($this->uploaded_file));
		if ($i === FALSE || imagesx($i) < SIZES['o']) return;
		imagewebp($i, USERLOGO, 70);
	}
}

new Upload_logo;
