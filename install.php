<?php
$source = __DIR__ . "/lib/*";
$dest = getcwd() . "/lib";

if (!file_exists($dest)) {
	echo sprintf("Invalid directory called. Dir not exists! '%s'\n", $dest);
	exit(1);
}

foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST) as $item) {
	if ($item->isDir()) {
		mkdir($dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
	} else {
		copy($item, $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname());
	}
}
