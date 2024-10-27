<?php
defined('ABSPATH') or die('No script kiddies please!');

class AdScout_Encrypt_Decrypt
{


    /**
     * Encrypt code using OpenSSL
     *
     * @param string $code The code to encrypt.
     */
    public function encrypt($code)
    {
        if (empty($code)) {
            return '';
        }

        $encryption_key = base64_decode(SECURE_AUTH_KEY);
        $iv = substr(openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc')), 0, 16);
        if (strlen($iv) < 16 || strlen($iv) > 16) {
            $iv = str_pad($iv, 16, '0');
        }
        $encrypted = openssl_encrypt($code, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);

        // Append the $iv variable to use for decrypting later.
        return base64_encode($encrypted . '||::||' . $iv);
    }

    /**
     * Decrypt code using OpenSSL
     *
     * @param string $code The code to decrypt.
     */
    public static function decrypt($code)
    {
        $encryption_key = base64_decode(SECURE_AUTH_KEY);
        $code = base64_decode($code);

        if(!str_contains($code, '||::||') and substr_count($code, '::') > 1) {

            $parts = explode('::', $code);
            $last = array_pop($parts);
            $parts = array(implode('::', $parts), $last);
            $encrypted_data = $parts[0];
            $iv = $parts[1];
        }

        if(!str_contains($code, '||::||') and substr_count($code, '::') <= 1) {

            $parts = explode('::', $code);
            $encrypted_data = $parts[0];
            $iv = $parts[1];
        }


        if(str_contains($code, '||::||')) {
            list($encrypted_data, $iv) = explode('||::||', $code, 2);
        }

        // Grab the $iv from earlier, to decrypt.
        if (strlen($iv) < 16 || strlen($iv) > 16) {
            $iv = str_pad($iv, 16, '0');
        }

        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, OPENSSL_RAW_DATA, $iv);
    }

}
