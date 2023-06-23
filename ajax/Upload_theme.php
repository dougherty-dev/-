<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

final class Upload_theme {
	use Common, Upload_common;

	private string $filename = '', $pathname = '', $name = '', $ziptemp = '';

	public function __construct() {
		$this->init();
		extension_loaded('zip') and $this->handle_upload();
	}

	private function handle_upload(): void {
		$this->upload_exists($_FILES['file']) and $this->file_is_moved($_FILES['file']) and
		$this->file_is_archive() and $this->have_pathname() and $this->handle_archive();

		is_real_file($this->uploaded_file) and unlink($this->uploaded_file);
	}

	private function file_is_archive(): bool {
		return ($finfo = finfo_open(FILEINFO_MIME_TYPE) and
			$finfo_file = finfo_file($finfo, $this->uploaded_file) and
			in_array($finfo_file, ['application/zip', 'application/x-zip-compressed', 'multipart/x-zip']));
	}

	private function have_pathname(): bool {
		$this->filename = pathinfo($this->uploaded_file, PATHINFO_FILENAME);
		$this->name = trim_title($this->filename);
		$this->slug = trim_slug($this->filename);
		$this->id = $this->db_add_theme();
		$this->pathname = USERTHEMES . "/{$this->id}-{$this->slug}"; // unique
		make_check_directory($this->pathname);
		return $this->id > 0;
	}

	private function handle_archive(): void {
		$this->unzip();

		if (file_exists($this->pathname . '/' . THEME_FILENAME)) {
			$this->postzip();
		} else { // invalid theme, no theme.css
			empty_folder($this->pathname);
			$this->db_delete_theme();
		}
	}

	private function unzip(): void {
		make_check_directory($this->ziptemp = UPLOADS . '/' . time());

		$zip = new ZipArchive;
		$zip->open($this->uploaded_file);
		$zip->extractTo($this->ziptemp);
		$zip->close();

		$zipdir = glob("{$this->ziptemp}/*", GLOB_ONLYDIR);
		is_array($zipdir) and copy_folder($zipdir[0] , $this->pathname);
		empty_folder($this->ziptemp);
	}

	private function postzip(): void {
		$themename = $this->find_theme_name();
		if ($themename !== '') {
			$this->name = trim_title($themename);
			$this->slug = trim_slug($themename);
			rename($this->pathname, $new_pathname = USERTHEMES . "/{$this->id}-{$this->slug}");
			$this->db_update_theme();
		}
	}

	function find_theme_name(): string {
		if (FALSE !== $lines = file($this->pathname . '/' . THEME_FILENAME)) {
			foreach ($lines as $line) {
				if (str_starts_with($line, 'Theme name:')) {
					return trim(explode(':', $line, 2)[1]);
				}
			}
		}
		return '';
	}

	private function db_add_theme(): int {
		$stmt = $this->db->instance->prepare('INSERT INTO `themes` (`name`, `slug`, `time`)
			VALUES (:name, :slug, :time) RETURNING `id`');
		$stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
		$stmt->bindValue(':slug', $this->slug, PDO::PARAM_STR);
		$stmt->bindValue(':time', time(), PDO::PARAM_STR);
		$stmt->execute();
		return (int) $stmt->fetchColumn();
	}

	private function db_update_theme(): void {
		$stmt = $this->db->instance->prepare('UPDATE `themes` SET `name`=:name, `slug`=:slug WHERE `id`=:id');
		$stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
		$stmt->bindValue(':slug', $this->slug, PDO::PARAM_STR);
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		$stmt->execute();
	}

	private function db_delete_theme(): void {
		$this->id > 0 or exit;
		$stmt = $this->db->instance->prepare('DELETE FROM `themes` WHERE `id`=:id');
		$stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
		$stmt->execute();
	}
}

new Upload_theme;
