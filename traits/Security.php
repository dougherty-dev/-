<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Security {
	private function credentials(): void {
		$this->init();
		$this->tokenizer();
		$this->check_credentials();
	}

	private function tokenizer(): void {
		$_SESSION["token"] = rbytes(64);
	}

	private function check_credentials(): void {
		$_SESSION['admin'] = isset($_COOKIE['admin']) &&
			hash_equals(hash('sha256', $_COOKIE['admin']),
				$this->db->get_preference('validator_hash'));
	}

	private function require_admin(): void {
		if (!$this->is_admin()) {
			header("Location: /");
			exit;
		}
	}

	private function secure(): void {
		$this->credentials();
		$this->require_admin();
	}
}
