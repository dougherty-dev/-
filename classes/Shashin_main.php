<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Shashin_main {
	use Common, Security, Headers, Markup;

	public function __construct() {
		$this->credentials();
		$this->init_markup();
		$this->show_shashin();
		$this->end_markup();
	}

	private function show_folio(): string {
		$this->current_image_type = IMAGE_TYPES['small'];
		$this->get_sets();
		$this->get_front_images();
		$sets = '';
		foreach ($this->proper_set_id as $order => $set_id) {
			$setpath = findpath($set_id, $this->set_slug[$order], 's');
			$title = reduced_string($this->set_title[$order]);
			$sets .= <<< EOT
					<div class="img" data-link="$setpath" title="$title">
						<img src="{$this->image_filepath[$order]}">
						<p class="image-text">$title ({$this->set_count[$order]})</p>
					</div>

EOT;
		}

		return <<< EOT
			<div class="grid-folio">
				<div id="folio">
$sets				<div>
			</div> <!-- grid-folio -->

EOT;
	}

}
