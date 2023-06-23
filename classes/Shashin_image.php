<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Shashin_image {
	use Common, Security, Headers, Markup;

	public function __construct(public int $image_path_id, public string $image_path_slug) {
		$this->credentials();
		$this->init_markup();
		$this->show_shashin();
		$this->end_markup();
	}

	private function show_folio(): string {
		$this->current_image_id = $this->image_path_id;
		$this->current_image_type = IMAGE_TYPES['medium'];
		$this->get_set();
		$this->get_set_images();

		$keys = array_keys($this->image_id);
		$k = (int) array_search($this->current_image_id, $this->image_id);
		$prev = in_array($i = $k - 1, $keys) ? $i : NULL;
		$next = in_array($i = $k + 1, $keys) ? $i : NULL;

		$thumb_prev = ($prev !== NULL) ? $this->adjacent_image($prev, 'thumb-left') : '';
		$thumb_next = ($next !== NULL) ? $this->adjacent_image($next, 'thumb-right') : '';

		if ($this->image_attachment[$k] && in_array($this->image_attachment[$k], array_column(Video_format::cases(), 'value'))) {
			$source = imagepath($this->image_dir[$k], $this->image_id[$k], $this->image_slug[$k], 'a', $this->image_attachment[$k]);
			$media = <<< EOT
					<video id="video" class="medium" controls controlslist="nofullscreen nodownload noremoteplayback noplaybackrate">
						<source src="$source" type="video/{$this->image_attachment[$k]}">
						<img class="medium" data-link="{$this->image_id[$k]}" src="{$this->image_filepath[$k]}">
					</video>

EOT;
		} else {
			$media = <<< EOT
					<img class="medium" data-link="{$this->image_id[$k]}" src="{$this->image_filepath[$k]}">

EOT;
		}

		return <<< EOT
			<div class="grid-folio">
				<div id="imageview">
$media					<p class="image-title">{$this->image_title[$k]}</p>
$thumb_prev$thumb_next					<div class="image-description">{$this->image_description[$k]}</div>
				<div>
				<div id="original"></div>
			</div> <!-- grid-folio -->

EOT;
	}

	private function get_set(): void {
		$stmt = $this->db->instance->prepare('SELECT `set` FROM `images` WHERE `id`=:id');
		$stmt->bindValue(':id', $this->current_image_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt !== FALSE) $this->current_set_id = (int) $stmt->fetchColumn();
	}

	private function adjacent_image(int $id, string $class): string {
		$dim = $this->image_dimensions($this->image_w[$id], $this->image_h[$id], 50);
		$path = imagepath($this->image_dir[$id], $this->image_id[$id], $this->image_slug[$id], 't', $this->image_ext[$id]);
		$pagepath = findpath($this->image_id[$id], $this->image_slug[$id]);
		return <<< EOT
					<img class="$class" data-link="$pagepath" src="$path" $dim>

EOT;
	}

	private function image_dimensions(int $w, int $h, int $dim): string {
		return $w > $h ? "width=\"$dim\"" : "height=\"$dim\"";
	}

}
