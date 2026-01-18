<?php if (!defined('COREPATH')) exit('No direct script access allowed');

/**
 * Encryption Library
 *
 * Handles encryption and decryption of sensitive data using AES-256-CBC
 */
class Encryption {
    private $cipher = 'AES-256-CBC';
    private $key;

    /**
     * Constructor
     *
     * @throws Exception if encryption key is not set
     */
    public function __construct() {
        $this->key = getenv('ENCRYPTION_KEY');

        if (empty($this->key)) {
            error_log('Security Error: ENCRYPTION_KEY not set in environment');
            throw new Exception('Encryption key not configured');
        }

        // Ensure key is properly sized for AES-256 (32 bytes)
        $this->key = hash('sha256', $this->key, true);
    }

    /**
     * Encrypt data
     *
     * @param string $plaintext Data to encrypt
     * @return string Base64 encoded ciphertext with IV
     * @throws Exception if encryption fails
     */
    public function encrypt($plaintext) {
        if (empty($plaintext)) {
            return '';
        }

        try {
            // Generate random IV
            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = openssl_random_pseudo_bytes($ivLength);

            // Encrypt the data
            $ciphertext = openssl_encrypt(
                $plaintext,
                $this->cipher,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($ciphertext === false) {
                throw new Exception('Encryption failed');
            }

            // Combine IV and ciphertext, then base64 encode
            $encrypted = base64_encode($iv . $ciphertext);

            return $encrypted;
        } catch (Exception $e) {
            error_log('Encryption error: ' . $e->getMessage());
            throw new Exception('Encryption failed');
        }
    }

    /**
     * Decrypt data
     *
     * @param string $encrypted Base64 encoded ciphertext with IV
     * @return string Decrypted plaintext
     * @throws Exception if decryption fails
     */
    public function decrypt($encrypted) {
        if (empty($encrypted)) {
            return '';
        }

        try {
            // Base64 decode
            $data = base64_decode($encrypted, true);

            if ($data === false) {
                throw new Exception('Invalid encrypted data format');
            }

            // Extract IV and ciphertext
            $ivLength = openssl_cipher_iv_length($this->cipher);
            $iv = substr($data, 0, $ivLength);
            $ciphertext = substr($data, $ivLength);

            // Decrypt the data
            $plaintext = openssl_decrypt(
                $ciphertext,
                $this->cipher,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv
            );

            if ($plaintext === false) {
                throw new Exception('Decryption failed');
            }

            return $plaintext;
        } catch (Exception $e) {
            error_log('Decryption error: ' . $e->getMessage());
            throw new Exception('Decryption failed');
        }
    }

    /**
     * Generate a random encryption key
     *
     * @return string Base64 encoded random key
     */
    public static function generateKey() {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }
}
