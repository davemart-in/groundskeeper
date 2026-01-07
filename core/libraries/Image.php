<?php if (!defined('APPPATH')) exit('No direct script access allowed');

/**
 * Validate URL to prevent SSRF attacks
 * Blocks internal IPs, local addresses, and non-HTTP(S) protocols
 */
function image_validate_url($url) {
	// Validate URL format
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return false;
	}
	
	// Parse URL components
	$parsed = parse_url($url);
	
	// Only allow HTTP and HTTPS protocols
	if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
		return false;
	}
	
	// Block if no host
	if (!isset($parsed['host'])) {
		return false;
	}
	
	$host = $parsed['host'];
	
	// Block localhost and local addresses
	$blocked_hosts = ['localhost', '127.0.0.1', '0.0.0.0', '::1'];
	if (in_array(strtolower($host), $blocked_hosts)) {
		return false;
	}
	
	// Get IP address from hostname
	$ip = gethostbyname($host);
	
	// If hostname couldn't be resolved, block it
	if ($ip === $host && !filter_var($ip, FILTER_VALIDATE_IP)) {
		return false;
	}
	
	// Block private and reserved IP ranges
	if (filter_var($ip, FILTER_VALIDATE_IP)) {
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			return false;
		}
	}
	
	// Block common internal cloud metadata endpoints
	$blocked_ips = ['169.254.169.254', '168.63.129.16'];
	if (in_array($ip, $blocked_ips)) {
		return false;
	}
	
	return true;
}

function image_check($url) {
	// Validate URL to prevent SSRF
	if (!image_validate_url($url)) {
		return false;
	}
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Prevent redirects to internal URLs
	curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Add timeout
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS | CURLPROTO_HTTP); // Only allow HTTP(S)
	curl_exec($ch);
	// 400 -> not found, $retcode = 200, found.
	$http_code = (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) ? true : false;
	curl_close($ch);
	return $http_code;
}

function image_convert($image, $path='', $name='') {
	// Validate input
	if (!is_array($image)) {
		return ['error' => 'Input must be an array containing image information'];
	}

	if (!isset($image['type']) || !isset($image['tmp_name'])) {
		return ['error' => 'Image array must contain "type" and "tmp_name" keys'];
	}

	if (!$path) {
		$path = sys_get_temp_dir();
	}
	if (!$name) {
		$name = uniqid('converted_', true) . '.jpg';
	}
	$imageType = $image['type'];
		if (DEBUG) { debug($imageType, "image_convert() imageType"); }
	$imagePath = $image['tmp_name']; // Assuming this is the path to the uploaded file
		if (DEBUG) { debug($imagePath, "image_convert() imagePath"); }
	$convertedImagePath = rtrim($path, '/') . '/' . $name;
		if (DEBUG) { debug($convertedImagePath, "image_convert() convertedImagePath"); }

	try {
		$sourceImage = false;
		
		switch ($imageType) {
			case 'image/avif':
				// AVIF to JPEG conversion
				$sourceImage = @imagecreatefromavif($imagePath);
				if ($sourceImage === false) {
					return ['error' => 'Failed to create image from AVIF file'];
				}
				if (!@imagejpeg($sourceImage, $convertedImagePath, 90)) {
					imagedestroy($sourceImage);
					return ['error' => 'Failed to save converted JPEG file'];
				}
				imagedestroy($sourceImage);
				break;
			case 'image/webp':
				// WEBP to JPEG conversion
				$sourceImage = @imagecreatefromwebp($imagePath);
				if ($sourceImage === false) {
					return ['error' => 'Failed to create image from WEBP file'];
				}
				if (!@imagejpeg($sourceImage, $convertedImagePath, 90)) {
					imagedestroy($sourceImage);
					return ['error' => 'Failed to save converted JPEG file'];
				}
				imagedestroy($sourceImage);
				break;
			case 'image/png':
				// PNG to JPEG conversion
				$sourceImage = @imagecreatefrompng($imagePath);
				if ($sourceImage === false) {
					return ['error' => 'Failed to create image from PNG file - file may be corrupted or invalid'];
				}
				if (!@imagejpeg($sourceImage, $convertedImagePath, 90)) {
					imagedestroy($sourceImage);
					return ['error' => 'Failed to save converted JPEG file'];
				}
				imagedestroy($sourceImage);
				break;
			default:
				// Unsupported image type
				return ['error' => 'Unsupported image type: ' . $imageType];
		}
	} catch (Exception $e) {
		if ($sourceImage) {
			imagedestroy($sourceImage);
		}
		return ['error' => 'Exception during conversion: ' . $e->getMessage()];
	}

	// Check if the file exists and determine its size
	$fileExists = file_exists($convertedImagePath);
		if (DEBUG) { debug($fileExists, "image_convert() fileExists"); }
	$fileSize = $fileExists ? filesize($convertedImagePath) : 0;
		if (DEBUG) { debug($fileSize, "image_convert() fileSize"); }

	$image_array = array(
		'name' => basename($convertedImagePath),
		'full_path' => realpath($convertedImagePath),
		'type' => 'image/jpeg',
		'tmp_name' => $convertedImagePath,
		'error' => $fileExists ? 0 : 1,
		'size' => $fileSize
	);
		if (DEBUG) { debug($image_array, "image_convert() image_array"); }

	return $image_array;
}

function image_gravatar_url($email) {
	return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=492&d=404';
}

function image_gravatar_save($email, $size = 492) {
	// Validate size parameter
	$size = intval($size);
	if ($size < 1 || $size > 2048) {
		$size = 492;
	}
	
	// Get gravatar URL
	$gravatar_url = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?s=' . $size . '&d=404';
	
	// Validate URL (even though we construct it, good practice)
	if (!image_validate_url($gravatar_url)) {
		return false;
	}
	
	// Check if gravatar exists (returns 404 if no gravatar)
	$ch = curl_init($gravatar_url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
	curl_exec($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	// If no gravatar found, return false
	if ($http_code != 200) {
		return false;
	}
	
	// Download gravatar image with safety measures
	$context = stream_context_create([
		'http' => [
			'timeout' => 5,
			'follow_location' => 0,
			'max_redirects' => 0
		],
		'ssl' => [
			'verify_peer' => true,
			'verify_peer_name' => true
		]
	]);
	
	$image_data = @file_get_contents($gravatar_url, false, $context);
	if ($image_data === false) {
		return false;
	}
	
	// Create temporary file
	$temp_file = tempnam(sys_get_temp_dir(), 'gravatar_');
	if (!$temp_file) {
		return false;
	}
	
	// Save image data to temp file
	if (file_put_contents($temp_file, $image_data) === false) {
		unlink($temp_file);
		return false;
	}
	
	// S3 key for user avatar
	$s3_key = $_SESSION['username'] . '/avatar.jpg';
	
	// Upload to S3
	$result = s3Upload($temp_file, S3BUCKET, $s3_key);
	
	// Clean up temp file
	unlink($temp_file);
	
	return $result !== false;
}

function image_upload($project_id, $name, $tmp_name, $stage_id = null) {
	global $hashid;

	if (DEBUG) { debug($tmp_name, '$tmp_name'); }
	
	// Generate S3 key: projects/{company_id}/{project_id_hash}/{name}.jpg
	$project_id_hash = $hashid->encode($project_id);
	if ($stage_id) {
		$s3_key = ENV . '/' . $_SESSION['company_id'] . '/' . $project_id_hash . '/' . $stage_id . '/' . $name . '.jpg';
	} else {
		$s3_key = ENV . '/' . $_SESSION['company_id'] . '/' . $project_id_hash . '/' . $name . '.jpg';
	}
	
	// Handle case where $tmp_name is an array (from $_FILES)
	$tmp_file = is_array($tmp_name) ? $tmp_name : ['tmp_name' => $tmp_name];
	
	if (DEBUG) { debug($tmp_file, '$tmp_file'); debug($s3_key, '$s3_key'); }

	// Upload to S3 using existing function
	$result = s3Upload($tmp_file, S3BUCKET, $s3_key);

	return $result !== false;
}

function image_screenshot_with_hotspot($screenshot_path, $naturalWidth, $naturalX, $naturalY, $output_path = '') {
        if (!$screenshot_path) {
                return false;
        }

        if (!$output_path) {
                $output_path = sys_get_temp_dir() . '/' . uniqid('hotspot_', true) . '.png';
        }

        // Load screenshot - support local paths and URLs
        if (filter_var($screenshot_path, FILTER_VALIDATE_URL)) {
                // Validate URL to prevent SSRF
                if (!image_validate_url($screenshot_path)) {
                        return false;
                }
                
                // Download with safety measures
                $context = stream_context_create([
                        'http' => [
                                'timeout' => 10,
                                'follow_location' => 0,
                                'max_redirects' => 0
                        ],
                        'ssl' => [
                                'verify_peer' => true,
                                'verify_peer_name' => true
                        ]
                ]);
                
                $image_data = @file_get_contents($screenshot_path, false, $context);
                if ($image_data === false) {
                        return false;
                }
                $image = @imagecreatefromstring($image_data);
        } else {
                // For local files, ensure path is within allowed directories
                $real_path = realpath($screenshot_path);
                if (!$real_path || !file_exists($real_path)) {
                        return false;
                }
                
                // Additional check: ensure file is actually an image
                $image_info = @getimagesize($real_path);
                if ($image_info === false) {
                        return false;
                }
                
                $image = @imagecreatefromstring(file_get_contents($real_path));
        }

        if (!$image) {
                return false;
        }

        // Calculate hotspot position
        $width = imagesx($image);
        $height = imagesy($image);
        $ratio = $naturalWidth > 0 ? $width / $naturalWidth : 1;
        $x = intval(round($naturalX * $ratio));
        $y = intval(round($naturalY * $ratio));

        // Prepare image for alpha blending
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Use the same blue circle style as multiple hotspots
        $glowColor = imagecolorallocatealpha($image, 0, 196, 255, 80);
        imagefilledellipse($image, $x, $y, 100, 100, $glowColor);
        
        $border = imagecolorallocate($image, 255, 255, 255);
        imagefilledellipse($image, $x, $y, 50, 50, $border);
        
        // Use blue color for the hotspot center
        $fill = imagecolorallocate($image, 0, 196, 255);
        imagefilledellipse($image, $x, $y, 40, 40, $fill);

        // Save png
        imagepng($image, $output_path);
        imagedestroy($image);

        return $output_path;
}

function image_screenshot_with_multiple_hotspots($screenshot_path, $hotspots, $output_path = '') {
        if (!$screenshot_path || !is_array($hotspots) || empty($hotspots)) {
                return false;
        }
        if (!$output_path) {
                $output_path = sys_get_temp_dir() . '/' . uniqid('hotspots_', true) . '.png';
        }
        
        // Load screenshot - support data URLs, local paths and URLs
        if (strpos($screenshot_path, 'data:') === 0) {
                // Handle data URL
                $parts = explode(',', $screenshot_path);
                if (count($parts) !== 2) {
                        return false;
                }
                $image_data = base64_decode($parts[1]);
                if ($image_data === false) {
                        return false;
                }
                $image = @imagecreatefromstring($image_data);
        } else if (filter_var($screenshot_path, FILTER_VALIDATE_URL)) {
                // Validate URL to prevent SSRF
                if (!image_validate_url($screenshot_path)) {
                        return false;
                }
                
                // Download with safety measures
                $context = stream_context_create([
                        'http' => [
                                'timeout' => 10,
                                'follow_location' => 0,
                                'max_redirects' => 0
                        ],
                        'ssl' => [
                                'verify_peer' => true,
                                'verify_peer_name' => true
                        ]
                ]);
                
                $image_data = @file_get_contents($screenshot_path, false, $context);
                if ($image_data === false) {
                        return false;
                }
                $image = @imagecreatefromstring($image_data);
        } else {
                // For local files, ensure path is within allowed directories
                $real_path = realpath($screenshot_path);
                if (!$real_path || !file_exists($real_path)) {
                        return false;
                }
                
                // Additional check: ensure file is actually an image
                $image_info = @getimagesize($real_path);
                if ($image_info === false) {
                        return false;
                }
                
                $image = @imagecreatefromstring(file_get_contents($real_path));
        }
        if (!$image) {
                return false;
        }
        
        // Get image dimensions
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Prepare image for alpha blending
        imagealphablending($image, true);
        imagesavealpha($image, true);
        
        // Draw each hotspot
        foreach ($hotspots as $index => $hotspot) {
                // Convert percentage coordinates to pixels
                $x = intval(round(($hotspot['x'] / 100) * $width));
                $y = intval(round(($hotspot['y'] / 100) * $height));
                
                // Draw circles at half the previous size
                $glowColor = imagecolorallocatealpha($image, 0, 196, 255, 80);
                imagefilledellipse($image, $x, $y, 100, 100, $glowColor);
                
                $border = imagecolorallocate($image, 255, 255, 255);
                imagefilledellipse($image, $x, $y, 50, 50, $border);
                
                // Use blue color for the hotspot center
                $fill = imagecolorallocate($image, 0, 196, 255);
                imagefilledellipse($image, $x, $y, 40, 40, $fill);
        }
        
        // Save png
        imagepng($image, $output_path);
        imagedestroy($image);
        
        return $output_path;
}