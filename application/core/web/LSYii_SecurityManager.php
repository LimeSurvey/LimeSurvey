<?php
/**
 * LimeSurvey
 * Copyright (C) 2007-2018 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v3 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class LSYii_SecurityManager extends CSecurityManager
{


    /**
     * Generate a random ASCII string. Generates only [0-9a-zA-z_~] characters which are all
     * transparent in raw URL encoding.
     * @param integer $length length of the generated string in characters.
     * @param boolean $cryptographicallyStrong set this to require cryptographically strong randomness.
     * @return string|boolean random string or false in case it cannot be generated.
     * @since 1.1.14
     */
    public function generateRandomString($length,$cryptographicallyStrong=true)
    {
        if(($randomBytes=$this->generateRandomBytes($length+2,$cryptographicallyStrong))!==false)
            return strtr($this->substr(base64_encode($randomBytes),0,$length),array('+'=>'_','/'=>'~'));
        return false;
    }




    /**
    * Create a directory in tmp dir using a random string
    *
    * @param  string $dir      the temp directory (if empty will use the one from configuration)
    * @param  string $prefix   wanted prefix for the directory
    * @param  int    $mode     wanted  file mode for this directory
    * @return string           the path of the created directory
    */
    public function createRandomTempDir($dir=null, $prefix = '', $mode = 0700)
    {

        $sDir = (empty($dir)) ? Yii::app()->getConfig('tempdir') : get_absolute_path ($dir);

        if (substr($sDir, -1) != DIRECTORY_SEPARATOR) {
            $sDir .= DIRECTORY_SEPARATOR;
        }

        do {
            $sRandomString = getRandomString();
            $path = $sDir.$prefix.$sRandomString;
        }
        while (!mkdir($path, $mode));

        return $path;
    }

    /**
     * Generate a random string, using openssl if available, else using md5
     * @param  int    $length wanted lenght of the random string (only for openssl mode)
     * @return string
     */
    public function getRandomString($length=32)
    {

        if ( function_exists('openssl_random_pseudo_bytes') ) {
            $token = "";
            $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
            $codeAlphabet.= "0123456789";
            for($i=0;$i<$length;$i++){
                $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
            }
        }else{
            $token = md5(uniqid(rand(), true));
        }
        return $token;
    }

    /**
     * Get a random number between two values using openssl_random_pseudo_bytes
     * @param  int    $min
     * @param  int    $max
     * @return string
     */
    public function crypto_rand_secure($min, $max)
    {
            $range = $max - $min;
            if ($range < 0) return $min; // not so random...
            $log = log($range, 2);
            $bytes = (int) ($log / 8) + 1; // length in bytes
            $bits = (int) $log + 1; // length in bits
            $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);
            return $min + $rnd;
    }

    /**
     * Test if a given zip file is Zip Bomb
     * see comment here : http://php.net/manual/en/function.zip-entry-filesize.php
     * @param string $zip_filename
     * @return int
     */
    public function isZipBomb($zip_filename)
    {
        return ( get_zip_originalsize($zip_filename) >  getMaximumFileUploadSize() );
    }

    /**
     * Get the original size of a zip archive to prevent Zip Bombing
     * see comment here : http://php.net/manual/en/function.zip-entry-filesize.php
     * @param string $filename
     * @return int
     */
    public function get_zip_originalsize($filename)
    {

        if ( function_exists ('zip_entry_filesize') ){
            $size = 0;
            $resource = zip_open($filename);

            if ( ! is_int($resource) ) {
                while ($dir_resource = zip_read($resource)) {
                    $size += zip_entry_filesize($dir_resource);
                }
                zip_close($resource);
            }

            return $size;
        }else{
            if ( YII_DEBUG ){
                Yii::app()->setFlashMessage("Warning! The PHP Zip extension is not installed on this server. You're not protected from ZIP bomb attacks.", 'error');
            }
        }

        return -1;
    }
    

}
