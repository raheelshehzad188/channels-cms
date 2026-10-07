<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_crypto {

    protected $privateKey;
    protected $publicKey;
    protected $lastError = '';

    public function __construct()
    {
        $dir = APPPATH . 'config/payment_keys/';
        $pub = $dir . 'card_public.pem';
        $priv = $dir . 'card_private.pem';
        if (is_file($pub)) {
            $this->publicKey = (string) file_get_contents($pub);
        }
        if (is_file($priv)) {
            if (!is_readable($priv)) {
                $this->lastError = 'Card private key is not readable.';
            } else {
                $this->privateKey = (string) file_get_contents($priv);
            }
        } else {
            $this->lastError = 'Card private key file is missing.';
        }
    }

    public function last_error()
    {
        return $this->lastError;
    }

    public function public_key_pem()
    {
        return $this->publicKey ?: '';
    }

    public function public_key_spki_base64()
    {
        $pem = $this->public_key_pem();
        if ($pem === '') {
            return '';
        }
        $key = openssl_pkey_get_public($pem);
        if (!$key) {
            return '';
        }
        $details = openssl_pkey_get_details($key);
        return !empty($details['key']) ? $this->pem_to_b64($details['key']) : '';
    }

    public function decrypt_payload($ciphertextB64)
    {
        if ($this->privateKey === '' || $this->privateKey === null) {
            if ($this->lastError === '') {
                $this->lastError = 'Card private key is empty.';
            }
            return null;
        }
        if ($ciphertextB64 === '') {
            $this->lastError = 'Encrypted card payload is empty.';
            return null;
        }
        $cipher = base64_decode($ciphertextB64, true);
        if ($cipher === false) {
            $this->lastError = 'Encrypted card payload is not valid base64.';
            return null;
        }
        $plain = '';
        $ok = openssl_private_decrypt($cipher, $plain, $this->privateKey, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) {
            $this->lastError = 'Card decrypt failed: ' . (openssl_error_string() ?: 'openssl_private_decrypt');
            return null;
        }
        $data = json_decode($plain, true);
        if (!is_array($data)) {
            $this->lastError = 'Decrypted card payload is not JSON.';
            return null;
        }
        return $data;
    }

    protected function pem_to_b64($pem)
    {
        $pem = preg_replace('/-----BEGIN PUBLIC KEY-----/', '', $pem);
        $pem = preg_replace('/-----END PUBLIC KEY-----/', '', $pem);
        return preg_replace('/\s+/', '', $pem);
    }
}
