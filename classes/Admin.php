<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

final class Admin {
	use Admin_common;

	public function __construct() {
		$this->secure();
		$this->show_admin();
	}

	private function show_admin_folio(): string {
		return <<< EOT
			<div class="grid-folio">
				<h1>Admin</h1>
				<div>
					<h2>Information</h2>
					<p class="smaller">
						Server: {$_SERVER['HTTP_HOST']}<br>
						Shashin version: {$this->echo(VERSION)} {$this->echo(VERSION_DATE)}<br>
						PHP: {$this->echo(phpversion())} ({$this->echo(PHP_SAPI)})<br>
						GD: {$this->echo(defined("GD_VERSION") ? GD_VERSION : '⚠️')}
						| GD AVIF: {$this->echo(function_exists('imageavif') ? '✅' : '⚠️')}
						| GD WebP: {$this->echo(function_exists('imagewebp') ? '✅' : '⚠️')}<br>
						SQLite3: {$this->echo(class_exists('SQLite3') ? SQLite3::version()['versionString'] : '⚠️')}<br>
						post_max_size: {$this->echo((string) ini_get('post_max_size'))}<br>
						upload_max_filesize: {$this->echo((string) ini_get('upload_max_filesize'))}
						</p>
				</div>
				<hr>
{$this->edit_header()}
{$this->edit_footer()}
{$this->sitemap()}
			</div> <!-- grid-folio -->

EOT;
	}

	private function edit_header(): string {
		$banner_image = file_exists(USERLOGO) ? USERLOGO_HTML : SHASHINLOGO_HTML;
		return <<< EOT
				<div id="banner">
					<h2>Banner image</h2>
					<p><img class="logo" src="$banner_image"></p>
					<p class="delete">❌</p>
					<form id="droplogo" class="dropzone">
						<div class="dz-message" data-dz-message>
							<p class="emoji">⬇️</p>
							<p>AVIF, WebP, PNG, JPEG (2000+ px)</p>
						</div>
					</form>
				</div>
				<hr>
EOT;
	}

	private function edit_footer(): string {
		$footer = $this->get_footer();
		return <<< EOT
				<div id="footer">
					<h2>Footer</h2>
					<p class="delete">❌</p>
					<textarea id="edit-footer" rows="10" cols="40">$footer</textarea>
					<div class="edit-grid-footer">
$footer					</div>
				</div>
				<hr>
EOT;
	}

	private function sitemap(): string {
		return <<< EOT
				<div>
					<h2>Sitemap</h2>
					<button id="generate-sitemap">Generate</button>
				</div>
EOT;
	}

}
