<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Admin_images {
	use Admin_common;

	private Image_format $image_format;
	private int $image_quality = 30;

	public function __construct() {
		$this->secure();
		$this->get_image_format();
		$this->get_image_quality();
		$this->get_sets();
		$this->get_current_image_set();
		$this->get_set_images();
		$this->reorder_images();
		$this->show_admin();
	}

	private function get_image_format(): void {
		$image_format = $this->db->get_preference('image_format');
		$this->image_format = Image_format::tryFrom($image_format) ?? Image_format::WebP;
		$this->db->save_preference('image_format', $this->image_format->value);
	}

	private function get_image_quality(): void {
		$this->image_quality = (int) $this->db->get_preference('image_quality');
		in_range($this->image_quality, 10, 90) or $this->image_quality = 30;
		$this->db->save_preference('image_quality', (string) $this->image_quality);
	}

	private function get_current_image_set(): void {
		$this->current_image_type = IMAGE_TYPES['thumb'];
		$this->current_set_id = (int) $this->db->get_preference('current_set_id');
		if (!in_array($this->current_set_id, $this->set_id)) {
			$this->current_set_id = count($this->set_id) ? $this->set_id[0] : 0;
			$this->db->save_preference('current_set_id', (string) $this->current_set_id);
		}
	}

	private function show_admin_folio(): string {
		return <<< EOT
			<div class="grid-folio">
				<h1>Images</h1>
{$this->get_image_format_options()}
{$this->get_quality_options()}
{$this->get_set_options()}
{$this->get_dropzone()}
{$this->get_image_list()}
			</div> <!-- grid-folio -->
EOT;
	}

	private function get_image_format_options(): string {
		$image_format_options = '';
		foreach (Image_format::cases() as $image_format) {
			$selected = ($this->image_format === $image_format) ? ' selected="selected"' : '';
			$image_format_options .= <<< EOT
					<option value="{$image_format->value}"$selected>{$image_format->format()['type']}</option>

EOT;
		}

		return <<< EOT
				<select id="image_format">
$image_format_options				</select>
EOT;
	}

	private function get_quality_options(): string {
		$image_quality_options = '';
		foreach (range(5, 95, 5) as $quality) {
			$selected = ($this->image_quality === $quality) ? ' selected="selected"' : '';
			$image_quality_options .= <<< EOT
					<option value="$quality"$selected>$quality</option>

EOT;
		}
		return <<< EOT
				<select id="image_quality">
$image_quality_options				</select>
EOT;
	}

	private function get_set_options(): string {
		$set_options = '';
		foreach ($this->set_id as $k => $set_id) {
			$selected = ($this->current_set_id === $set_id) ? ' selected="selected"' : '';
			$set_options .= <<< EOT
					<option value="$set_id"$selected>{$this->set_title[$k]}</option>

EOT;
		}

		return <<< EOT
				➡️ <select id="current_set_id">
$set_options				</select>
EOT;
	}

	private function get_dropzone(): string {
		return <<< EOT
				<form id="dropimage" class="dropzone">
					<div class="dz-message" data-dz-message>
						<p class="emoji">⬇️</p>
						<p>AVIF, WebP, PNG, JPEG (2000+ px)</p>
					</div>
				</form>
EOT;
	}

	private function get_image_list(): string {
		$attach_media_text = <<< EOT
							<br>
							<span class="attach-media">🔗</span>
							<div class="drop-attach dialogue-attach">
								<button class="attach">🔗 ⬇️
									<span class="smaller">WebM, MP4, PDF</span>
								</button>
								<input type="file" accept="video/webm, video/mp4, image/pdf" class="media-attachment" name="filename" required>
							</div>

EOT;

		$dropdown_list = $this->get_dropdown_list();
		$images = '';
		foreach ($this->image_id as $k => $image_id) {
			$is_poster = $this->image_poster[$k] ? ' poster' : '';
			$attachment = $attach_media = '';
			if ($this->image_attachment[$k]) {
				$attachment = <<< EOT
							<span class="delete-attachment smaller">🔗 {$this->image_attachment[$k]} (❌)</span>

EOT;

			} else {
				$attach_media = $attach_media_text;
			}

			$images .= <<< EOT
					<tr class="edit-images" data-id="$image_id">
						<td class="edit-thumb center"><img class="thumb set-poster$is_poster" src="{$this->image_filepath[$k]}"></td>
						<td>
							<input type="text" size="40" maxlength="40" name="title" placeholder="Title" value="{$this->image_title[$k]}"><br>
							<input type="text" size="40" maxlength="40" name="slug" placeholder="Slug [a-z0-9\-]" value="{$this->image_slug[$k]}"><br>
							<textarea cols="40" rows="3" name="description" placeholder="Description (HTML)">{$this->image_description[$k]}</textarea><br>
$attachment						</td>
						<td>
							<span class="delete">❌</span>
							<span class="up">⬆️</span>
							<span class="down">⬇️</span><br>
							<span class="move-image">➡️</span>
$dropdown_list
							<br>
							<span class="replace-image">🔄</span>
							<div class="drop-replace dialogue-replace">
								<button class="replace">🔄
									<span class="smaller">️⬇️ <em>image[-o, -m, -s, -t].ext</em></span>
								</button>
								<input type="file" accept="image/webp, image/avif, image/jpeg" class="image-replacement" name="filename" required multiple>
							</div>
$attach_media						</td>
					</tr>

EOT;
		}

		return <<< EOT
				<table id="current-images" data-id="{$this->current_set_id}">
$images			</table>
EOT;
	}

	private function get_dropdown_list(): string {
		$set_options = <<< EOT
								<option disabled selected value>➡️</option>

EOT;
		foreach ($this->set_id as $k => $set_id) {
			$set_id !== $this->current_set_id and $set_options .= <<< EOT
								<option value="$set_id">{$this->set_title[$k]}</option>

EOT;

		}

		return <<< EOT
							<select class="move-image-set-list">
$set_options							</select>
EOT;
	}

}
