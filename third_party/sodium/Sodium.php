<?php
class Sodium extends CApplicationComponent{

    protected $sEncryptionKey = null;
    protected $bLibraryExists = false;

    public function init(){
        require_once(APPPATH.'/third_party/sodium_compat/autoload.php');

        $this->checkIfLibraryExists();

        $this->checkIfKeyExists();
        
        if( !function_exists( 'mcrypt_module_open') ){
			//throw new CException( Yii::t('aes256', 'You must have mcrypt lib enable on your server to be enabled to use this extension.') );
        }
		if(empty($this->privatekey_32bits_hexadecimal) || strlen($this->privatekey_32bits_hexadecimal)!=64){
            //throw new CException(Yii::t('aes256','aes256 extension parameter privatekey_32bits_hexadecimal must be filled with exactly 64 hexadecimal characters !'));
        }
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
        if (empty(Yii::app()->getConfig('encryptionkey'))){
            $this->generateEncryptionKey(); //return false;
        }
        $this->sEncryptionKey = $this->getEncryptionKey();
    }

    /**
	 * 
	 * Get encryption key from version.php config file
	 * @return string Return encryption key string
	 */
	protected function getEncryptionKey(){			 	
        return sodium_hex2bin(Yii::app()->getConfig('encryptionkey'));
    }	
    
    /**
	 * Encrypt input data using AES256 CBC encryption
	 * @param unknown_type $dataToEncrypt Data to encrypt. Could be a string or a serializable PHP object
	 * @return string Return encrypted AES256 CBC value
	 */
	public function encrypt($dataToEncrypt){
        $key = $this->sEncryptionKey;
        $nonce = random_bytes(ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = ParagonIE_Sodium_Compat::crypto_secretbox($dataToEncrypt, $nonce, $key);
        $sEncrypted = urlencode(base64_encode($nonce . $ciphertext));
        return $sEncrypted;
	}
 
	/**
	 * 
	 * Decrypt encrypted string.
	 * @param string $encryptedString Encrypted string to decrypt
	 * @param bool $bReturnFalseIfError false by default. If TRUE, return false in case of error (bad decryption). Else, return given $encryptedInput value
	 * @return string Return decrypted value (string or unsezialized object) if suceeded. Return FALSE if an error occurs (bad password/salt given) or inpyt encryptedString
	 */
	public function decrypt($encryptedString,$bReturnFalseIfError=false){			 	
        $key = ($this->sEncryptionKey);
        if (!empty($encryptedString)){
            $data = base64_decode(urldecode($encryptedString));
            $nonce = substr($data, 0, ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
            $ciphertext = substr($data, ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_NONCEBYTES);
            $plaintext = ParagonIE_Sodium_Compat::crypto_secretbox_open($ciphertext, $nonce, $key);
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
	 * @return string Return encryption key string
	 */
	protected function generateEncryptionKey(){			 	
        @ini_set('auto_detect_line_endings', '1');
        $bLineFound = 0;
        $sRootdir      = Yii::app()->getConfig("rootdir");
        $versionlines = file($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php');
        $handle       = fopen($sRootdir.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'version.php', "w");
        $sEncryptionKey = sodium_bin2hex(random_bytes(ParagonIE_Sodium_Compat::CRYPTO_SECRETBOX_KEYBYTES));

        if (empty($sEncryptionKey)){
            fclose($handle);
            return false;
        }

        $sNewLine = '$config[\'encryptionkey\'] = \''.$sEncryptionKey.'\';'."\r\n";       

        foreach ($versionlines as $line) {
            if (strpos($line, 'encryptionkey') !== false) {
                $bLineFound = 1;
                fwrite($handle, $sNewLine);
                continue;
            }

            if ($bLineFound == 0 && strpos($line, 'return $config;') !== false) {
                fwrite($handle, $sNewLine);
            }

            fwrite($handle, $line);
        }
        fclose($handle);
        Yii::app()->setConfig("encryptionkey", $sEncryptionKey);
        return $sEncryptionKey;
    }	    
 
		    

}
