<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Database {
	public PDO $instance;

	public function __construct() {
		$this->connect();
	}

	public function connect(): void {
		$this->instance = new PDO('sqlite:' . DB . '/shashin.db');
		$this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->instance->setAttribute(PDO::ATTR_PERSISTENT, TRUE);
		$pragma = 'PRAGMA temp_store = MEMORY; PRAGMA mmap_size = 1000000000; PRAGMA auto_vacuum = FULL; PRAGMA busy_timeout = 5000';
		$this->instance->exec($pragma);
	}

	public function close_db(): void {
		unset($this->instance);
		$this->connect();
	}

	public function get_preference(string $name): string {
		$value = '';
		$stmt = $this->instance->prepare("SELECT `value` FROM `preferences` WHERE `name`=:name LIMIT 1");
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->bindColumn('value', $value, PDO::PARAM_STR);
		$stmt->execute();
		$stmt->fetch(PDO::FETCH_OBJ);
		$stmt->closeCursor();
		return $value;
	}

	public function save_preference(string $name, string $value): void {
		$stmt = $this->instance->prepare("REPLACE INTO `preferences` (`name`, `value`) VALUES (:name, :value)");
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->bindValue(':value', $value, PDO::PARAM_STR);
		$stmt->execute();
	}

	public function delete_preference(string $name): void {
		$stmt = $this->instance->prepare("DELETE FROM `preferences` WHERE `name`=:name");
		$stmt->bindValue(':name', $name, PDO::PARAM_STR);
		$stmt->execute();
	}
}
