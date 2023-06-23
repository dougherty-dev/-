<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Upload_image {
	use Common, Upload_common;

	private ?GdImage $image;
	private Image_format $image_format;
	private int $image_quality = 30;
	private string $pathname = '';
	/** @var int[] $w */ private array $w = ['o' => 0, 'm' => 0, 's' => 0, 't' => 0];
	/** @var int[] $h */ private array $h = ['o' => 0, 'm' => 0, 's' => 0, 't' => 0];

	public function __construct() {
		$this->init();
		$this->get_prefs();
		$this->current_set_id > 0 and function_exists('imagecreatefromstring') and $this->handle_upload();
	}

	private function get_prefs(): void {
		$this->image_format = Image_format::tryFrom($this->db->get_preference('image_format')) ?? Image_format::WebP;
		$this->image_quality = (int) $this->db->get_preference('image_quality');
		in_range($this->image_quality, 10, 95) or $this->image_quality = 30;
		$this->current_set_id = (int) $this->db->get_preference('current_set_id');
	}

	private function handle_upload(): void {
		$this->upload_exists($_FILES['file']) and $this->file_is_moved($_FILES['file']) and $this->file_is_image() and
			$this->have_dimensions() and $this->have_pathname() and $this->handle_images();

		is_real_file($this->uploaded_file) and unlink($this->uploaded_file);
	}

	private function have_dimensions(): bool {
		$i = imagecreatefromstring((string) file_get_contents($this->uploaded_file));
		if ($i === FALSE || max($this->w['o'] = imagesx($i), $this->h['o'] = imagesy($i)) < SIZES['o']) return FALSE;
		$this->image = $i;

		foreach (['m', 's', 't'] as $d) [$this->w[$d], $this->h[$d]] = $this->scaled_image_params($this->w['o'], $this->h['o'], SIZES[$d]);
		return TRUE;
	}

	/** @return int[] */
	private function scaled_image_params(int $w, int $h, int $ref): array {
		$h > 0 && $w > 0 && $ref > 0 or exit;
		$q = $w / $h;
		[$sf_w, $sf_h] = ($h > $w) ? [$q, 1] : [1, 1 / $q];
		return [(int) ceil($sf_w * $ref), (int) ceil($sf_h * $ref)];
	}

	private function have_pathname(): bool {
		$this->dir = date('Y') . '/' . date('m');
		make_check_directory(USERIMAGES . "/{$this->dir}");
		$this->slug = trim_slug(pathinfo($this->uploaded_file, PATHINFO_FILENAME));
		$id = $this->add_image_to_db();
		$this->pathname = USERIMAGES . "/{$this->dir}/$id-{$this->slug}"; // unique
		return $id > 0;
	}

	private function handle_images(): void {
		$this->save_image($this->image, "{$this->pathname}-o.");
		foreach (['m', 's', 't'] as $d) {
			if ($this->image !== NULL) {
				$image = imagescale($this->image, $this->w[$d], $this->h[$d]);
				$image !== FALSE and $this->save_image($image, "{$this->pathname}-$d.");
			}
		}
	}

	private function add_image_to_db(): int {
		$stmt = $this->db->instance->prepare('INSERT INTO `images` (`set`, `dir`, `slug`, `ext`, `w`, `h`)
			VALUES (:set, :dir, :slug, :ext, :w, :h) RETURNING `id`');
		$stmt->bindValue(':set', $this->current_set_id, PDO::PARAM_INT);
		$stmt->bindValue(':dir', $this->dir, PDO::PARAM_STR);
		$stmt->bindValue(':slug', $this->slug, PDO::PARAM_STR);
		$stmt->bindValue(':ext', $this->image_format->format()['ext'], PDO::PARAM_STR);
		$stmt->bindValue(':w', $this->w['o'], PDO::PARAM_INT);
		$stmt->bindValue(':h', $this->h['o'], PDO::PARAM_INT);
		$stmt->execute();
		return (int) $stmt->fetchColumn();
	}

	private function save_image(?GdImage $image, string $filename): void {
		$image !== NULL and match ($this->image_format) {
			Image_format::WebP => function_exists('imagewebp') and imagewebp($image, $filename . Image_format::WebP->format()['ext'], $this->image_quality),
			Image_format::AVIF => function_exists('imageavif') and imageavif($image, $filename . Image_format::AVIF->format()['ext'], $this->image_quality),
			Image_format::JPEG => imagejpeg($image, $filename . Image_format::JPEG->format()['ext'], $this->image_quality),
		};
	}
}

new Upload_image;
