<?php

/**
 * Class LSSodium
 */
class LSSodium
{
    public $bLibraryExists = false;
    protected $sEncryptionMethod = null;
    protected $sEncryptionNonce = null;
    protected $sEncryptionSecretBoxKey = null;

    public function init()
    {
        require_once Yii::app()->basePath . '/../vendor/paragonie/sodium_compat/src/Compat.php';
        require_once Yii::app()->basePath . '/../vendor/paragonie/sodium_compat/src/Core/Util.php';
        require_once Yii::app()->basePath . '/../vendor/paragonie/sodium_compat/autoload.php';

        $this->checkIfLibraryExists();

        if ($this->bLibraryExists === false) {
            /*throw new SodiumException(sprintf(gT("This operation uses data encryption functions which require Sodium library to be installed, but library was not found. If you don't want to use data encryption, you have to disable encryption in attribute settings. Here is a link to the manual page:
            %s", 'unescaped'), 'https://www.limesurvey.org/manual/Data_encryption#Errors'));*/
        } else {
            $this->checkIfKeyExists();
        }
    }

    /** 
     * Set the encrytion method
     * @param string 
     * @return void
     */
    public function setEncryptionMethod($string)
    {
        $this->sEncryptionMethod = $string;
    }

    /**
     * Check if Sodium library is installed
     * @return void
     */
    public function checkIfLibraryExists()
    {
        if (function_exists('sodium_crypto_sign_open')) {
            $this->bLibraryExists = true;
        }
    }

    /**
     *
     * Check if encryption key and nonce exist in configuration and generate it if missing
     * @return void
     * @throws SodiumException
     */
    protected function checkIfKeyExists()
    {
        if (empty(App()->getConfig('encryptionsecretboxkey')) && empty(App()->getConfig('encryptionnonce'))) {
            $this->generateEncryptionKeys();
        }
        if ($this->sEncryptionNonce === null) {
            $this->sEncryptionNonce = $this->getEncryptionNonce();
        }
        if ($this->sEncryptionSecretBoxKey === null) {
            $this->sEncryptionSecretBoxKey = $this->getEncryptionSecretBoxKey();
        }
    }

    /**
     * @return string
     * @throws SodiumException
     */
    protected function getEncryptionNonce()
    {
        return sodium_hex2bin((string) Yii::app()->getConfig('encryptionnonce'));
    }

    /**
     * @return string
     * @throws SodiumException
     */
    protected function getEncryptionSecretBoxKey()
    {
        return sodium_hex2bin((string) Yii::app()->getConfig('encryptionsecretboxkey'));
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     * @throws SodiumException
     */
    protected function getEncryptionKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionkeypair'));
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     * @throws SodiumException
     */
    protected function getEncryptionPublicKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionpublickey'));
    }

    /**
     *
     * Get encryption key from version.php config file
     * @return string Return encryption key string
     * @throws SodiumException
     */
    protected function getEncryptionSecretKey()
    {
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionsecretkey'));
    }

    /**
     * Encrypt input data using AES256 CBC encryption
     * @param string $sDataToEncrypt Data to encrypt. Could be a string or a serializable PHP object
     * @return string Return encrypted AES256 CBC value
     * @throws SodiumException
     */
    public function encrypt($sDataToEncrypt): string
    {
        if ($this->bLibraryExists === true) {
            if (isset($sDataToEncrypt) && $sDataToEncrypt !== "") {
                switch ($this->sEncryptionMethod) {
                    case 'H':
                        // generate a random nonce
                        $nonce = random_bytes(ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
                        // encrypt plaintext with key and nonce
                        $ciphertext = ParagonIE_Sodium_Compat::crypto_secretbox(
                            strval($sDataToEncrypt),
                            $nonce,
                            $this->sEncryptionSecretBoxKey
                        );
                        $sEncrypted = ParagonIE_Sodium_Compat::bin2hex($nonce) . ParagonIE_Sodium_Compat::bin2hex($ciphertext);
                        break;
                    case 'B':
                    default:
                        $sEncrypted = base64_encode(ParagonIE_Sodium_Compat::crypto_secretbox((string) $sDataToEncrypt, $this->sEncryptionNonce, $this->sEncryptionSecretBoxKey));
                        break;
                }
                return $sEncrypted;
            }
            return '';
        }
        return $sDataToEncrypt;
    }

    /**
     *
     * Decrypt encrypted string.
     * @param string $sEncryptedString Encrypted string to decrypt, if it string 'null', didn't try to decode
     * @param bool $bReturnFalseIfError false by default. If TRUE, return false in case of error (bad decryption). Else, return given $encryptedInput value
     * @return string|false Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or input encryptedString
     * @throws SodiumException
     */
    public function decrypt($sEncryptedString, $bReturnFalseIfError = false): string
    {
        if ($this->bLibraryExists === true) {
            if (!empty($sEncryptedString) && $sEncryptedString !== 'null') {
                /* Try to decrypt according to sEncryptionMethod if H */
                switch ($this->sEncryptionMethod) {
                    case 'H':
                        $plaintext = $this->decryptHardened($sEncryptedString);
                        if ($plaintext !== false) {
                            return $plaintext;
                        }
                        $plaintext = $this->decryptBasic($sEncryptedString);
                        if ($plaintext !== false) {
                            return $plaintext;
                        }
                        break;
                    case 'B':
                    default:
                        $plaintext = $this->decryptBasic($sEncryptedString);
                        if ($plaintext !== false) {
                            return $plaintext;
                        }
                        $plaintext = $this->decryptHardened($sEncryptedString);
                        if ($plaintext !== false) {
                            return $plaintext;
                        }
                        break;
                }
                /* We get here : all solution return false */
                if ($bReturnFalseIfError) {
                    throw new SodiumException(sprintf(gT("Wrong decryption key! Decryption key has changed since this data were last saved, so data can't be decrypted. Please consult our manual at %s.", 'unescaped'), 'https://www.limesurvey.org/manual/Data_encryption#Errors'));
                }
                return false;
            }
            return '';
        }
        return $sEncryptedString;
    }

    /**
     * Decrypt encrypted string using Hardened methos
     * @param string $sEncryptedString Encrypted string to decrypt, if it string 'null', didn't try to decode
     * @return string|false Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or input encryptedString
    */
    private function decryptHardened($sEncryptedString)
    {
            $minLength = (
                ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES +
                ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_MACBYTES
            );
            // check that encrypted string is of sufficient length to
            // contain at minimum the random nonce and authentication tag
            // split the string into nonce and cipher text then try to decrypt
            if (strlen($sEncryptedString) < $minLength) {
                return false;
            }
            $nonceAndCipherText = ParagonIE_Sodium_Compat::hex2bin($sEncryptedString);
            $nonce = substr($nonceAndCipherText, 0, ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = substr($nonceAndCipherText, ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
            $plaintext = ParagonIE_Sodium_Compat::crypto_secretbox_open(
                $ciphertext,
                $nonce,
                $this->sEncryptionSecretBoxKey
            );
            return $plaintext;
    }

    /**
     * Decrypt encrypted string using Basic methos
     * @param string $sEncryptedString Encrypted string to decrypt, if it string 'null', didn't try to decode
     * @return string|false Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or input encryptedString
    */
    private function decryptBasic($sEncryptedString)
    {
            $plaintext = ParagonIE_Sodium_Compat::crypto_secretbox_open(base64_decode($sEncryptedString), $this->sEncryptionNonce, $this->sEncryptionSecretBoxKey);
            return $plaintext;
    }

    /**
     *
     * Write encryption key to version.php config file
     * @throws Exception
     * @return void
     */
    protected function generateEncryptionKeys()
    {
        // commented out to be able to generate new keys for encryption update, also this function will only be executed if no key is available and is redundant.
//        if (is_file(APPPATH . 'config/security.php')) {
//            // Never replace an existing file
//            throw new CException(500, gT("Configuration file already exist"));
//        }
        $sEncryptionNonce = sodium_bin2hex(random_bytes(ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES));
        $sEncryptionSecretBoxKey = sodium_bin2hex(ParagonIE_Sodium_Compat::crypto_secretbox_keygen());
        // old keys used for encryption are still available as a backup if they have been used before
        $sEncryptionKeypair = sodium_bin2hex($this->getEncryptionKey());
        $sEncryptionPublicKey = sodium_bin2hex($this->getEncryptionPublicKey());
        $sEncryptionSecretKey = sodium_bin2hex($this->getEncryptionSecretKey());

        if (empty($sEncryptionNonce) || empty($sEncryptionSecretBoxKey)) {
            return;
        }

        $sConfig = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');" . "\n"
            . "/*" . "\n"
            . " * LimeSurvey" . "\n"
            . " * Copyright (C) 2007-2019 The LimeSurvey Project Team / Carsten Schmitz" . "\n"
            . " * All rights reserved." . "\n"
            . " * License: GNU/GPL License v3 or later, see LICENSE.php" . "\n"
            . " * LimeSurvey is free software. This version may have been modified pursuant" . "\n"
            . " * to the GNU General Public License, and as distributed it includes or" . "\n"
            . " * is derivative of works licensed under the GNU General Public License or" . "\n"
            . " * other free or open source software licenses." . "\n"
            . " * See COPYRIGHT.php for copyright notices and details." . "\n"
            . " */" . "\n"
            . "\n"
            . "/* " . "\n"
            . "WARNING!!!" . "\n"
            . "ONCE SET, ENCRYPTION KEYS SHOULD NEVER BE CHANGED, OTHERWISE ALL ENCRYPTED DATA COULD BE LOST !!!" . "\n"
            . "\n"
            . "*/" . "\n"
            . "\n"
            . "\$config = array();" . "\n";
        if ($sEncryptionKeypair) {
            $sConfig .= "\$config['encryptionkeypair'] = '" . $sEncryptionKeypair . "';" . "\n";
        }
        if ($sEncryptionPublicKey) {
            $sConfig .= "\$config['encryptionpublickey'] = '" . $sEncryptionPublicKey . "';" . "\n";
        }
        if ($sEncryptionSecretKey) {
            $sConfig .= "\$config['encryptionsecretkey'] = '" . $sEncryptionSecretKey . "';" . "\n";
        }
        $sConfig .= "\$config['encryptionnonce'] = '" . $sEncryptionNonce . "';" . "\n"
            . "\$config['encryptionsecretboxkey'] = '" . $sEncryptionSecretBoxKey . "';" . "\n"
            . "return \$config;";

        Yii::app()->setConfig("encryptionnonce", $sEncryptionNonce);
        Yii::app()->setConfig("encryptionsecretboxkey", $sEncryptionSecretBoxKey);
        $configdir = \Yii::app()->getConfig('configdir');
        if (is_writable($configdir)) {
            file_put_contents($configdir . '/security.php', $sConfig);
        } else {
            throw new CHttpException(500, gT("Configuration directory is not writable"));
        }
    }
}
