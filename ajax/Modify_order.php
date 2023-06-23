<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Modify_order {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		$this->check_post_id() and match (TRUE) {
			isset($_POST['next_id']) => $this->down(),
			isset($_POST['prev_id']) => $this->up(),
			default => NULL
		};
	}

	private function up(): void {
		$prev_id = (int) filter_var($_POST['prev_id'], FILTER_VALIDATE_INT);
		$prev_id > 0 and $this->swap_order($this->post_id, $prev_id, $_POST['table']);
	}

	private function down(): void {
		$next_id = (int) filter_var($_POST['next_id'], FILTER_VALIDATE_INT);
		$next_id > 0 and $this->swap_order($next_id, $this->post_id, $_POST['table']);
	}

}

new Modify_order;
