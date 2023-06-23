<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Logout {
	use Common;

	public function __construct() {
		$this->init();
		$this->logout();
	}

	private function logout(): void {
		$this->db->delete_preference('validator_hash');
	}
}

new Logout;
