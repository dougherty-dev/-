<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class New_page {
	use Common;

	public function __construct() {
		$this->init();
		if (isset($_POST['new_page'])) $this->new_page();
	}

	private function new_page(): void {
		parse_str($_POST['new_page'], $new_page);
		$title = $slug = '';

		is_string($new_page['title']) and $title = trim_title($new_page['title']);
		if ($title === '') return;

		if (is_string($new_page['slug']) && $new_page['slug'] !== '') {
			$slug = trim_slug($new_page['slug']);
		} else {
			$slug = trim_slug($title);
		}

		$stmt = $this->db->instance->prepare("SELECT MAX(`order`) AS `max` FROM `pages`");
		$stmt->execute();
		$max = (int) $stmt->fetchColumn();

		$stmt = $this->db->instance->prepare("INSERT INTO `pages` (`title`, `slug`, `order`)
			VALUES (:title, :slug, :order)");
		$stmt->bindValue(':title', $title, PDO::PARAM_STR);
		$stmt->bindValue(':slug', $slug, PDO::PARAM_STR);
		$stmt->bindValue(':order', $max + 1, PDO::PARAM_INT);
		$stmt->execute();
	}
}

new New_page;
