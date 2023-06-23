<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Attach {
	use Common, Upload_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and $this->handle_attachment_upload();
	}

	private function handle_attachment_upload(): void {
		$this->get_image_data();
		foreach ($_FILES as $file) {
			$this->upload_exists($file) and $this->file_is_moved($file) or $this->cleanup();
			$this->move_attachment() and $this->attachment_to_db() or $this->cleanup();
			echo "✅";
		}
	}

	private function move_attachment(): bool {
		$this->attachment = pathinfo($this->uploaded_file, PATHINFO_EXTENSION);
		$pathname = imagepath($this->dir, $this->id, $this->slug, 'a', $this->attachment, USERIMAGES);
		return rename($this->uploaded_file, $pathname);
	}

	private function attachment_to_db(): bool {
		$stmt = $this->db->instance->prepare("UPDATE `images` SET `attachment`=:attachment WHERE `id`=:id");
		$stmt->bindValue(':attachment', $this->attachment, PDO::PARAM_STR);
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $stmt->execute();
	}
}

new Attach;
