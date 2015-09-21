<?php
/*
 * This file is part of the Manuel Aguirre Project.
 *
 * (c) Manuel Aguirre <programador.manuel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ku\SsoServerBundle\Security;


/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class UserDataEncrypter
{
    private $key;

    /**
     * UserDataDecrypter constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($data)
    {
        $td = self::getTd();
        $iv = $this->createIv($td);

        $this->init($td, $iv);

        $encripted = mcrypt_generic($td, $data);

        $this->deinit($td);

        return base64_encode($iv . $encripted);
    }

    protected function init($td, $iv)
    {
        $res = mcrypt_generic_init($td, $this->createSecurityKey(), $iv);

        if ($res < 0 || $res === false) {
            throw new \RuntimeException("Couldn't initialize mcrypt");
        }
    }

    protected function deinit($td){
        $res = mcrypt_generic_deinit($td);

        if($res === false){
            throw new \RuntimeException("Mcrypt couldn't be properly deinitialized");
        }

        $res = mcrypt_module_close($td);

        if($res === false){
            throw new \RuntimeException("Mcrypt module couldn't be properly closed");
        }
    }

    protected static function getTd()
    {
        return mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CBC, '');
    }

    private function getIvSize($td)
    {
        return mcrypt_enc_get_iv_size($td);
    }

    private function createIv($td)
    {
        return mcrypt_create_iv($this->getIvSize($td), MCRYPT_RAND);
    }

    private function createSecurityKey()
    {
        $key = $this->key;
        $key2 = md5($key);
        $length = 10;

        $key = substr($key, 0, $length) . substr(strtoupper($key2), (round(strlen($key2) / 2)), $length);

        return substr($key . $key2 . strtoupper($key), 0, $length);
    }
}
