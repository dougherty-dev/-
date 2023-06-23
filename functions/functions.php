<?php declare(strict_types = 1);

function rbytes(int $n): string {
	return bin2hex(random_bytes(max(1, $n)));
}

function in_range(int|float $x, int|float $min, int|float $max): bool {
	return $x >= $min && $x <= $max;
}

function trim_slug(string $slug): string {
	$slug = (string) filter_var($slug, FILTER_SANITIZE_SPECIAL_CHARS);
	$slug = strtolower((string) preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $slug)));
	$slug = preg_replace('/--+/', '-', $slug);
	$slug = trim((string) $slug, '-');
	$slug !== '' or $slug = rbytes(5);
	return $slug;
}

function trim_title(string $title): string {
	$title = (string) filter_var($title, FILTER_SANITIZE_SPECIAL_CHARS);
	$title = trim($title, ' ');
	$title = (string) preg_replace('/\s+/', ' ', $title);
	return $title;
}

function make_check_directory(string $dir): void {
	$dir !== '' && is_dir($dir) or mkdir($dir, PERMISSIONS, recursive: TRUE) or exit('File structure not writable.');
	is_writable($dir) && is_readable($dir) or exit('File structure not accessible.');
}

function empty_folder(string $dir): void {
	if ($dir !== '' && is_real_dir($dir) && is_array($files = scandir($dir))) {
		foreach (array_diff($files, ['.', '..']) as $file) {
			if (is_real_dir($d = "$dir/$file")) empty_folder($d);
			elseif (is_real_file($d)) unlink($d);
		}
		rmdir($dir);
	}
}

function copy_folder(string $src, string $dst): void {
	is_dir($src) or exit;
	$dir = opendir($src) or exit;
	make_check_directory($dst);
	while($file = readdir($dir)) {
		if ($file !== '.' && $file != '..') {
			if (is_real_dir("$src/$file")) copy_folder("$src/$file", "$dst/$file");
			else copy("$src/$file", "$dst/$file");
		}
	}
	closedir($dir);
}

function is_real_dir(string $dir): bool {
	return $dir !== '' && !is_link($dir) && file_exists($dir) && is_dir($dir) && is_writable($dir);
}

function is_real_file(string $file): bool {
	return $file !== '' && !is_link($file) && file_exists($file) && is_file($file) && is_writable($file);
}

function imagepath(string $dir, int $id, string $slug, string $type, string $ext, string $base = USERIMAGES_HTML): string {
	return $base . "/$dir/$id-$slug-$type.$ext";
}

function findpath(int $id, string $slug, string $type = 'i'): string {
	$path = HTML . "/$type/$id-$slug";
	return constant('EXTENSION') ? "$path." . EXTENSION : $path;
}

function encode_text(string $text): string {
	return htmlspecialchars($text);
}

function decode_text(string $text): string {
	return htmlspecialchars_decode($text);
}

function reduced_string(?string $str, int $n = TRIMMED_TITLE_LENGTH): string {
	return $str ? mb_strimwidth($str, 0, $n, '…') : '';
}
