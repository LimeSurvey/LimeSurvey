<?php

/** This file is part of KCFinder project
  *
  *      @desc Uploader class
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class uploader {
    protected $config = array();
    protected $opener = array();
    protected $type;
    protected $typeDir;
    protected $typeURL;
    protected $types = array();
    protected $typeSettings = array('disabled', 'theme', 'dirPerms', 'filePerms', 'denyZipDownload', 'maxImageWidth', 'maxImageHeight', 'thumbWidth', 'thumbHeight', 'jpegQuality');
    protected $charset;
    protected $lang = 'en';
    protected $langInputNames = array('lang', 'langCode', 'lng', 'language', 'lang_code');
    protected $file;
    protected $dateTimeFull;   // Currently not used
    protected $dateTimeMid;    // Currently not used
    protected $dateTimeSmall;
    protected $labels = array();
    protected $get;
    protected $post;
    protected $cookie;
    protected $session;

    public function __get($property) {
        return property_exists($this, $property) ? $this->$property : null;
    }

    public function __construct() {

        // DISABLE MAGIC QUOTES
        if (function_exists('set_magic_quotes_runtime'))
            @set_magic_quotes_runtime(false);

        // INPUT INIT
        $input = new input();
        $this->get = &$input->get;
        $this->post = &$input->post;
        $this->cookie = &$input->cookie;

        // LINKING UPLOADED FILE
        if (count($_FILES))
            $this->file = &$_FILES[key($_FILES)];

        // LOAD DEFAULT CONFIGURATION
        require "config.php";

        // SETTING UP SESSION
        if (isset($_CONFIG['_sessionLifetime']))
            ini_set('session.gc_maxlifetime', $_CONFIG['_sessionLifetime'] * 60);
        if (isset($_CONFIG['_sessionDir']))
            ini_set('session.save_path', $_CONFIG['_sessionDir']);
        if (isset($_CONFIG['_sessionDomain']))
            ini_set('session.cookie_domain', $_CONFIG['_sessionDomain']);
        session_start();

        // RELOAD DEFAULT CONFIGURATION
        require "config.php";
        $this->config = $_CONFIG;

        // LOAD SESSION CONFIGURATION IF EXISTS
        if (isset($_CONFIG['_sessionVar']) &&
            is_array($_CONFIG['_sessionVar'])
        ) {
            foreach ($_CONFIG['_sessionVar'] as $key => $val)
                if ((substr($key, 0, 1) != "_") && isset($_CONFIG[$key]))
                    $this->config[$key] = $val;
            if (!isset($this->config['_sessionVar']['self']))
                $this->config['_sessionVar']['self'] = array();
            $this->session = &$this->config['_sessionVar']['self'];
        } else
            $this->session = &$_SESSION;

        // GET TYPE DIRECTORY
        $this->types = &$this->config['types'];
        $firstType = array_keys($this->types);
        $firstType = $firstType[0];
        $this->type = (
            isset($this->get['type']) &&
            isset($this->types[$this->get['type']])
        )
            ? $this->get['type'] : $firstType;

        // LOAD DIRECTORY TYPE SPECIFIC CONFIGURATION IF EXISTS
        if (is_array($this->types[$this->type])) {
            foreach ($this->types[$this->type] as $key => $val)
                if (in_array($key, $this->typeSettings))
                    $this->config[$key] = $val;
            $this->types[$this->type] = isset($this->types[$this->type]['type'])
                ? $this->types[$this->type]['type'] : "";
        }

        // COOKIES INIT
        if (!strlen($this->config['cookieDomain']))
            $this->config['cookieDomain'] = $_SERVER['HTTP_HOST'];
        if (!strlen($this->config['cookiePath']))
            $this->config['cookiePath'] = "/";

        // UPLOAD FOLDER INIT
        if ($this->config['uploadURL'] == "/") {
            $this->config['uploadDir'] = strlen($this->config['uploadDir'])
                ? path::normalize($this->config['uploadDir'])
                : path::normalize($_SERVER['DOCUMENT_ROOT']);
            $this->typeDir = "{$this->config['uploadDir']}/{$this->type}";
            $this->typeURL = "/{$this->type}";
        } else {
            $this->config['uploadURL'] = (substr($this->config['uploadURL'], 0, 1) === "/")
                ? path::normalize($this->config['uploadURL'])
                : path::rel2abs_url($this->config['uploadURL']);
            $this->config['uploadDir'] = strlen($this->config['uploadDir'])
                ? path::normalize($this->config['uploadDir'])
                : path::url2fullPath($this->config['uploadURL']);
            $this->typeDir = "{$this->config['uploadDir']}/{$this->type}";
            $this->typeURL = "{$this->config['uploadURL']}/{$this->type}";
        }
        if (!is_dir($this->config['uploadDir']))
            @mkdir($this->config['uploadDir'], $this->config['dirPerms']);

        // HOST APPLICATIONS INIT
        if (isset($this->get['CKEditorFuncNum']))
            $this->opener['CKEditor']['funcNum'] = $this->get['CKEditorFuncNum'];
        if (isset($this->get['opener']) &&
            (strtolower($this->get['opener']) == "tinymce") &&
            isset($this->config['_tinyMCEPath']) &&
            strlen($this->config['_tinyMCEPath'])
        )
            $this->opener['TinyMCE'] = true;

        // LOCALIZATION
        foreach ($this->langInputNames as $key)
            if (isset($this->get[$key]) &&
                preg_match('/^[a-z][a-z\._\-]*$/i', $this->get[$key]) &&
                file_exists("lang/" . strtolower($this->get[$key]) . ".php")
            ) {
                $this->lang = $this->get[$key];
                break;
            }
        $this->localize($this->lang);

        // CHECK & MAKE DEFAULT .htaccess
        $htaccess = "{$this->config['uploadDir']}/.htaccess";
        if (isset($this->config['_check4htaccess']) &&
            $this->config['_check4htaccess']
        ) {
            if (!file_exists($htaccess)) {
                if (!@file_put_contents($htaccess, $this->get_htaccess()))
                    $this->backMsg("Cannot write to upload folder.");
            } else {
                if (false === ($data = @file_get_contents($htaccess)))
                    $this->backMsg("Cannot read .htaccess");
                if (($data != $this->get_htaccess()) && !@file_put_contents($htaccess, $data))
                    $this->backMsg("Incorrect .htaccess file. Cannot rewrite it!");
            }
        }

        // CHECK & CREATE UPLOAD FOLDER
        if (!is_dir($this->typeDir)) {
            if (!mkdir($this->typeDir, $this->config['dirPerms']))
                $this->backMsg("Cannot create {dir} folder.", array('dir' => $this->type));
        } elseif (!is_readable($this->typeDir))
            $this->backMsg("Cannot read upload folder.");
    }

    public function upload() {
        $config = &$this->config;
        $file = &$this->file;
        $url = $message = "";

        if ($config['disabled'] || $config['readonly']) {
            if (isset($file['tmp_name'])) @unlink($file['tmp_name']);
            $message = $this->label("You don't have permissions to upload files.");

        } elseif (true === ($message = $this->checkUploadedFile())) {
            $message = "";

            $dir = "{$this->typeDir}/";
            if (isset($this->get['dir']) &&
                (false !== ($gdir = $this->checkInputDir($this->get['dir'])))
            ) {
                $udir = path::normalize("$dir$gdir");
                if (substr($udir, 0, strlen($dir)) !== $dir)
                    $message = $this->label("Unknown error.");
                else {
                    $l = strlen($dir);
                    $dir = "$udir/";
                    $udir = substr($udir, $l);
                }
            }

            if (!strlen($message)) {
                if (!is_dir(path::normalize($dir)))
                    @mkdir(path::normalize($dir), $this->config['dirPerms'], true);

                $target = file::getInexistantFilename("$dir{$file['name']}");

                if (!@move_uploaded_file($file['tmp_name'], $target) &&
                    !@rename($file['tmp_name'], $target) &&
                    !@copy($file['tmp_name'], $target)
                )
                    $message = $this->label("Cannot move uploaded file to target folder.");
                else {
                    if (function_exists('chmod'))
                        @chmod($target, $this->config['filePerms']);
                    $this->makeThumb($target);
                    $url = $this->typeURL;
                    if (isset($udir)) $url .= "/$udir";
                    $url .= "/" . basename($target);
                    $url = path::urlPathEncode($url);
                }
            }
        }

        if (strlen($message) &&
            isset($this->file['tmp_name']) &&
            file_exists($this->file['tmp_name'])
        )
            @unlink($this->file['tmp_name']);

        if (strlen($message) && method_exists($this, 'errorMsg'))
            $this->errorMsg($message);
        $this->callBack($url, $message);
    }

    protected function checkUploadedFile() {
        $config = &$this->config;
        $file = &$this->file;

        if (!is_array($file) || !isset($file['name']))
            return $this->label("Unknown error");

        $extension = file::getExtension($file['name']);
        $typePatt = strtolower(text::clearWhitespaces($this->types[$this->type]));

        // CHECK FOR UPLOAD ERRORS
        if ($file['error'])
            return
                ($file['error'] == UPLOAD_ERR_INI_SIZE) ?
                    $this->label("The uploaded file exceeds {size} bytes.",
                        array('size' => ini_get('upload_max_filesize'))) : (
                ($file['error'] == UPLOAD_ERR_FORM_SIZE) ?
                    $this->label("The uploaded file exceeds {size} bytes.",
                        array('size' => $this->get['MAX_FILE_SIZE'])) : (
                ($file['error'] == UPLOAD_ERR_PARTIAL) ?
                    $this->label("The uploaded file was only partially uploaded.") : (
                ($file['error'] == UPLOAD_ERR_NO_FILE) ?
                    $this->label("No file was uploaded.") : (
                ($file['error'] == UPLOAD_ERR_NO_TMP_DIR) ?
                    $this->label("Missing a temporary folder.") : (
                ($file['error'] == UPLOAD_ERR_CANT_WRITE) ?
                    $this->label("Failed to write file.") :
                    $this->label("Unknown error.")
            )))));

        // HIDDEN FILENAMES CHECK
        elseif (substr($file['name'], 0, 1) == ".")
            return $this->label("File name shouldn't begins with '.'");

        // EXTENSION CHECK
        elseif (!$this->validateExtension($extension, $this->type))
            return $this->label("Denied file extension.");

        // SPECIAL DIRECTORY TYPES CHECK (e.g. *img)
        elseif (preg_match('/^\*([^ ]+)(.*)?$/s', $typePatt, $patt)) {
            list($typePatt, $type, $params) = $patt;
            if (class_exists("type_$type")) {
                $class = "type_$type";
                $type = new $class();
                $cfg = $config;
                if (strlen($params))
                    $cfg['params'] = trim($params);
                $response = $type->checkFile($file['tmp_name'], $cfg);
                if ($response !== true)
                    return $this->label($response);
            } else
                return $this->label("Non-existing directory type.");
        }

        // IMAGE RESIZE
        $gd = new gd($file['tmp_name']);
        if (!$gd->init_error && !$this->imageResize($gd, $file['tmp_name']))
            return $this->label("The image is too big and/or cannot be resized.");

        return true;
    }

    protected function checkInputDir($dir, $inclType=true, $existing=true) {
        $dir = path::normalize($dir);
        if (substr($dir, 0, 1) == "/")
            $dir = substr($dir, 1);

        if ((substr($dir, 0, 1) == ".") || (substr(basename($dir), 0, 1) == "."))
            return false;

        if ($inclType) {
            $first = explode("/", $dir);
            $first = $first[0];
            if ($first != $this->type)
                return false;
            $return = $this->removeTypeFromPath($dir);
        } else {
            $return = $dir;
            $dir = "{$this->type}/$dir";
        }

        if (!$existing)
            return $return;

        $path = "{$this->config['uploadDir']}/$dir";
        return (is_dir($path) && is_readable($path)) ? $return : false;
    }

    protected function validateExtension($ext, $type) {
        $ext = trim(strtolower($ext));
        if (!isset($this->types[$type]))
            return false;

        $exts = strtolower(text::clearWhitespaces($this->config['deniedExts']));
        if (strlen($exts)) {
            $exts = explode(" ", $exts);
            if (in_array($ext, $exts))
                return false;
        }

        $exts = trim($this->types[$type]);
        if (!strlen($exts) || substr($exts, 0, 1) == "*")
            return true;

        if (substr($exts, 0, 1) == "!") {
            $exts = explode(" ", trim(strtolower(substr($exts, 1))));
            return !in_array($ext, $exts);
        }

        $exts = explode(" ", trim(strtolower($exts)));
        return in_array($ext, $exts);
    }

    protected function getTypeFromPath($path) {
        return preg_match('/^([^\/]*)\/.*$/', $path, $patt)
            ? $patt[1] : $path;
    }

    protected function removeTypeFromPath($path) {
        return preg_match('/^[^\/]*\/(.*)$/', $path, $patt)
            ? $patt[1] : "";
    }

    protected function imageResize($image, $file=null) {
        if (!($image instanceof gd)) {
            $gd = new gd($image);
            if ($gd->init_error) return false;
            $file = $image;
        } elseif ($file === null)
            return false;
        else
            $gd = $image;

        if ((!$this->config['maxImageWidth'] && !$this->config['maxImageHeight']) ||
            (
                ($gd->get_width() <= $this->config['maxImageWidth']) &&
                ($gd->get_height() <= $this->config['maxImageHeight'])
            )
        )
            return true;

        return (
            $gd->resize_fit($this->config['maxImageWidth'], $this->config['maxImageHeight']) &&
            $gd->imagejpeg($file, $this->config['jpegQuality'])
        );
    }

    protected function makeThumb($file, $overwrite=true) {
        $gd = new gd($file);

        // Drop files which are not GD handled images
        if ($gd->init_error)
            return true;

        $thumb = substr($file, strlen($this->config['uploadDir']));
        $thumb = $this->config['uploadDir'] . "/" . $this->config['thumbsDir'] . "/" . $thumb;
        $thumb = path::normalize($thumb);
        $thumbDir = dirname($thumb);
        if (!is_dir($thumbDir) && !@mkdir($thumbDir, $this->config['dirPerms'], true))
            return false;

        if (!$overwrite && is_file($thumb))
            return true;

        // Images with smaller resolutions than thumbnails
        if (($gd->get_width() <= $this->config['thumbWidth']) &&
            ($gd->get_height() <= $this->config['thumbHeight'])
        ) {
            $browsable = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG);
            // Drop only browsable types
            if (in_array($gd->type, $browsable))
                return true;

        // Resize image
        } elseif (!$gd->resize_fit($this->config['thumbWidth'], $this->config['thumbHeight']))
            return false;

        // Save thumbnail
        return $gd->imagejpeg($thumb, $this->config['jpegQuality']);
    }

    protected function localize($langCode) {
        require "lang/{$langCode}.php";
        setlocale(LC_ALL, $lang['_locale']);
        $this->charset = $lang['_charset'];
        $this->dateTimeFull = $lang['_dateTimeFull'];
        $this->dateTimeMid = $lang['_dateTimeMid'];
        $this->dateTimeSmall = $lang['_dateTimeSmall'];
        unset($lang['_locale']);
        unset($lang['_charset']);
        unset($lang['_dateTimeFull']);
        unset($lang['_dateTimeMid']);
        unset($lang['_dateTimeSmall']);
        $this->labels = $lang;
    }

    protected function label($string, array $data=null) {
        $return = isset($this->labels[$string]) ? $this->labels[$string] : $string;
        if (is_array($data))
            foreach ($data as $key => $val)
                $return = str_replace("{{$key}}", $val, $return);
        return $return;
    }

    protected function backMsg($message, array $data=null) {
        $message = $this->label($message, $data);
        if (isset($this->file['tmp_name']) && file_exists($this->file['tmp_name']))
            @unlink($this->file['tmp_name']);
        $this->callBack("", $message);
        die;
    }

    protected function callBack($url, $message="") {
        $message = text::jsValue($message);
        $CKfuncNum = isset($this->opener['CKEditor']['funcNum'])
            ? $this->opener['CKEditor']['funcNum'] : 0;
        if (!$CKfuncNum) $CKfuncNum = 0;
        header("Content-Type: text/html; charset={$this->charset}");

?><html>
<body>
<script type='text/javascript'>
var kc_CKEditor = (window.parent && window.parent.CKEDITOR)
    ? window.parent.CKEDITOR.tools.callFunction
    : ((window.opener && window.opener.CKEDITOR)
        ? window.opener.CKEDITOR.tools.callFunction
        : false);
var kc_FCKeditor = (window.opener && window.opener.OnUploadCompleted)
    ? window.opener.OnUploadCompleted
    : ((window.parent && window.parent.OnUploadCompleted)
        ? window.parent.OnUploadCompleted
        : false);
var kc_Custom = (window.parent && window.parent.KCFinder)
    ? window.parent.KCFinder.callBack
    : ((window.opener && window.opener.KCFinder)
        ? window.opener.KCFinder.callBack
        : false);
if (kc_CKEditor)
    kc_CKEditor(<?php echo $CKfuncNum ?>, '<?php echo $url ?>', '<?php echo $message ?>');
if (kc_FCKeditor)
    kc_FCKeditor(<?php echo strlen($message) ? 1 : 0 ?>, '<?php echo $url ?>', '', '<?php echo $message ?>');
if (kc_Custom) {
    if (<?php echo strlen($message) ?>) alert('<?php echo $message ?>');
    kc_Custom('<?php echo $url ?>');
}
if (!kc_CKEditor && !kc_FCKeditor && !kc_Custom)
    alert("<?php echo $message ?>");
</script>
</body>
</html><?php

    }

    protected function get_htaccess() {
        return "<IfModule mod_php4.c>
  php_value engine off
</IfModule>
<IfModule mod_php5.c>
  php_value engine off
</IfModule>
";
    }
}

?>