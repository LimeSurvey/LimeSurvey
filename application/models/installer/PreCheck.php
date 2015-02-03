<?php
/**
 * Model that checks all requirements.
 */
class PreCheck extends CFormModel 
{
       
    public $sessionSupport = false;
    
    public function __construct($scenario = '') {
        parent::__construct($scenario);
        if (!isset($_SESSION['precheck'])) {
            $_SESSION['precheck'] = 'precheck';
        } else {
            $this->sessionSupport = true;
        }
    }
        
    public function getVersion() {
        return PHP_VERSION;
    }
    public function getMemoryLimit() {
        return ini_get('memory_limit');
    }
    
    public function getPdoSupport() {
        return count(PDO::getAvailableDrivers()) > 0;
    }
    
    public function getMultiByteSupport() {
        return function_exists('mb_convert_encoding');        
    }
    
    public function getConfigPath() {
        return Yii::getPathOfAlias('application') . '/config';
    }
    
    public function getUploadPath() {
        return Yii::getPathOfAlias('application') . '/../upload';
    }
    
    public function getTempPath() {
        return Yii::getPathOfAlias('application') . '/../upload';
    }
    
    public function getZipSupport() {
        return function_exists('zip_open');        
    }
    
    public function getImapSupport() {
        return function_exists('imap_open');        
    }
    public function getZlibSupport() {
        return function_exists('zlib_get_coding_type');        
    }
    
    public function getLdapSupport() {
        return function_exists('ldap_connect');        
    }
    
    public function getGdSupport() {
        return function_exists('gd_info') && array_key_exists('FreeType Support', gd_info());
    }
    public function getRequiredValue($attribute) {
        $values = [
            'version' => '5.4.0',
            'memoryLimit' => '128M'
        ];
        return isset($values[$attribute]) ? $values[$attribute] : true;
    }
    
    public function rules() 
    {
        return [
            ['version', 'VersionValidator', 'min' => $this->getRequiredValue('version'), 'on' => ['required']],
            ['memoryLimit', 'MemoryValidator', 'min' => $this->getRequiredValue('memoryLimit'), 'on' => ['required']],
            ['pdoSupport', 'required', 'requiredValue' => true, 'on' => ['required']],
            ['multibyteSupport', 'required', 'requiredValue' => true, 'on' => ['required']],
            ['sessionSupport', 'required', 'requiredValue' => true, 'on' => ['required']],
            [['configPath', 'uploadPath', 'tempPath'], 'WritableValidator', 'forceDirectory' => true, 'recursive' => false, 'on' => ['required']],
            // Optional modules that Limesurvey can use.
            ['gdSupport', 'required', 'requiredValue' => true, 'on' => ['optional']],
            ['ldapSupport', 'required', 'requiredValue' => true, 'on' => ['optional']],
            ['zipSupport', 'required', 'requiredValue' => true, 'on' => ['optional']],
            ['zlibSupport', 'required', 'requiredValue' => true, 'on' => ['optional']],
            ['imapSupport', 'required', 'requiredValue' => true, 'on' => ['optional']],
            
        ];
    }
        /**
    * check requirements
    *
    * @param array $data return theme variables
    * @return bool requirements met
    */
    public function checkRequirements(&$aData)
    {
        // proceed variable check if all requirements are true. If any of them is false, proceed is set false.
        $bProceed = true; //lets be optimistic!

        /**
        * check image HTML template
        *
        * @param bool $result
        */
        function check_HTML_image($result)
        {
            $aLabelYesNo = array('wrong', 'right');
            return sprintf('<img src="%s/installer/images/tick-%s.png" alt="Found" />', Yii::app()->baseUrl, $aLabelYesNo[$result]);
        }


        function is_writable_recursive($sDirectory)
        {
            $sFolder = opendir($sDirectory);
            while($sFile = readdir( $sFolder ))
                if($sFile != '.' && $sFile != '..' &&
                ( !is_writable(  $sDirectory."/".$sFile  ) ||
                (  is_dir(   $sDirectory."/".$sFile   ) && !is_writable_recursive(   $sDirectory."/".$sFile   )  ) ))
                {
                    closedir($sFolder);
                    return false;
                }
                closedir($sFolder);
            return true;
        }

        /**
        * check for a specific PHPFunction, return HTML image
        *
        * @param string $function
        * @param string $image return
        * @return bool result
        */
        function check_PHPFunction($sFunctionName, &$sImage)
        {
            $bExists = function_exists($sFunctionName);
            $sImage = check_HTML_image($bExists);
            return $bExists;
        }

        /**
        * check if file or directory exists and is writeable, returns via parameters by reference
        *
        * @param string $path file or directory to check
        * @param int $type 0:undefined (invalid), 1:file, 2:directory
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_PathWriteable($path, $type, &$aData, $base, $keyError, $bRecursive=false)
        {
            $bResult = false;
            $aData[$base.'Present'] = 'Not Found';
            $aData[$base.'Writable'] = '';
            switch($type) {
                case 1:
                    $exists = is_file($path);
                    break;
                case 2:
                    $exists = is_dir($path);
                    break;
                default:
                    throw new Exception('Invalid type given.');
            }
            if ($exists)
            {
                $aData[$base.'Present'] = 'Found';
                if ((!$bRecursive && is_writable($path)) || ($bRecursive && is_writable_recursive($path)))
                {
                    $aData[$base.'Writable'] = 'Writable';
                    $bResult = true;
                }
                else
                {
                    $aData[$base.'Writable'] = 'Unwritable';
                }
            }
            $bResult || $aData[$keyError] = true;

            return $bResult;
        }

        /**
        * check if file exists and is writeable, returns via parameters by reference
        *
        * @param string $file to check
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_FileWriteable($file, &$data, $base, $keyError)
        {
            return check_PathWriteable($file, 1, $data, $base, $keyError);
        }

        /**
        * check if directory exists and is writeable, returns via parameters by reference
        *
        * @param string $directory to check
        * @param string $data to manipulate
        * @param string $base key for data manipulation
        * @param string $keyError key for error data
        * @return bool result of check (that it is writeable which implies existance)
        */
        function check_DirectoryWriteable($directory, &$data, $base, $keyError, $bRecursive=false)
        {
            return check_PathWriteable($directory, 2, $data, $base, $keyError, $bRecursive);
        }

        if (convertPHPSizeToBytes(ini_get('memory_limit'))/1024/1024<64 && ini_get('memory_limit')!=-1)
            $bProceed = !$aData['bMemoryError'] = true;


        // mbstring library check
        if (!check_PHPFunction('mb_convert_encoding', $aData['mbstringPresent']))
            $bProceed = false;

        // JSON library check
        if (!check_PHPFunction('json_encode', $aData['bJSONPresent']))
            $bProceed = false;

        // ** file and directory permissions checking **

        // config directory
        if (!check_DirectoryWriteable(Yii::app()->getConfig('rootdir').'/application/config', $aData, 'config', 'derror') )
            $bProceed = false;

        // templates directory check
        if (!check_DirectoryWriteable(Yii::app()->getConfig('tempdir').'/', $aData, 'tmpdir', 'tperror',true) )
            $bProceed = false;

        //upload directory check
        if (!check_DirectoryWriteable(Yii::app()->getConfig('uploaddir').'/', $aData, 'uploaddir', 'uerror',true) )
            $bProceed = false;

        // Session writable check
        $session = Yii::app()->session; /* @var $session CHttpSession */
        $sessionWritable = ($session->get('saveCheck', null)==='save');
        $aData['sessionWritable'] = $sessionWritable;
        $aData['sessionWritableImg'] = check_HTML_image($sessionWritable);
        if (!$sessionWritable){
            // For recheck, try to set the value again
            $session['saveCheck'] = 'save';
            $bProceed = false;
        }

        // ** optional settings check **

        // gd library check
        if (function_exists('gd_info')) {
            $aData['gdPresent'] = check_HTML_image(true);
        } else {
            $aData['gdPresent'] = check_HTML_image(false);
        }
        // ldap library check
        check_PHPFunction('ldap_connect', $aData['ldapPresent']);

        // php zip library check
        check_PHPFunction('zip_open', $aData['zipPresent']);

        // zlib php library check
        check_PHPFunction('zlib_get_coding_type', $aData['zlibPresent']);

        // imap php library check
        check_PHPFunction('imap_open', $aData['bIMAPPresent']);

        return $bProceed;
    }
    
    public function attributeNames() {
        $methods = array_diff(get_class_methods($this), get_class_methods(get_parent_class($this)));

        $getters = array_filter($methods, function($name) {
            return substr($name, 0, 3) == 'get';
        });
        return array_merge(parent::attributeNames(), array_map(function($name) {
            return substr($name, 3);
        }, $getters));
    }
    
}