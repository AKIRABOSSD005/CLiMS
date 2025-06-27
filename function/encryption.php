<?php
define('ENCRYPTION_KEY', 'XDT-YUGHH-GYGF-YUTY-GHRGFR'); // Replace with a strong key

// Encrypt data function
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encryptedData . '::' . base64_encode($iv)); // Return encrypted data with IV
}

// Decrypt data function
function decryptData($data, $key) {
    if (empty($data)) {
        echo "No data provided for decryption.<br>";
        return false;
    }

    $decodedData = base64_decode($data);
    if ($decodedData === false) {
        echo "Base64 decoding failed.<br>";
        return false;
    }

    $parts = explode('::', $decodedData, 2);
    if (count($parts) !== 2) {
        echo "Decryption failed: Data format is invalid (expected encryptedData::iv).<br>";
        return false;
    }

    $encryptedData = $parts[0];
    $iv = base64_decode($parts[1]); // Decode IV from base64
    if ($iv === false) {
        echo "IV decoding failed.<br>";
        return false;
    }

    $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
    if ($decrypted === false) {
        echo "Decryption failed at OpenSSL level.<br>";
        return false;
    }

    return $decrypted;
}
?>
