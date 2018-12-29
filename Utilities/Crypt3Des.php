<?php
/**
 * Created by IntelliJ IDEA.
 * User: lyq
 * Date: 2016/3/29
 * Time: 20:06
 * 兼容java的3DES(DESede/CBC/PKCS5Padding)加密方式
 */
namespace Kernel\Utilities;

class Crypt3Des
{
    private $key;
    private $iv;

    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }

    public function encrypt($input)
    {


        $data= openssl_encrypt($input, 'des-ede3-cbc', $this->key, 0, $this->iv);
        return $data;

        $text = $input;
        $key = $this->key;
        $iv = str_pad($this->iv, 16, '0');

        $block_size = 32;
        $text_length = strlen($text);
        $amount_to_pad = $block_size - ($text_length % $block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = $block_size;
        }
        $pad_chr = chr($amount_to_pad);
        $tmp = '';
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        $text = $text . $tmp;

        $encrypted = openssl_encrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);
        $encrypt_msg = base64_encode($encrypted);
        return $encrypt_msg;

        // $size = @mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        // $input = $this->pkcs5_pad($input, $size);
        // $key = str_pad($this->key, 24, '0');
        // $td = @mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        // if ($this->iv == '') {
        //     $iv = @mcrypt_create_iv(@mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        // } else {
        //     $iv = $this->iv;
        // }
        // @mcrypt_generic_init($td, $key, $iv);
        // $data = @mcrypt_generic($td, $input);
        // @mcrypt_generic_deinit($td);
        // @mcrypt_module_close($td);
        // $data = base64_encode($data);
        // return $data;
    }

    public function decrypt($encrypted)
    {

        return openssl_decrypt($encrypted, 'des-ede3-cbc', $this->key, 0, $this->iv);

        // $encrypted = base64_decode($encrypted);
        $text = base64_decode($encrypted);
        $key = $this->key;
        $iv = str_pad($this->iv, 16, '0');
        $decrypted = openssl_decrypt($text, 'AES-256-CBC', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);

        // $key = str_pad($this->key, 24, '0');
        // $td = @mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        // if ($this->iv == '') {
        //     $iv = @mcrypt_create_iv(@mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        // } else {
        //     $iv = $this->iv;
        // }
        // $ks = @mcrypt_enc_get_key_size($td);
        // @mcrypt_generic_init($td, $key, $iv);
        // $decrypted = @mdecrypt_generic($td, $encrypted);
        // @mcrypt_generic_deinit($td);
        // @mcrypt_module_close($td);
        $y = $this->pkcs5_unpad($decrypted);
        return $y;
    }

    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    private function PaddingPKCS7($data)
    {
        $block_size = @mcrypt_get_block_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }
}
