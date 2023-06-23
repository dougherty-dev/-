<?php declare(strict_types = 1);

version_compare(PHP_VERSION, '8.1', '>=') or die('Requires PHP 8.1+');
extension_loaded('sqlite3') or die('Requires SQLite3');

require_once dirname(__FILE__) . '/../functions/constants.php';
require_once FUNCTIONS . '/functions.php';

new Init;

final class Init {
	public function __construct() {
		if (defined('ERROR_REPORTING')) {
			error_reporting(E_ALL);
			ini_set('display_errors', '1');
		} else {
			error_reporting(0);
			ini_set('display_errors', '0');
		}

		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '0');

		mb_internal_encoding('UTF-8');

		set_include_path(implode(PATH_SEPARATOR, [CLASSES, TRAITS, AJAX]));
		spl_autoload_register(function (string $class): void {
			$folders = explode(PATH_SEPARATOR, (string) get_include_path());
			foreach ($folders as $folder) is_file($file = $folder . "/$class.php") and require $file;
		});

		$this->sanity_check();
		session_start();
	}

	private function sanity_check(): void {
		foreach ([BASE, DB, USERDATA, USERIMAGES, USERTHEMES, UPLOADS] as $dir) make_check_directory($dir);
	}

}
