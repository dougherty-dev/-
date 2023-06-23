<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Check_password {
	use Common;

	public function __construct() {
		$this->init();
		$this->check_password();
	}

	private function check_password(): void {
		$validator = NULL;
		if (file_exists(PASSWORD_FILE)) {
			$file = new SplFileObject(PASSWORD_FILE);
			$line = $file->current();
			if (is_string($line)) {
				$this->db->save_preference('password_hash', password_hash(trim($line), PASSWORD_DEFAULT));
				$validator = rbytes(64);
				unlink(PASSWORD_FILE);
			}
		} elseif (isset($_POST['password'])) {
			if (password_verify($_POST['password'], $this->db->get_preference('password_hash'))) {
				$validator = rbytes(32);
			}
		}

		$validator and $this->db->save_preference('validator_hash', hash('sha256', $validator));
		echo $validator;
	}
}

new Check_password;
