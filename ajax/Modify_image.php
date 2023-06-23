<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Modify_image {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and match (TRUE) {
			isset($_POST['set_poster'], $_POST['set_id']) => $this->set_poster(),
			isset($_POST['move_to_set_id']) => $this->move_image_to_set(),
			default => NULL
		};
	}

	private function set_poster(): void {
		$set_id = (int) filter_var($_POST['set_id'], FILTER_VALIDATE_INT);
		$set_id > 0 or exit;

		$this->db->instance->beginTransaction();
		$stmt = $this->db->instance->prepare("UPDATE `images` SET `poster`=0 WHERE `set`=:set_id;");
		$stmt->bindValue(':set_id', $set_id, PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->db->instance->prepare("UPDATE `images` SET `poster`=1 WHERE `id`=:id;");
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
		echo $this->db->instance->commit();
	}

	private function move_image_to_set(): void {
		$set_id = (int) filter_var($_POST['move_to_set_id'], FILTER_VALIDATE_INT);
		$set_id > 0 or exit;

		$stmt = $this->db->instance->prepare("UPDATE `images` SET `set`=:set_id, `poster`=0, `order`=99999 WHERE `id`=:id;");
		$stmt->bindValue(':set_id', $set_id, PDO::PARAM_INT);
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
	}

}

new Modify_image;
