<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Replace_image {
	use Common, Upload_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and $this->handle_upload();
	}

	private function handle_upload(): void {
		$ext = '';
		$this->get_image_data();

		$type_array = [];
		foreach ($_FILES as $file) {
			$filename = pathinfo($file['name'], PATHINFO_FILENAME);
			$type_array[] = mb_substr($filename, -1, 1);
		}

		array_diff(array_values(IMAGE_TYPES), $type_array) === [] or $this->cleanup();

		$exts = [];
		foreach (Image_format::cases() as $case) $exts[] = $case->format()['ext'];

		foreach ($_FILES as $file) {
			$this->upload_exists($file) and $this->file_is_moved($file) or $this->cleanup();

			$pathinfo = pathinfo($this->uploaded_file);
			isset($pathinfo['extension']) and $ext = $pathinfo['extension'] or $this->cleanup();
			in_array($ext, $exts) or $this->cleanup();

			$slug = mb_substr($pathinfo['filename'], 0, -2);

			$slug === $this->slug or $this->cleanup();

			$type = mb_substr($pathinfo['filename'], -1, 1);


			if ($type === 'o' && $ext !== $this->ext) {
				$stmt = $this->db->instance->prepare('UPDATE `images` SET `ext`=:ext WHERE `id`=:id');
				$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
				$stmt->bindValue(':ext', $ext, PDO::PARAM_STR);
				$stmt->execute();
			}

			$oldpath = imagepath($this->dir, $this->id, $slug, $type, $this->ext, USERIMAGES);
			$newpath = imagepath($this->dir, $this->id, $slug, $type, $ext, USERIMAGES);

			file_exists($oldpath) and is_file($oldpath) or $this->cleanup();
			unlink($oldpath);
			rename($this->uploaded_file, $newpath);
			echo "✅";
		}
	}
}

new Replace_image;
