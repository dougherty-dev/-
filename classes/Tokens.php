<?php declare(strict_types = 1);

final class Tokens {
	public function __construct() {
		isset($_SESSION['token'], $_POST['token']) or exit('No session or token');
		$_SESSION['token'] === $_POST['token'] or exit('Wrong token');
	}
}

new Tokens;
