<?php if (!defined('COREPATH')) exit('No direct script access allowed');

// -------------------------------------------------------------
// UTILITY FUNCTIONS
// -------------------------------------------------------------

function file_write($path, $data, $mode = 'a') {
	try {
		$dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }
		
		// Open file, mode a creates file if it doesn't exist
		$fp = fopen($path, $mode);
		// File lock to prevent the same file from being opened simultaneously
		flock($fp, LOCK_EX);

		for ($result = $written = 0, $length = strlen($data); $written < $length; $written += $result) {
			if (($result = fwrite($fp, substr($data, $written))) === false) {
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return is_int($result);
	} catch (Throwable $e) {
		return false;
	}
}