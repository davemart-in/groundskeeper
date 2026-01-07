<?php if (!defined('COREPATH')) exit('No direct script access allowed');

function encrypt_string($plaintext) {
    $key = getenv('OAUTH_ENCRYPTION_KEY');
    if (!$key) return $plaintext;
    $cipher = 'aes-256-gcm';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $tag = ''; // Will be filled by openssl_encrypt
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
    return base64_encode($iv . $tag . $ciphertext_raw);
}

function decrypt_string($ciphertext) {
    $key = getenv('OAUTH_ENCRYPTION_KEY');
    if (!$key) return $ciphertext;
    $data = base64_decode($ciphertext);
    $cipher = 'aes-256-gcm';
    $ivlen = openssl_cipher_iv_length($cipher);
    $taglen = 16; // GCM tag is always 16 bytes
    $iv = substr($data, 0, $ivlen);
    $tag = substr($data, $ivlen, $taglen);
    $ciphertext_raw = substr($data, $ivlen + $taglen);
    return openssl_decrypt($ciphertext_raw, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
}
