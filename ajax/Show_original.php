<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';

class Show_original {
	use Common;

	public function __construct() {
		$this->init();
		isset($_POST['original_id']) and $this->show_original();
	}

	private function show_original(): void {
		$original_id = (int) filter_var($_POST['original_id'], FILTER_VALIDATE_INT);
		$original_id > 0 or exit;

		$stmt = $this->db->instance->prepare('SELECT * FROM `images` WHERE `id`=:id');
		$stmt->bindValue(':id', $original_id, PDO::PARAM_INT);
		$stmt->execute();
		$stmt !== FALSE or exit;
		$r = $stmt->fetchAll(PDO::FETCH_ASSOC)[0];

		$image_description = $r['description'] ?? '';
		$image_title = $r['title'] ?? '';
		$image_filepath = imagepath($r['dir'], $r['id'], $r['slug'], 'o', $r['ext']);
		$image_attachment = (string) $r['attachment'];
		$href_filepath = $r['attachment'] ? imagepath($r['dir'], $r['id'], $r['slug'], 'a', $r['attachment']) : $image_filepath;

		echo <<< EOT
			<p><a target="image" href="$href_filepath"><img class="original" src="$image_filepath" width="{$r['w']}" height="{$r['h']}" title="$image_title"></a></p>

EOT;

		if ($r['attachment']) echo <<< EOT
			<p class="attachment-text">🔗 {$r['attachment']}</p>

EOT;
	}
}

new Show_original;
