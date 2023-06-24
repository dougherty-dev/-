<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Shashin_set {
	use Common, Security, Headers, Markup;

	private string $current_set_title, $current_set_description;

	public function __construct(public int $set_path_id, public string $set_path_slug) {
		$this->credentials();
		$this->init_markup();
		$this->show_shashin();
		$this->end_markup();
	}

	private function show_folio(): string {
		$this->current_set_id = $this->set_path_id;
		$this->current_image_type = IMAGE_TYPES['small'];
		$this->get_set_data();
		$this->get_set_images();
		$this->reorder_images();
		$images = '';
		foreach ($this->image_id as $order => $image_id) {
			$lazy = ($order > 20) ? ' loading="lazy"' : '';
			$pagepath = findpath($image_id, $this->image_slug[$order]);
			$description = reduced_string($this->image_title[$order]);
			$images .= <<< EOT
					<div class="img" data-link="$pagepath" title="{$this->image_title[$order]}">
						<img$lazy src="{$this->image_filepath[$order]}">
						<p class="image-text">$description</p>
					</div>

EOT;
		}

		return <<< EOT
			<div class="grid-folio">
				<p class="set-title">{$this->current_set_title}</p>
				<div class="set-description">{$this->current_set_description}</div>
				<div id="folio">
$images				<div>
			</div> <!-- grid-folio -->

EOT;
	}

	private function get_set_data(): void {
		$stmt = $this->db->instance->prepare("SELECT * FROM `sets` WHERE `id`=:id ORDER BY `order` LIMIT 1");
		$stmt->bindValue(':id', $this->current_set_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$this->current_set_title = $r['title'];
			$this->current_set_description = decode_text((string) $r['description']);
		}
	}
}
