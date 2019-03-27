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
        @ini_set('auto_detect_line_endings', '1');
        $bLineFound1 = 0;
        $bLineFound2 = 0;
        $bLineFound3 = 0;
        $sRootdir      = Yii::app()->getConfig("rootdir");
        $versionlines = file($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'security.php');
        $handle       = fopen($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'security.php', "w");
        $sEncryptionKeypair   = ParagonIE_Sodium_Compat::crypto_sign_keypair();
        $sEncryptionPublicKey = ParagonIE_Sodium_Compat::crypto_sign_publickey($sEncryptionKeypair);
        $sEncryptionSecretKey = ParagonIE_Sodium_Compat::crypto_sign_secretkey($sEncryptionKeypair);

        if (empty($sEncryptionKeypair)){
            fclose($handle);
            return false;
        }

        // new configuration entries
        $sNewLine1  = '$config[\'encryptionkeypair\'] = \''.ParagonIE_Sodium_Compat::bin2hex($sEncryptionKeypair).'\';'."\r\n";       
        $sNewLine2 = '$config[\'encryptionpublickey\'] = \''.ParagonIE_Sodium_Compat::bin2hex($sEncryptionPublicKey).'\';'."\r\n";       
        $sNewLine3 = '$config[\'encryptionsecretkey\'] = \''.ParagonIE_Sodium_Compat::bin2hex($sEncryptionSecretKey).'\';'."\r\n";       

        foreach ($versionlines as $line) {
            // replace configuration entries if exists
            if (strpos($line, 'encryptionkeypair') !== false) {
                $bLineFound1 = 1;
                fwrite($handle, $sNewLine1);
                continue;
            }
            if (strpos($line, 'encryptionpublickey') !== false) {
                $bLineFound2 = 1;
                fwrite($handle, $sNewLine2);
                continue;
            }
            if (strpos($line, 'encryptionsecretkey') !== false) {
                $bLineFound3 = 1;
                fwrite($handle, $sNewLine3);
                continue;
            }

            // write configuration entries into configuration file for each entry that doesn't exist
            if (strpos($line, 'return $config;') !== false) {
                if ($bLineFound1 == 0){
                    fwrite($handle, $sNewLine1);
                }
                if ($bLineFound2 == 0){
                    fwrite($handle, $sNewLine2);
                }
                if ($bLineFound3 == 0){
                    fwrite($handle, $sNewLine3);
                }
            }

            fwrite($handle, $line);
        }
        fclose($handle);
        Yii::app()->setConfig("encryptionkeypair", ParagonIE_Sodium_Compat::bin2hex($sEncryptionKeypair));
        Yii::app()->setConfig("encryptionpublickey", ParagonIE_Sodium_Compat::bin2hex($sEncryptionPublicKey));
        Yii::app()->setConfig("encryptionsecretkey", ParagonIE_Sodium_Compat::bin2hex($sEncryptionSecretKey));
    }
}
