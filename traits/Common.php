<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Common {
	private Database $db;

	/** @var int[] $set_id */ public array $set_id = [];
	/** @var string[] $set_slug */ public array $set_slug = [];
	/** @var string[] $set_title */ public array $set_title = [];
	/** @var string[] $set_description */ public array $set_description = [];
	/** @var int[] $set_order */ public array $set_order = [];
	/** @var int[] $set_count */ public array $set_count = [];
	/** @var int[] $proper_set_id */ public array $proper_set_id = [];

	/** @var int[] $theme_id */ public array $theme_id = [];
	/** @var string[] $theme_slug */ public array $theme_slug = [];
	/** @var string[] $theme_name */ public array $theme_name = [];
	/** @var string[] $theme_time */ public array $theme_time = [];

	/** @var int[] $image_id */ private array $image_id = [];
	/** @var string[] $image_dir */ private array $image_dir = [];
	/** @var string[] $image_slug */ private array $image_slug = [];
	/** @var string[] $image_ext */ private array $image_ext = [];
	/** @var string[] $image_title */ private array $image_title = [];
	/** @var string[] $image_description */ private array $image_description = [];
	/** @var int[] $image_w */ private array $image_w = [];
	/** @var int[] $image_h */ private array $image_h = [];
	/** @var int[] $image_order */ private array $image_order = [];
	/** @var int[] $image_poster */ private array $image_poster = [];
	/** @var string[] $image_attachment */ private array $image_attachment = [];
	/** @var string[] $image_filepath */ private array $image_filepath = [];

	/** @var int[] $page_id */ public array $page_id = [];
	/** @var string[] $page_slug */ public array $page_slug = [];
	/** @var string[] $page_title */ public array $page_title = [];
	/** @var string[] $page_content */ public array $page_content = [];
	/** @var int[] $page_order */ public array $page_order = [];

	private int $current_set_id = 0, $current_image_id = 0, $current_theme_id = 0, $current_theme_time = 0;
	private string $current_image_type = IMAGE_TYPES['small'];
	private string $current_theme_slug = '';

	private function init(): void {
		$this->db = new Database;
	}

	private function echo(string $string): string {
		return $string;
	}

	private function is_admin(): bool {
		return isset($_SESSION['admin']) && $_SESSION['admin'];
	}

	private function get_sets(): void {
		$stmt = $this->db->instance->prepare('SELECT * FROM `sets` ORDER BY `order`');
		$stmt->execute();
		$n = 1;
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->set_id[] = $r['id'];
			$this->set_slug[] = $r['slug'];
			$this->set_title[] = $r['title'];
			$this->set_description[] = decode_text((string) $r['description']);
			$this->set_order[] = $n++;
		}
	}

	private function get_pages(): void {
		$stmt = $this->db->instance->prepare('SELECT * FROM `pages` ORDER BY `order`');
		$stmt->execute();
		$n = 1;
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->page_id[] = $r['id'];
			$this->page_slug[] = $r['slug'];
			$this->page_title[] = $r['title'];
			$this->page_content[] = decode_text((string) $r['content']);
			$this->page_order[] = $n++;
		}
	}

	private function get_themes(): void {
		$stmt = $this->db->instance->prepare('SELECT * FROM `themes`');
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->theme_id[] = $r['id'];
			$this->theme_slug[] = $r['slug'];
			$this->theme_name[] = $r['name'];
			$this->theme_time[] = $r['time'];
		}
	}

	private function get_current_theme(): void {
		$this->current_theme_id = (int) $this->db->get_preference('current_theme_id');
		if (!in_array($this->current_theme_id, $this->theme_id)) {
			$this->current_theme_id = 0;
			$this->db->save_preference('current_theme_id', (string) $this->current_theme_id);
		}

		$slug = '';
		$stmt = $this->db->instance->prepare('SELECT `slug`, `time` FROM `themes` WHERE `id`=:id');
		$stmt->bindValue(':id', $this->current_theme_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$slug = $r['slug'];
			$this->current_theme_time = (int) $r['time'];
		}
		$slug !== '' and $this->current_theme_slug = "{$this->current_theme_id}-$slug";
	}

	private function get_set_images(): void {
		$stmt = $this->db->instance->prepare('SELECT * FROM `images` WHERE `set`=:set_id ORDER BY `order`');
		$stmt->bindValue(':set_id', $this->current_set_id, PDO::PARAM_INT);
		$stmt->execute();
		$n = 1;
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->image_id[] = $r['id'];
			$this->image_dir[] = $r['dir'];
			$this->image_slug[] = $r['slug'];
			$this->image_ext[] = $r['ext'];
			$this->image_title[] = $r['title'];
			$this->image_description[] = decode_text((string) $r['description']);
			$this->image_w[] = $r['w'];
			$this->image_h[] = $r['h'];
			$this->image_order[] = $n++;
			$this->image_poster[] = $r['poster'];
			$this->image_attachment[] = $r['attachment'];
			$this->image_filepath[] = imagepath($r['dir'], $r['id'], $r['slug'], $this->current_image_type, $r['ext']);
		}
	}

	private function get_front_images(): void {
		$stmt = $this->db->instance->prepare('SELECT `images`.`title` AS `title`, `images`.`description` AS `description`,
			`images`.`id` AS `id`, `images`.`slug` AS `slug`, `dir`, `ext`, `set`, COUNT(`images`.`id`) AS `count`, MAX(`images`.`poster`)
			FROM `images` LEFT JOIN `sets` ON `sets`.`id`=`images`.`set` GROUP BY `sets`.`order`');
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->image_title[] = $r['title'];
			$this->image_description[] = decode_text((string) $r['description']);
			$this->image_filepath[] = imagepath($r['dir'], $r['id'], $r['slug'], $this->current_image_type, $r['ext']);
			$this->set_count[] = $r['count'];
			$r['count'] > 0 and $this->proper_set_id[] = $r['set'];
		}
	}

	private function count_images(int $set_id): int {
		$stmt = $this->db->instance->prepare('SELECT COUNT(`id`) FROM `images` WHERE `set`=:set_id');
		$stmt->bindValue(':set_id', $set_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE) return (int) $stmt->fetchColumn();
		return 0;
	}

	private function reorder_images(): void {
		foreach ($this->image_id as $order => $id) {
			$stmt = $this->db->instance->prepare('UPDATE `images` SET `order`=:order WHERE `id`=:id');
			$stmt->bindValue(':order', $this->image_order[$order], PDO::PARAM_INT);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	private function reorder_sets(): void {
		foreach ($this->set_id as $order => $id) {
			$stmt = $this->db->instance->prepare('UPDATE `sets` SET `order`=:order WHERE `id`=:id');
			$stmt->bindValue(':order', $this->set_order[$order], PDO::PARAM_INT);
			$stmt->bindValue(':id', $id, PDO::PARAM_INT);
			$stmt->execute();
		}
	}

	private function swap_order(int $id1, int $id2, string $table): void {
		$this->db->instance->beginTransaction();
		$stmt = $this->db->instance->prepare("UPDATE `$table` SET `order`=`order`-1 WHERE `id`=:id1");
		$stmt->bindValue(':id1', $id1, PDO::PARAM_INT);
		$stmt->execute();

		$stmt = $this->db->instance->prepare("UPDATE `$table` SET `order`=`order`+1 WHERE `id`=:id2");
		$stmt->bindValue(':id2', $id2, PDO::PARAM_INT);
		$stmt->execute();
		$this->db->instance->commit();
	}

	private function get_footer(): string {
		$y = date("Y");
		$footer = decode_text((string) $this->db->get_preference('footer'));
		if ($footer === '') $footer = <<< EOT
					<p class="horizontal">© $y</p>
					<img class="software horizontal" src="{$this->echo(IMAGES)}/shashin.webp" alt="写真 Shashin" title="写真 Shashin">

EOT;
		return $footer;
	}

}
