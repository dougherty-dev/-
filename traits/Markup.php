<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Markup {
	private function show_shashin(): void {
		echo <<< EOT
{$this->show_header()}
{$this->show_sidebar()}
{$this->show_footer()}
{$this->show_folio()}
EOT;
	}

	private function show_header(): string {
		$banner_image = file_exists(USERLOGO) ? USERLOGO_HTML : SHASHINLOGO_HTML;
		return <<< EOT
			<div class="grid-header">
				<header>
					<a href="/"><img class="logo" src="$banner_image" alt="Shashin"></a>
				</header>
			</div> <!-- grid-header -->
EOT;
	}

	private function show_footer(): string {
		return <<< EOT
			<div class="grid-footer">
{$this->get_footer()}
			</div> <!-- grid-footer -->
EOT;
	}

	private function show_sidebar(): string {

		return <<< EOT
			<div id="sidebar" class="grid-sidebar">
				<div>
					<p class="sidebar_title">Sets</p>
{$this->sidebar_sets()}				</div>
				<div>
					<p class="sidebar_title">Random</p>
{$this->sidebar_random_image()}
				</div>
				<div>
					<p class="sidebar_title">Pages</p>
{$this->sidebar_pages()}				</div>
				<div>
					<p class="sidebar_title">Admin</p>
{$this->sidebar_admin()}
				</div>
			</div> <!-- grid-sidebar -->
EOT;
	}

	private function sidebar_random_image(): string {
		$stmt = $this->db->instance->query("SELECT * FROM `images` ORDER BY RANDOM() LIMIT 1");
		if ($stmt === FALSE or !is_array($r = $stmt->fetch(PDO::FETCH_ASSOC))) return '';
		$imagepath = imagepath($r['dir'], $r['id'], $r['slug'], IMAGE_TYPES['small'], $r['ext']);
		$pagepath = findpath($r['id'], $r['slug']);
		return <<< EOT
					<img class="link fit" data-link="$pagepath" src="$imagepath">
EOT;
	}

	private function sidebar_sets(): string {
		$this->get_sets();
		$sets = '';
		foreach ($this->set_id as $order => $set_id) {
			if ($this->count_images($set_id) === 0) continue;
			$setpath = findpath($set_id, $this->set_slug[$order], 's');
			$title = mb_strimwidth($this->set_title[$order], 0, 20, '…');
			$sets .= <<< EOT
					<span class="link" data-link="$setpath">$title</span><br>

EOT;
		}

		return $sets;
	}

	private function sidebar_admin(): string {
		if ($this->is_admin()) {
			return <<< EOT
					<p><a href="/admin/"><button>Edit️</button></a>
						<button id="admin_logout">Logout</button></p>
EOT;
		} else {
			return <<< EOT
					<p><input type="password" name="admin_login_password" id="admin_login_password" size="20"></p>
					<p><button id="admin_login_password_submit">Login</button></p>
EOT;
		}
	}

	private function sidebar_pages(): string {
		$this->get_pages();
		$pages = '';
		foreach ($this->page_id as $order => $page_id) {
			$pagepath = findpath($page_id, $this->page_slug[$order], 'p');
			$title = mb_strimwidth($this->page_title[$order], 0, 20, '…');
			$pages .= <<< EOT
					<span class="link" data-link="$pagepath">$title</span><br>

EOT;
		}

		return $pages;
	}
}
