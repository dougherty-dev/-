<?php declare(strict_types = 1);

require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/../classes/Init.php';
require_once TOKENS;

class Modify_admin {
	use Common, Ajax_common;

	public function __construct() {
		$this->init();
		match (TRUE) {
			isset($_POST['footer']) => $this->modify_footer(),
			isset($_POST['delete_footer']) => $this->delete_footer(),
			isset($_POST['delete_banner']) => $this->delete_banner(),
			isset($_POST['generate_sitemap']) => $this->generate_sitemap(),
			default => NULL
		};
	}

	private function modify_footer(): void {
		$this->db->save_preference('footer', decode_text($_POST['footer']));

		echo <<< EOT
					<h2>Footer</h2>
					<textarea id="edit_footer" rows="10" cols="50">{$_POST['footer']}</textarea>
					<div class="edit-grid-footer">
{$_POST['footer']}					</div>
EOT;
	}

	private function delete_footer(): void {
		$this->db->save_preference('footer', '');
	}

	private function delete_banner(): void {
		is_real_file(USERLOGO) and unlink(USERLOGO);
	}

	private function generate_sitemap(): void {
		$host = $_SERVER['HTTP_HOST'];
		$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' ? 'https' : 'http';
		$site = "$protocol://$host";

		$sitemap = <<< EOT
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
	xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
	<url>
		<loc>$site/</loc>
	</url>

EOT;

		$stmt = $this->db->instance->prepare('SELECT `id`, `slug` FROM `pages`');
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$path = findpath($r['id'], $r['slug'], 'p');
			$sitemap .= <<< EOT
	<url>
		<loc>$site$path</loc>
	</url>

EOT;
		}

		$stmt = $this->db->instance->prepare('SELECT `id`, `slug` FROM `sets`');
		$stmt->execute();
		if ($stmt !== FALSE) foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$path = findpath($r['id'], $r['slug'], 's');
			$sitemap .= <<< EOT
	<url>
		<loc>$site$path</loc>

EOT;

			$stmt2 = $this->db->instance->prepare('SELECT `dir`, `id`, `slug`, `ext`, `attachment`, `title` FROM `images` WHERE `set`=:set');
			$stmt2->bindValue(':set', $r['id'], PDO::PARAM_INT);
			$stmt2->execute();
			if ($stmt2 !== FALSE) foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $s) {
				$title = $s['title'] ? htmlspecialchars($s['title'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1) : '';
				foreach (IMAGE_TYPES as $t) {
					$path = imagepath($s['dir'], $s['id'], $s['slug'], $t, $s['ext']);
					$sitemap .= <<< EOT
		<image:image>
			<image:loc>$site$path</image:loc>
			<image:caption>$title</image:caption>
		</image:image>

EOT;
				}

				if ($s['attachment'] === 'mp4' || $s['attachment'] === 'webm') {
					$path = imagepath($s['dir'], $s['id'], $s['slug'], 'a', $s['attachment']);
					$sitemap .= <<< EOT
		<video:video>
			<video:content_loc>$site$path</video:content_loc>
			<video:title>$title</video:title>
		</video:video>

EOT;
				} elseif ($s['attachment'] === 'pdf') {
					$path = imagepath($s['dir'], $s['id'], $s['slug'], 'a', $s['attachment']);
					$sitemap .= <<< EOT
		<image:image>
			<image:loc>$site$path</image:loc>
			<image:caption>$title</image:caption>
		</image:image>

EOT;
				}
			}

			$sitemap .= <<< EOT
	</url>

EOT;
		}

		$sitemap .= <<< EOT
</urlset>

EOT;

		$robots = <<< EOT
User-agent: *
Allow: /

Sitemap: $site/sitemap.xml
EOT;

		file_put_contents(SITEMAP_FILE, $sitemap);
		file_exists(ROBOTS_FILE) or file_put_contents(ROBOTS_FILE, $robots);
	}

}

new Modify_admin;
