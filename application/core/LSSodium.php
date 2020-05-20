<?php
class LSSodium
{
    public $bLibraryExists = false;
    protected $sEncryptionKeypair = null;
    protected $sEncryptionPublicKey = null;
    protected $sEncryptionSecretKey = null;

    public function init()
    {
        require_once APPPATH.'/third_party/sodium_compat/src/Compat.php';
        require_once APPPATH.'/third_party/sodium_compat/src/Core/Util.php';
        require_once APPPATH.'/third_party/sodium_compat/autoload.php';

        $this->checkIfLibraryExists();

        if ($this->bLibraryExists === false) {
            /*throw new SodiumException(sprintf(gT("This operation uses data encryption functions which require Sodium library to be installed, but library was not found. If you don't want to use data encryption, you have to disable encryption in attribute settings. Here is a link to the manual page:
            %s", 'unescaped'), 'https://manual.limesurvey.org/Data_encryption#Errors'));*/
        } else {
            $this->checkIfKeyExists();
        }
    }
    
    /**
     * Check if Sodium library is installed
     * @return bool
     */
    public function checkIfLibraryExists()
    {
        if (function_exists('sodium_crypto_sign_open')) {
            $this->bLibraryExists = true;
        }
    }

    /**
     *
     * Check if encryption key exists in configuration
     * @return bool Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or inpyt encryptedString
     */
    protected function checkIfKeyExists()
    {
        if (empty(Yii::app()->getConfig('encryptionkeypair'))) {
            $this->generateEncryptionKeys(); //return false;
        }
        if ($this->sEncryptionKeypair === null) {
            $this->sEncryptionKeypair = $this->getEncryptionKey();
        }
        if ($this->sEncryptionPublicKey === null) {
            $this->sEncryptionPublicKey = $this->getEncryptionPublicKey();
        }
        if ($this->sEncryptionSecretKey === null) {
            $this->sEncryptionSecretKey = $this->getEncryptionSecretKey();
        }
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     */
    protected function getEncryptionKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionkeypair'));
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     */
    protected function getEncryptionPublicKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionpublickey'));
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     */
    protected function getEncryptionSecretKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionsecretkey'));
    }
    
    /**
     * Encrypt input data using AES256 CBC encryption
     * @param unknown_type $sDataToEncrypt Data to encrypt. Could be a string or a serializable PHP object
     * @return string Return encrypted AES256 CBC value
     */
    public function encrypt($sDataToEncrypt)
    {
        if ($this->bLibraryExists === true) {
            if (!empty($sDataToEncrypt)) {
                $sEncrypted = base64_encode(ParagonIE_Sodium_Compat::crypto_sign((string) $sDataToEncrypt, $this->sEncryptionSecretKey));
                return $sEncrypted;
            } else {
                return '';
            }
        } else {
            return $sDataToEncrypt;
        }
    }
 
    /**
     *
     * Decrypt encrypted string.
     * @param string $sEncryptedString Encrypted string to decrypt
     * @param bool $bReturnFalseIfError false by default. If TRUE, return false in case of error (bad decryption). Else, return given $encryptedInput value
     * @return string Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or inpyt encryptedString
     */
    public function decrypt($sEncryptedString, $bReturnFalseIfError = false)
    {
        if ($this->bLibraryExists === true) {
            if (!empty($sEncryptedString) && $sEncryptedString != 'null') {
                $plaintext = ParagonIE_Sodium_Compat::crypto_sign_open(base64_decode($sEncryptedString), $this->sEncryptionPublicKey);
                if ($plaintext === false) {
                    throw new SodiumException(sprintf(gT("Wrong decryption key! Decryption key has changed since this data were last saved, so data can't be decrypted. Please consult our manual at %s.", 'unescaped'), 'https://manual.limesurvey.org/Data_encryption#Errors'));
                } else {
                    return $plaintext;
                }
            }
        } else {
            return $sEncryptedString;
        }
    }
 
    /**
     *
     * Write encryption key to version.php config file
     */
    protected function generateEncryptionKeys()
    {
        if (is_file(APPPATH.'config/security.php')) {
            // Never replace an existing file
            throw new CException(500, gT("Configuration file already exist"));
        }
        $sEncryptionKeypair   = ParagonIE_Sodium_Compat::crypto_sign_keypair();
        $sEncryptionPublicKey = ParagonIE_Sodium_Compat::bin2hex(ParagonIE_Sodium_Compat::crypto_sign_publickey($sEncryptionKeypair));
        $sEncryptionSecretKey = ParagonIE_Sodium_Compat::bin2hex(ParagonIE_Sodium_Compat::crypto_sign_secretkey($sEncryptionKeypair));
        $sEncryptionKeypair   = ParagonIE_Sodium_Compat::bin2hex($sEncryptionKeypair);

        if (empty($sEncryptionKeypair)) {
            return false;
        }
        
        $sConfig = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');"."\n"
            ."/*"."\n"
            ." * LimeSurvey"."\n"
            ." * Copyright (C) 2007-2019 The LimeSurvey Project Team / Carsten Schmitz"."\n"
            ." * All rights reserved."."\n"
            ." * License: GNU/GPL License v3 or later, see LICENSE.php"."\n"
            ." * LimeSurvey is free software. This version may have been modified pursuant"."\n"
            ." * to the GNU General Public License, and as distributed it includes or"."\n"
            ." * is derivative of works licensed under the GNU General Public License or"."\n"
            ." * other free or open source software licenses."."\n"
            ." * See COPYRIGHT.php for copyright notices and details."."\n"
            ." */"."\n"
            ."\n"
            ."/* "."\n"
            ."WARNING!!!"."\n"
            ."ONCE SET, ENCRYPTION KEYS SHOULD NEVER BE CHANGED, OTHERWISE ALL ENCRYPTED DATA COULD BE LOST !!!"."\n"
            ."\n"
            ."*/"."\n"
            ."\n"
            ."\$config = array();"."\n"
            ."\$config['encryptionkeypair'] = '".$sEncryptionKeypair."';"."\n"
            ."\$config['encryptionpublickey'] = '".$sEncryptionPublicKey."';"."\n"
            ."\$config['encryptionsecretkey'] = '".$sEncryptionSecretKey."';"."\n"
            ."return \$config;";

        Yii::app()->setConfig("encryptionkeypair", $sEncryptionKeypair);
        Yii::app()->setConfig("encryptionpublickey", $sEncryptionPublicKey);
        Yii::app()->setConfig("encryptionsecretkey", $sEncryptionSecretKey);
        if (is_writable(APPPATH.'config')) {
            file_put_contents(APPPATH.'config/security.php', $sConfig);
        } else {
            throw new CHttpException(500, gT("Configuration directory is not writable"));
        }
    }
}
