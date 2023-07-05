<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final Class Controller {
	private Database $db;
	private int $id = 0;
	private string $slug = '', $dirname = '', $basename = '';

	public function __construct() {
		$this->pathinfo();
		match ($this->dirname) {
			'/s' => $this->shashin_set(),
			'/i' => $this->shashin_image(),
			'/p' => $this->shashin_page(),
			default => $this->shashin_main()
		};
	}

	private function shashin_set(): void {
		$this->check_id_slug('sets') or $this->redirect();
		new Shashin_set($this->id, $this->slug);
	}

	private function shashin_image(): void {
		$this->check_id_slug('images') or $this->redirect();
		new Shashin_image($this->id, $this->slug);
	}

	private function shashin_page(): void {
		$this->check_id_slug('pages') or $this->redirect();
		new Shashin_page($this->id, $this->slug);
	}

	private function shashin_main(): void {
		$this->basename === '' or $this->deny();
		new Shashin_main;
	}

	private function pathinfo(): void {
		$pathinfo = pathinfo($_SERVER["REQUEST_URI"]);
		$components = explode('-', $this->basename = $pathinfo['basename'], 2);
		isset($pathinfo['dirname']) and $this->dirname = $pathinfo['dirname'];
		isset($components[0]) and $this->id = (int) $components[0];
		isset($components[1]) and $this->slug = $components[1];
		if (constant('EXTENSION') !== '') {
			$parts = explode('.', $this->slug);
			$this->slug = (isset($parts[1]) && $parts[1] === EXTENSION) ? $parts[0] : '';
		}
	}

	private function check_id_slug(string $table): int {
		$this->db = new Database;
		$stmt = $this->db->instance->prepare("SELECT COUNT(`id`) FROM `$table` WHERE `id`=:id AND `slug`=:slug");
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		$stmt->bindValue(':slug', $this->slug, PDO::PARAM_STR);
		$stmt->execute();
		return ($stmt !== FALSE) ? (int) $stmt->fetchColumn() : 0;
	}

	private function redirect(): void {
		http_response_code(301);
		headers_sent() or header('Location: /');
		exit;
	}

	private function deny(): void {
		http_response_code(403);
		headers_sent() or header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
		exit;
	}
}
