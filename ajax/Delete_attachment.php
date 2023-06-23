<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Delete_attachment {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and $this->delete_attachment();
	}

	private function delete_attachment(): void {
		$this->get_image_data();
		$pathname = imagepath($this->dir, $this->id, $this->slug, 'a', $this->attachment, USERIMAGES);
		file_exists($pathname) and unlink($pathname);
		$this->db_remove_attachment() or exit;
		echo TRUE;
	}

	private function db_remove_attachment(): bool {
		$stmt = $this->db->instance->prepare("UPDATE `images` SET `attachment`=NULL WHERE `id`=:id");
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		return $stmt->execute();
	}
}

new Delete_attachment;
