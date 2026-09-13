<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_crypto {

    protected $privateKey;
    protected $publicKey;

    public function __construct()
    {
        $dir = APPPATH . 'config/payment_keys/';
        $pub = $dir . 'card_public.pem';
        $priv = $dir . 'card_private.pem';
        if (is_file($pub)) {
            $this->publicKey = file_get_contents($pub);
        }
        if (is_file($priv)) {
            $this->privateKey = file_get_contents($priv);
        }
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
        if ($this->privateKey === '' || $ciphertextB64 === '') {
            return null;
        }
        $cipher = base64_decode($ciphertextB64, true);
        if ($cipher === false) {
            return null;
        }
        $plain = '';
        $ok = openssl_private_decrypt($cipher, $plain, $this->privateKey, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) {
            return null;
        }
        $data = json_decode($plain, true);
        return is_array($data) ? $data : null;
    }

    protected function pem_to_b64($pem)
    {
        $pem = preg_replace('/-----BEGIN PUBLIC KEY-----/', '', $pem);
        $pem = preg_replace('/-----END PUBLIC KEY-----/', '', $pem);
        return preg_replace('/\s+/', '', $pem);
    }
}
