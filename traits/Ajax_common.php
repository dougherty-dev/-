<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Ajax_common {
	private int $post_id = 0, $id = 0;
	private string $dir = '', $slug = '', $ext = '', $uploaded_file = '', $attachment = '';

	private function check_post_id(): bool {
		isset($_POST['post_id']) and $this->post_id = (int) filter_var($_POST['post_id'], FILTER_VALIDATE_INT);
		return $this->post_id > 0;
	}

	private function db_modify_text(string $table, string $field, string $text): void {
		$stmt = $this->db->instance->prepare("UPDATE `$table` SET `$field`=:field WHERE `id`=:id");
		$stmt->bindValue(':field', $text, PDO::PARAM_STR);
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
	}

	private function get_image_data(): void {
		$stmt = $this->db->instance->prepare("SELECT `id`, `dir`, `slug`, `ext`, `attachment` FROM `images` WHERE `id`=:id LIMIT 1");
		$stmt->bindValue(':id', $this->post_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE && is_array($r = $stmt->fetch(PDO::FETCH_ASSOC))) {
			$this->id = $r['id'];
			$this->dir = $r['dir'];
			$this->slug = $r['slug'];
			$this->ext = $r['ext'];
			$this->attachment = (string) $r['attachment'];
		}
	}
}
