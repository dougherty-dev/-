<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Shashin_page {
	use Common, Security, Headers, Markup;

	private string $content = '';

	public function __construct(public int $page_path_id, public string $page_path_slug) {
		$this->credentials();
		$this->init_markup();
		$this->show_shashin();
		$this->end_markup();
	}
//

	private function show_folio(): string {
		$this->get_page();
		return <<< EOT
			<div class="grid-folio">
{$this->content}			</div> <!-- grid-folio -->

EOT;
	}

	private function get_page(): void {
		$stmt = $this->db->instance->prepare('SELECT `content` FROM `pages` WHERE `id`=:id');
		$stmt->bindValue(':id', $this->page_path_id, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt !== FALSE) $this->content = decode_text((string) $stmt->fetchColumn());
	}

}
