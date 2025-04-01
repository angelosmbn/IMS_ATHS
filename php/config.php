<?php
$encryptionKey = 'A$s3cr3t&StR0ngK3y!';
// Function to encrypt data
function encryptVariable($data, $encryptionKey) {
    // Generate a random initialization vector (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Encrypt using AES-256-CBC (Cipher Block Chaining)
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

    // Concatenate IV and encrypted data
    $encryptedData = base64_encode($iv . $encrypted);

    return $encryptedData;
}

// Function to decrypt data
function decryptVariable($encryptedData, $encryptionKey) {
    // Decode the base64-encoded data
    $data = base64_decode($encryptedData);

    // Extract IV and encrypted data
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));

    // Decrypt using AES-256-CBC
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);

    return $decrypted;
}


/*
$encryptionKey = 'A$s3cr3t&StR0ngK3y!';

function encryptVariable($data, $encryptionKey) {
    // Generate a random initialization vector (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-gcm'));

    // Encrypt using AES-256-GCM (Galois Counter Mode)
    $encrypted = openssl_encrypt($data, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);

    // Concatenate IV and tag with encrypted data
    $encryptedData = base64_encode($iv . $tag . $encrypted);

    return $encryptedData;
}

function decryptVariable($encryptedData, $encryptionKey) {
    // Decode the base64-encoded data
    $data = base64_decode($encryptedData);

    // Extract IV, tag, and encrypted data
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-gcm'));
    $tag = substr($data, openssl_cipher_iv_length('aes-256-gcm'), 16);
    $encrypted = substr($data, 16 + openssl_cipher_iv_length('aes-256-gcm'));

    // Decrypt using AES-256-GCM
    $decrypted = openssl_decrypt($encrypted, 'aes-256-gcm', $encryptionKey, OPENSSL_RAW_DATA, $iv, $tag);

    // Check if decryption was successful
    if ($decrypted !== false) {
        return $decrypted;
    } else {
        // Handle decryption failure (e.g., log an error)
        return false;
    }
}
function encryptVariable($data) {
    // Use a simple XOR encryption (not very secure, but easy to implement)
    $encryptedData = "";
    for ($i = 0; $i < strlen($data); $i++) {
        $encryptedData .= chr(ord($data[$i]) ^ 123); // Adjust the XOR key as needed
    }
    return $encryptedData;
}

function decryptVariable($encryptedData) {
    // Use the same XOR key as in encryption
    $decryptedData = "";
    for ($i = 0; $i < strlen($encryptedData); $i++) {
        $decryptedData .= chr(ord($encryptedData[$i]) ^ 123);
    }
    return $decryptedData;
}*/
?>

