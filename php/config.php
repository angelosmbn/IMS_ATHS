<?php
// Encryption key (should be kept secret)
$key = "assumpta_secret_key";

// Function to encrypt data
function encryptData($data, $key) {
    // Generate an initialization vector (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    
    // Encrypt the data using AES-256-CBC algorithm
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    
    // Concatenate the IV with the encrypted data
    $encryptedData = base64_encode($iv . $encrypted);
    
    return $encryptedData;
}

// Function to decrypt data
function decryptData($encryptedData, $key) {
    // Decode the base64-encoded data
    $data = base64_decode($encryptedData);
    
    // Extract the IV from the decoded data
    $iv = substr($data, 0, openssl_cipher_iv_length('aes-256-cbc'));
    
    // Extract the encrypted data (excluding the IV)
    $encrypted = substr($data, openssl_cipher_iv_length('aes-256-cbc'));
    
    // Decrypt the data using AES-256-CBC algorithm
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    
    return $decrypted;
}
?>

