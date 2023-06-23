<?php declare(strict_types = 1);

define('VERSION', '0.1.0');
define('VERSION_DATE', '2023-06-23' . time());
define('AUTHOR', 'Niklas Dougherty');

define('ERROR_REPORTING', FALSE);

define('BASE', realpath(dirname(__FILE__) . '/..'));
define('DB', BASE . '/db');
define('FUNCTIONS', BASE . '/functions');
define('CLASSES', BASE . '/classes');
define('TRAITS', BASE . '/traits');
define('AJAX', BASE . '/ajax');
define('ADMIN', BASE . '/admin');
define('TOKENS', CLASSES . '/Tokens.php');

define('USERDATA', BASE . '/usr');
define('USERIMAGES', USERDATA . '/img');
define('USERTHEMES', USERDATA . '/thm');
define('UPLOADS', USERDATA . '/upl');
define('USERLOGO', USERIMAGES . '/logo.webp');

define('HTML', '');
define('IMAGES', HTML . '/img');
define('USERIMAGES_HTML', HTML . '/usr/img');
define('USERTHEMES_HTML', HTML . '/usr/thm');
define('USERLOGO_HTML', USERIMAGES_HTML . '/logo.webp');
define('SHASHINLOGO_HTML', IMAGES . '/shashin-logo.webp');

define('PASSWORD_FILE', BASE . '/.htpassword.txt');
define('SITEMAP_FILE', BASE . '/sitemap.xml');
define('ROBOTS_FILE', BASE . '/robots.txt');

define('IMAGE_TYPES', ['thumb' => 't', 'small' => 's', 'medium' => 'm', 'original' => 'o']);
define('SIZES', ['t' => 500, 's' => 1000, 'm' => 2000, 'o' => 2001]);
define('PERMISSIONS', 0700);

define('EXTENSION', 'shashin'); // avoid trailing slash redirects
define('TRIMMED_TITLE_LENGTH', 30);

define('THEME_NAME', 'theme');
define('THEME_FILENAME', THEME_NAME . '.css');
define('DEFAULT_THEME', 'Shashin');

enum Image_format: string {
	case AVIF = 'avif';
	case WebP = 'webp';
	case JPEG = 'jpeg';

	/** @return string[] */
	public function format(): array {
		return match($this) {
			static::AVIF => ['type' => 'AVIF', 'ext' => 'avif'],
			static::WebP => ['type' => 'WebP', 'ext' => 'webp'],
			static::JPEG => ['type' => 'JPEG', 'ext' => 'jpg'],
		};
	}
}

enum Video_format: string {
	case MPEG4 = 'mp4';
	case WebM = 'webm';

	/** @return string[] */
	public function format(): array {
		return match($this) {
			static::MPEG4 => ['type' => 'MPEG4', 'ext' => 'mp4'],
			static::WebM => ['type' => 'WebM', 'ext' => 'webm'],
		};
	}
}
