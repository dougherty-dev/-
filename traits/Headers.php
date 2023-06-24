<?php declare(strict_types = 1);

defined('VERSION_DATE') or die();

trait Headers {
	private function init_markup(): void {
		$this->get_themes();
		$this->get_current_theme();
		$theme_filename = '/' . $this->current_theme_slug . '/' . THEME_FILENAME;
		$theme = ($this->current_theme_slug !== '' && is_real_file(USERTHEMES . $theme_filename)) ?
			USERTHEMES_HTML . $theme_filename . "?{$this->current_theme_time}" :
			"/css/shashin.css?{$this->echo(VERSION_DATE)}";

		$admin = '';
		if ($this->is_admin()) $admin = <<< EOT
	<link rel="stylesheet" type="text/css" href="/css/dropzone.css">

EOT;

		echo <<< EOT
<!doctype html>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="token" content="{$_SESSION["token"]}">
	<script src="/js/jquery-3.6.4.min.js"></script>
$admin	<link rel="stylesheet" type="text/css" href="$theme">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<title>写真 {$this->echo(VERSION)}</title>
</head>
<body>
	<main>
		<div class="shashin-grid">

EOT;
	}

	private function end_markup(): void {
		$admin = '';
		if ($this->is_admin()) $admin = <<< EOT
		<script src="/js/dropzone-min.js"></script>
		<script src="/js/admin.js?{$this->echo(VERSION_DATE)}"></script>

EOT;

		echo <<< EOT
		</div> <!-- shashin-grid -->
	</main>
	<footer>
$admin		<script src="/js/functions.js?{$this->echo(VERSION_DATE)}"></script>
	</footer>
</body>
</html>

EOT;
	}
}
