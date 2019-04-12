<?php
class Sodium extends CApplicationComponent{

    protected $bLibraryExists = false;
    protected $sEncryptionKeypair = null;
    protected $sEncryptionPublicKey = null;
    protected $sEncryptionSecretKey = null;

    public function init(){
        require_once(APPPATH.'/third_party/sodium_compat/autoload.php');

        $this->checkIfLibraryExists();

        $this->checkIfKeyExists();
        
        /*
        if( !function_exists( 'mcrypt_module_open') ){
			//throw new CException( Yii::t('aes256', 'You must have mcrypt lib enable on your server to be enabled to use this extension.') );
        }
		if(empty($this->privatekey_32bits_hexadecimal) || strlen($this->privatekey_32bits_hexadecimal)!=64){
            //throw new CException(Yii::t('aes256','aes256 extension parameter privatekey_32bits_hexadecimal must be filled with exactly 64 hexadecimal characters !'));
        }
        */
	}

	
	/**
	 * Encrypt input data using AES256 CBC encryption
	 * @return bool 
	 */
	public function checkIfLibraryExists(){
        
        $version = SODIUM_LIBRARY_VERSION;
        if ($version != ''){
            $this->bLibraryExists = true;
        }
    }

    	/**
	 * 
	 * Check if encryption key exists in configuration
	 * @return bool Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or inpyt encryptedString
	 */
	protected function checkIfKeyExists(){			 	
        if (empty(Yii::app()->getConfig('encryptionkeypair'))){
            $this->generateEncryptionKeys(); //return false;
        }
        if ($this->sEncryptionKeypair === null){
            $this->sEncryptionKeypair = $this->getEncryptionKey();
        }
        if ($this->sEncryptionPublicKey === null){
            $this->sEncryptionPublicKey = $this->getEncryptionPublicKey();
        }
        if ($this->sEncryptionSecretKey === null){
            $this->sEncryptionSecretKey = $this->getEncryptionSecretKey();
        }
    }

    /**
	 * 
	 * Get encryption key from version.php config file
	 * @return string Return encryption key string
	 */
	protected function getEncryptionKey(){			 	
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionkeypair'));
    }

    /**
	 * 
	 * Get encryption key from version.php config file
	 * @return string Return encryption key string
	 */
	protected function getEncryptionPublicKey(){			 	
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionpublickey'));
    }

    /**
	 * 
	 * Get encryption key from version.php config file
	 * @return string Return encryption key string
	 */
	protected function getEncryptionSecretKey(){			 	
        return ParagonIE_Sodium_Compat::hex2bin(Yii::app()->getConfig('encryptionsecretkey'));
    }	
    
    /**
	 * Encrypt input data using AES256 CBC encryption
	 * @param unknown_type $sDataToEncrypt Data to encrypt. Could be a string or a serializable PHP object
	 * @return string Return encrypted AES256 CBC value
	 */
	public function encrypt($sDataToEncrypt){
        if (!empty($sDataToEncrypt)){
            $sEncrypted = base64_encode(ParagonIE_Sodium_Compat::crypto_sign((string) $sDataToEncrypt, $this->sEncryptionSecretKey));
        }
        return $sEncrypted;
	}
 
	/**
	 * 
	 * Decrypt encrypted string.
	 * @param string $sEncryptedString Encrypted string to decrypt
	 * @param bool $bReturnFalseIfError false by default. If TRUE, return false in case of error (bad decryption). Else, return given $encryptedInput value
	 * @return string Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or inpyt encryptedString
	 */
	public function decrypt($sEncryptedString, $bReturnFalseIfError=false){ 	
        if (!empty($sEncryptedString)  && $sEncryptedString != 'null'){
            $plaintext = ParagonIE_Sodium_Compat::crypto_sign_open(base64_decode($sEncryptedString), $this->sEncryptionPublicKey);
            
            if ($plaintext === false) {
                //throw new Exception("Bad ciphertext");
            }
            return $plaintext;
        }

        return '';
	}	
 
	/**
	 * 
	 * Write encryption key to version.php config file
	 */
	protected function generateEncryptionKeys(){			 	
        $sEncryptionKeypair   = ParagonIE_Sodium_Compat::crypto_sign_keypair();
        $sEncryptionPublicKey = ParagonIE_Sodium_Compat::bin2hex(ParagonIE_Sodium_Compat::crypto_sign_publickey($sEncryptionKeypair));
        $sEncryptionSecretKey = ParagonIE_Sodium_Compat::bin2hex(ParagonIE_Sodium_Compat::crypto_sign_secretkey($sEncryptionKeypair));
        $sEncryptionKeypair   = ParagonIE_Sodium_Compat::bin2hex($sEncryptionKeypair);

        if (empty($sEncryptionKeypair)){
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
            header('refresh:5;url='.$this->createUrl("installer/welcome"));
            echo "<b>".gT("Configuration directory is not writable")."</b><br/>";
            printf(gT('You will be redirected in about 5 secs. If not, click <a href="%s">here</a>.', 'unescaped'), $this->createUrl('installer/welcome'));
            Yii::app()->end();
        }
    }
}
