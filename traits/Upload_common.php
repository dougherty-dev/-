<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Upload_common {
	use Ajax_common;

	private function check_post_id(): bool {
		isset($_POST['post_id']) and $this->post_id = (int) filter_var($_POST['post_id'], FILTER_VALIDATE_INT);
		return $this->post_id > 0;
	}

	/** @param string[] $file */
	private function upload_exists(array $file): bool {
		return isset($file['tmp_name'], $file['name'], $file['error']) && $file['error'] == UPLOAD_ERR_OK;
	}

	/** @param string[] $file */
	private function file_is_moved(array $file): bool {
		$this->uploaded_file = UPLOADS . '/' . $file['name'];
		return is_uploaded_file($file['tmp_name']) && move_uploaded_file($file['tmp_name'], $this->uploaded_file);
	}

	private function file_is_image(): bool {
		return ($finfo = finfo_open(FILEINFO_MIME_TYPE) and
			$finfo_file = finfo_file($finfo, $this->uploaded_file) and
			explode('/', $finfo_file)[0] === 'image');
	}

	private function cleanup(): void {
			is_real_file($this->uploaded_file) and unlink($this->uploaded_file);
			echo "⚠️";
			exit;
	}
}
