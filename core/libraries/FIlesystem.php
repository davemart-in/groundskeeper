<?php if (!defined('COREPATH')) exit('No direct script access allowed');

function filesystem_directory_map($sourceDir, $directoryDepth = 0) {
	try {
		
		if (!is_dir($sourceDir)) {
			return false;
		}

		$fp = opendir($sourceDir);

		$fileData  = [];
		$newDepth  = $directoryDepth - 1;
		$sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		while (false !== ($file = readdir($fp))) {
			// Remove '.', '..'
			if ($file === '.' || $file === '..') {
				continue;
			}

			if (is_dir($sourceDir . $file)) {
				$file .= DIRECTORY_SEPARATOR;
			}

                        if (($directoryDepth < 1 || $newDepth > 0) && is_dir($sourceDir . $file)) {
                                $fileData[$file] = filesystem_directory_map($sourceDir . $file, $newDepth);
			} else {
				$fileData[] = $file;
			}
		}

		closedir($fp);

		return $fileData;
	} catch (Throwable $e) {
		return [];
	}
}

function filesystem_filesize($log_file) {
	$size_in_bytes = filesize($log_file);
	$size_in_kb    = $size_in_bytes / 1024;
	$size_in_megabytes = $size_in_bytes / 1024 / 1024;
	// If less than 1kb, return in bytes
	if ($size_in_kb < 1) {
		return $size_in_bytes . ' B';
	}
	// If less than 1mb, return in kb
	if ($size_in_megabytes < 1) {
		return round($size_in_kb, 2) . ' KB';
	}
	// Else return in mb
	return round($size_in_megabytes, 2) . ' MB';
}