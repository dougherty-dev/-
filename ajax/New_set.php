<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class New_set {
	use Common;

	public function __construct() {
		$this->init();
		if (isset($_POST['new_set'])) $this->new_set();
	}

	private function new_set(): void {
		parse_str($_POST['new_set'], $new_set);
		$title = $slug = '';

		is_string($new_set['title']) and $title = trim_title($new_set['title']);
		if ($title === '') return;

		if (is_string($new_set['slug']) && $new_set['slug'] !== '') {
			$slug = trim_slug($new_set['slug']);
		} else {
			$slug = trim_slug($title);
		}

		$description = filter_var($new_set['description'], FILTER_SANITIZE_SPECIAL_CHARS);

		$stmt = $this->db->instance->prepare("SELECT MAX(`order`) AS `max` FROM `sets`");
		$stmt->execute();
		$max = (int) $stmt->fetchColumn();

		$stmt = $this->db->instance->prepare("INSERT INTO `sets` (`title`, `slug`, `description`, `order`)
			VALUES (:title, :slug, :description, :order)");
		$stmt->bindValue(':title', $title, PDO::PARAM_STR);
		$stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
		$stmt->bindValue(':description', $description, PDO::PARAM_STR);
		$stmt->bindValue(':order', $max + 1, PDO::PARAM_INT);
		$stmt->execute();
	}
}

new New_set;
