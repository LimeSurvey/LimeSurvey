<?php

/**
 * This extension is needed to add complex functions to twig, needing specific process (like accessing config datas).
 * Most of the calls to internal functions don't need to be set here, but can be directly added to the internal config file.
 * For example, the calls to encode, gT and eT don't need any extra parameters or process, so they are added as filters in the congif/internal.php:
 *
 * 'filters' => array(
 *     'jencode' => 'CJSON::encode',
 *     't'     => 'eT',
 *     'gT'    => 'gT',
 * ),
 *
 * So you only add functions here when they need a specific process while called via Twig.
 * To add an advanced function to twig:
 *
 * 1. Add it here as a static public function
 *      eg:
 *          static public function foo($bar)
 *          {
 *              return procces($bar);
 *          }
 *
 * 2. Add it in config/internal.php as a function, and as an allowed function in the sandbox
 *      eg:
 *          twigRenderer' => array(
 *              ...
 *              'functions' => array(
 *                  ...
 *                  'foo' => 'LS_Twig_Extension::foo',
 *                ...),
 *              ...
 *              'sandboxConfig' => array(
 *              ...
 *                  'functions' => array('include', ..., 'foo')
 *                 ),
 *
 * Now you access this function in any twig file via: {{ foo($bar) }}, it will show the result of process($bar).
 * If LS_Twig_Extension::foo() returns some HTML, by default the HTML will be escaped and shows as text.
 * To get the pure HTML, just do: {{ foo($bar) | raw }}
 */

use Twig\Extension\AbstractExtension;

class LS_Twig_Extension extends AbstractExtension
{
    /**
     * Publish a css file from public style directory, using or not the asset manager (depending on configuration)
     * In any twig file, you can register a public css file doing: {{ registerPublicCssFile($sPublicCssFileName) }}
     * @param string $sPublicCssFileName name of the CSS file to publish in public style directory
     */
    public static function registerPublicCssFile($sPublicCssFileName)
    {
        Yii::app()->clientScript->registerCssFile(
            Yii::app()->getConfig('publicstyleurl') .
            $sPublicCssFileName
        );
    }


    /**
     * Publish a css file from template directory, using or not the asset manager (depending on configuration)
     * In any twig file, you can register a template css file doing: {{ registerTemplateCssFile($sTemplateCssFileName) }}
     * @param string $sTemplateCssFileName name of the CSS file to publish in template directory (it should contains the subdirectories)
     */
    public static function registerTemplateCssFile($sTemplateCssFileName)
    {
        /*
            CSS added from template could require some files from the template folder file...  (eg: background.css)
            So, if we want the statements like :
              url("../files/myfile.jpg)
             to point to an existing file, the css file must be published in the same tmp directory than the template files
             in other words, the css file must be added to the template package.
        */

        $oTemplate = self::getTemplateForRessource($sTemplateCssFileName);
        Yii::app()->clientScript->packages[$oTemplate->sPackageName]['css'][] = $sTemplateCssFileName;
    }

    /**
     * Publish a script file from general script directory, using or not the asset manager (depending on configuration)
     * In any twig file, you can register a general script file doing: {{ registerGeneralScript($sGeneralScriptFileName) }}
     * @param string $sGeneralScriptFileName name of the script file to publish in general script directory (it should contains the subdirectories)
     * @param string $position
     * @param array $htmlOptions
     */
    public static function registerGeneralScript($sGeneralScriptFileName, $position = null, array $htmlOptions = array())
    {
        $position = self::getPosition($position);
        Yii::app()->clientScript->registerScriptFile(
            App()->getConfig('generalscripts') .
            $sGeneralScriptFileName,
            $position,
            $htmlOptions
        );
    }

    /**
     * Publish a script file from template directory, using or not the asset manager (depending on configuration)
     * In any twig file, you can register a template script file doing: {{ registerTemplateScript($sTemplateScriptFileName) }}
     * @param string $sTemplateScriptFileName name of the script file to publish in general script directory (it should contains the subdirectories)
     * @param string $position
     * @param array $htmlOptions
     */
    public static function registerTemplateScript($sTemplateScriptFileName, $position = null, array $htmlOptions = array())
    {
        $oTemplate = self::getTemplateForRessource($sTemplateScriptFileName);
        Yii::app()->clientScript->packages[$oTemplate->sPackageName]['js'][] = $sTemplateScriptFileName;
    }

    /**
     * Publish a script
     * In any twig file, you can register a script doing: {{ registerScript($sId, $sScript) }}
     *
     * NOTE: this function is not recursive, so don't use it to register a script located inside a theme folder, or inherited themes will be broken.
     * NOTE! to register a script located inside a theme folder, registerTemplateScript()
     *
     */
    public static function registerScript($id, $script, $position = null, array $htmlOptions = array())
    {
        $position = self::getPosition($position);
        Yii::app()->clientScript->registerScript(
            $id,
            $script,
            $position,
            $htmlOptions
        );
    }

    /**
     * Convert a json object to a PHP array (so no troubles with object method in sandbox)
     * @param string $json
     * @param boolean $assoc return sub object as array too
     * @return array
     */
    public static function json_decode($json, $assoc = true)
    {
        return (array) json_decode((string)$json, $assoc);
    }

    /**
     * @param $position
     * @return string
     */
    public static function getPosition($position)
    {
        switch ($position) {
            case "POS_HEAD":
                $position = LSYii_ClientScript::POS_HEAD;
                break;

            case "POS_BEGIN":
                $position = LSYii_ClientScript::POS_BEGIN;
                break;

            case "POS_END":
                $position = LSYii_ClientScript::POS_END;
                break;

            case "POS_POSTSCRIPT":
                $position = LSYii_ClientScript::POS_POSTSCRIPT;
                break;

            default:
                $position = '';
                break;
        }

        return $position;
    }

    /**
     * since count with a noncountable element is throwing a warning in latest php versions
     * we have to be sure not to kill rendering by a wrong variable
     *
     * @param mixed $element
     * @return void
     */
    public static function safecount($element)
    {
        $isCountable = is_array($element) || $element instanceof Countable;
        if ($isCountable) {
            return count($element);
        }
        return 0;
    }
    /**
     * Retrieve the question classes for a given question id
     * Use in survey template question.twig file.
     * TODO: we'd rather provide a oQuestion object to the twig view with a method getAllQuestion(). But for now, this public static function respect the old way of doing
     *
     * @param  int      $iQid the question id
     * @return string   the classes
     * @deprecated must be removed when allow to broke template. Since it was in 3.0 , it was in API (and question.twig are surely be updated).
     */
    public static function getAllQuestionClasses($iQid)
    {

        $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($iQid);
        $sType           = $lemQuestionInfo['info']['type'];
        $aSGQA           = explode('X', (string) $lemQuestionInfo['sgqa']);
        $iSurveyId       = $aSGQA[0];

        $aQuestionClass  = Question::getQuestionClass($sType);

        /* Add the relevance class */
        if (!$lemQuestionInfo['relevant']) {
            $aQuestionClass .= ' ls-irrelevant';
            $aQuestionClass .= ' ls-hidden';
        }

        /* Can use aQuestionAttributes too */
        if ($lemQuestionInfo['hidden']) {
            $aQuestionClass .= ' ls-hidden-attribute'; /* another string ? */
            $aQuestionClass .= ' ls-hidden';
        }

        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes(Question::model()->findByPk($iQid));

        //add additional classes
        if (isset($aQuestionAttributes['cssclass']) && $aQuestionAttributes['cssclass'] != "") {
            /* Got to use static expression */
            $emCssClass = trim((string) LimeExpressionManager::ProcessString($aQuestionAttributes['cssclass'], null, array(), 1, 1, false, false, true)); /* static var is the lmast one ...*/
            if ($emCssClass != "") {
                $aQuestionClass .= " " . CHtml::encode($emCssClass);
            }
        }

        if ($lemQuestionInfo['info']['mandatory'] == 'Y' || $lemQuestionInfo['info']['mandatory'] == 'S') {
            $aQuestionClass .= ' mandatory';
        }

        if ($lemQuestionInfo['anyUnanswered'] && $_SESSION['survey_' . $iSurveyId]['maxstep'] != $_SESSION['survey_' . $iSurveyId]['step']) {
            $aQuestionClass .= ' missing';
        }

        return $aQuestionClass;
    }

    public static function renderCaptcha()
    {
        return App()->getController()->createWidget('LSCaptcha', array(
            'captchaAction' => 'captcha',
            'buttonOptions' => array('class' => 'btn btn-xs btn-info'),
            'buttonType' => 'button',
            'buttonLabel' => gT('Reload image', 'unescaped')
        ));
    }


    public static function createUrl($url, $params = array())
    {
        return App()->getController()->createUrl($url, $params);
    }

    public static function createAbsoluteUrl($url, $params = array())
    {
        return App()->getController()->createAbsoluteUrl($url, $params);
    }

    /**
     * @param string $sRessource
     */
    public static function assetPublish($sRessource)
    {
        return App()->assetManager->publish($sRessource);
    }

    /**
     * @var $sImagePath  string                 the image path relative to the template root
     * @var $alt         string                 the alternative text display
     * @var $htmlOptions array                  additional HTML attribute
     * @return string
     */
    public static function image($sImagePath, $alt = '', $htmlOptions = array( ))
    {
        $sUrlImgAsset = self::imageSrc($sImagePath, '');
        if (!$sUrlImgAsset) {
            return '';
        }
        return CHtml::image($sUrlImgAsset, $alt, $htmlOptions);
    }

    /**
     * @var $sImagePath  string                 the image path relative to the template root
     * @var $default     string|false                 an alternative image if the provided one cant be found
     * @return string|false
     */
    public static function imageSrc($sImagePath, $default = false)
    {
        // If $sImagePath is a 'virtual' path, we must get the real path.
        if (preg_match('/(image::\w+::)/', (string) $sImagePath, $m)) {
            $oTemplate =  Template::getLastInstance();
            Yii::import('application.helpers.SurveyThemeHelper');
            $sFullPath = SurveyThemeHelper::getRealThemeFilePath($sImagePath, $oTemplate->template_name, $oTemplate->sid);
        } else {
            // If it's not a virtual path, only paths relative to a theme (and within the theme) are allowed.
            // Recurrence on templates to find the file
            $oTemplate = self::getTemplateForRessource($sImagePath);
            $sUrlImgAsset =  $sImagePath;

            if ($oTemplate) {
                $sFullPath = $oTemplate->path . $sImagePath;
            }
        }

        if (empty($sFullPath)) {
            if ($default) {
                return self::imageSrc($default);
            }
            return false;
        }

        // check if this is a true image
        $checkImage = LSYii_ImageValidator::validateImage($sFullPath);

        if (!$checkImage['check']) {
            return false;
        }

        $sUrlImgAsset = self::assetPublish($sFullPath);
        return $sUrlImgAsset;
    }

    /**
     * @var $resourcePath string : the needed resource
     * @var $default string : the default resource if needed resssource didn't exist
     * @return string|false
     */
    public static function templateResourceUrl($resourcePath, $default = false)
    {
        /* get extension of file in allowedthemeuploads */
        $aAllowExtensions = explode(',', (string) Yii::app()->getConfig('allowedthemeuploads'));
        $info = pathinfo((string) $resourcePath);
        if (!isset($info['extension']) || !in_array(strtolower($info['extension']), $aAllowExtensions)) {
            if ($default) {
                return self::templateResourceUrl($default);
            }
            return false;
        }
        // Reccurence on templates to find the file
        $oTemplate = self::getTemplateForRessource($resourcePath);
        if (empty($oTemplate)) {
            /* Didn't allow file out of template (diff with image) */
            return false;
        }
        $sFullPath = $oTemplate->path . $resourcePath;
        $resourceAsset = self::assetPublish($sFullPath);
        return $resourceAsset;
    }


    /**
     * Get the parsed output of the expression manger for a specific string
     *
     * @param String $sInString
     * @return String
     */
    public static function getExpressionManagerOutput($sInString)
    {
        templatereplace(flattenText($sInString));
        return LimeExpressionManager::GetLastPrettyPrintExpression();
    }

    /**
     * Get the textcontrol widget output for a specific string
     *
     * @param String $sInString
     * @return String
     */
    public static function getTextDisplayWidget($sInString, $name)
    {
        templatereplace(flattenText($sInString));
        $fullInString = LimeExpressionManager::GetLastPrettyPrintExpression();

        $widget = App()->getController()->widget('ext.admin.TextDisplaySwitch.TextDisplaySwitch', array(
            'widgetsJsName' =>  $name,
            'textToDisplay' => $fullInString,
            'returnHtml' => true
        ));
        return $widget->run();
    }



    /**
     * Checks for a permission on render
     *
     * @param String $permission
     * @param String $permissionGrade
     * @param Integer|NULL $iSurveyId (default null)
     *
     * @return Boolean
     */
    public static function checkPermission($permission, $permissionGrade, $iSurveyId = null)
    {

        if ($iSurveyId === null) {
            return Permission::model()->hasGlobalPermission($permission, $permissionGrade);
        }
        return Permission::model()->hasSurveyPermission($iSurveyId, $permission, $permissionGrade);
    }

    /**
     * @param string $sRessource
     */
    public static function getTemplateForRessource($sRessource)
    {
        $oRTemplate =  Template::getLastInstance();

        while (!file_exists($oRTemplate->path . $sRessource)) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                return false;
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }
        $sRessourcePath = realpath($oRTemplate->path . $sRessource);
        $sTemplatePath = realpath($oRTemplate->path);
        if (substr($sTemplatePath, 0, strlen($sTemplatePath)) !== $sTemplatePath) {
            return false;
        }
        return $oRTemplate;
    }

    public static function getPost($sName, $sDefaultValue = null)
    {
        return Yii::app()->request->getPost($sName, $sDefaultValue);
    }

    public static function getParam($sName, $sDefaultValue = null)
    {
        return Yii::app()->request->getParam($sName, $sDefaultValue);
    }

    public static function getQuery($sName, $sDefaultValue = null)
    {
        return Yii::app()->request->getQuery($sName, $sDefaultValue);
    }

    /**
     * @param string $name
     */
    public static function unregisterPackage($name)
    {
        return Yii::app()->clientScript->unregisterPackage($name);
    }

    /**
     * @param string $name
     */
    public static function unregisterScriptFile($name)
    {
        return Yii::app()->clientScript->unregisterScriptFile($name);
    }

    public static function registerScriptFile($path, $position = null)
    {

        Yii::app()->clientScript->registerScriptFile($path, ($position === null ? LSYii_ClientScript::POS_BEGIN : self::getPosition($position)));
    }

    public static function registerCssFile($path)
    {
        Yii::app()->clientScript->registerCssFile($path);
    }

    public static function registerPackage($name)
    {
        Yii::app()->clientScript->registerPackage($name, LSYii_ClientScript::POS_BEGIN);
    }

    /**
     * Unregister all packages/script files for AJAX rendering
     */
    public static function unregisterScriptForAjax()
    {
        $oTemplate            = Template::getLastInstance();
        $sTemplatePackageName = 'limesurvey-' . $oTemplate->sTemplateName;
        self::unregisterPackage($sTemplatePackageName);
        self::unregisterPackage('template-core');
        self::unregisterPackage('bootstrap');
        self::unregisterPackage('jquery');
        self::unregisterPackage('bootstrap-template');
        self::unregisterPackage('fontawesome');
        self::unregisterPackage('template-default-ltr');
        self::unregisterPackage('decimal');
        self::unregisterPackage('expressions');
        self::unregisterPackage('expressionscript');
        self::unregisterScriptFile('/assets/scripts/survey_runtime.js');
        self::unregisterScriptFile('/assets/scripts/nojs.js');
    }

    public static function listCoreScripts()
    {
        foreach (Yii::app()->clientScript->coreScripts as $key => $package) {
            echo "<hr>";
            echo "$key: <br>";
            var_dump($package);
        }
    }

    public static function listScriptFiles()
    {
        foreach (Yii::app()->clientScript->getScriptFiles() as $key => $file) {
            echo "<hr>";
            echo "$key: <br>";
            var_dump($file);
        }
    }

    /**
     * Process any string with current page
     * @param string to be processed
     * @param boolean $static return static string (or not)
     * @param integer $numRecursionLevels recursion (max) level to do
     * @param array $aReplacement replacement out of EM
     * @return string
     */
    public static function processString($string, $static = false, $numRecursionLevels = 3, $aReplacement = array())
    {
        if (!is_string($string)) {
            /* Add some errors in template editor , see #13532 too */
            if (Yii::app()->getController()->getId() == 'admin' && Yii::app()->getController()->getAction()->getId() == 'themes') {
                Yii::app()->setFlashMessage(gT("Usage of processString without a string in your template"), 'error');
            }
            return;
        }
        return LimeExpressionManager::ProcessStepString($string, $aReplacement, $numRecursionLevels, $static);
    }

    /**
     * Get html text and remove whole not clean string
     * @param string $string to flatten
     * @param boolean $encode html entities
     * @return string
     */
    public static function flatString($string, $encode = false)
    {
        // Remove script before removing tag, no tag : no other script (onload, on error etc …
        $string = strip_tags(stripJavaScript($string));
        // Remove new lines
        if (version_compare(substr(PCRE_VERSION, 0, strpos(PCRE_VERSION, ' ')), '7.0') > -1) {
            $string = preg_replace(array('~\R~u'), array(' '), $string);
        } else {
            $string = str_replace(array("\r\n", "\n", "\r"), array(' ', ' ', ' '), $string);
        }
        // White space to real space
        $string = preg_replace('/\s+/', ' ', $string);

        if ($encode) {
            return \CHtml::encode($string);
        }
        return $string;
    }

    /**
     * get flat and ellipsize string
     * @param string $string to ellipsize
     * @param integer $maxlength of the final string
     * @param float $position of the ellipsis in string (between 0 and 1)
     * @param string $ellipsis string to shown in place of removed part
     * @return string
     */
    public static function ellipsizeString($string, $maxlength, $position = 1, $ellipsis = '…')
    {
        $string = self::flatString($string, false);
        $string = ellipsize($string, $maxlength, $position, $ellipsis);// Use common_helper function
        return $string;
    }

    /**
     * flat and ellipsize text, for template compatibility
     * @deprecated (4.0)
     * @param string $sString :the string
     * @param boolean $bFlat : flattenText or not : completely flat (not like flattenText from common_helper)
     * @param integer $iAbbreviated : max string text (if true : always flat), 0 or false : don't abbreviated
     * @param string $sEllipsis if abbreviated : the char to put at end (or middle)
     * @param integer $fPosition if abbreviated position to split (in % : 0 to 1)
     * @return string
     */
    public static function flatEllipsizeText($sString, $bFlat = true, $iAbbreviated = 0, $sEllipsis = '...', $fPosition = 1)
    {
        if (!$bFlat && !$iAbbreviated) {
            return (string) $sString;
        }
        $sString = self::flatString($sString);
        if ($iAbbreviated > 0) {
            $sString = ellipsize($sString, $iAbbreviated, $fPosition, $sEllipsis);
        }
        return (string) $sString;
    }

    public static function darkencss($cssColor, $grade = 10, $alpha = 1)
    {

        $aColors = str_split(substr((string) $cssColor, 1), 2);
        $return = [];
        foreach ($aColors as $color) {
            $decColor = hexdec($color);
            $decColor = $decColor - $grade;
            $decColor = $decColor < 0 ? 0 : ($decColor > 255 ? 255 : $decColor);
            $return[] = $decColor;
        }
        if ($alpha === 1) {
            return '#' . join('', array_map(function ($val) {
                return dechex($val);
            }, $return));
        }

        return 'rgba(' . join(', ', $return) . ',' . $alpha . ')';
    }

    /**
     * Check if a needle is in a multidimensional array
     * @param mixed $needle The searched value.
     * @param array $haystack The array.
     * @param bool $strict If the third parameter strict is set to TRUE then the in_array() function will also check the types of the needle in the haystack.
     * @todo in_array_r is not defined - delete this method?
     */
    function in_multiarray($needle, $haystack, $strict = false)
    {

        foreach ($haystack as $item) {
            /** @psalm-suppress UndefinedFunction */
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }


    public static function lightencss($cssColor, $grade = 10, $alpha = 1)
    {
        $aColors = str_split(substr((string) $cssColor, 1), 2);
        $return = [];
        foreach ($aColors as $color) {
            $decColor = hexdec($color);
            $decColor = $decColor + $grade;
            $decColor = $decColor < 0 ? 0 : ($decColor > 255 ? 255 : $decColor);
            $return[] = $decColor;
        }
        if ($alpha === 1) {
            return '#' . join('', array_map(function ($val) {
                return dechex($val);
            }, $return));
        }

        return 'rgba(' . join(', ', $return) . ',' . $alpha . ')';
    }

    public static function getConfig($item)
    {
        return Yii::app()->getConfig($item);
    }


    /**
     * Retrieve all the previous answers from a given token
     * To use it:
     *  {% set aResponses = getAllTokenAnswers(aSurveyInfo.sid) %}
     *  {{ dump(aResponses) }}
     *
     *  Of course, the survey must use token. If you want to show it after completion, the you must turn on public statistics
     */
    public static function getAllTokenAnswers($iSurveyID)
    {

        $oResponses = SurveyDynamic::model($iSurveyID)->findAll(
            array(
                                'condition' => 'token = :token',
                                'params'    => array( ':token' => $_SESSION['survey_' . $iSurveyID]['token']),
                            )
        );

        $aResponses = array();

        if (count($oResponses) > 0) {
            foreach ($oResponses as $oResponse) {
                array_push($aResponses, $oResponse->attributes);
            }
        }

        return $aResponses;
    }

    /**
     * Returns true if the language uses RTL writing system.
     */
    public static function getLanguageRTL($sLanguageCode)
    {
        Yii::app()->loadHelper('surveytranslator');
        return getLanguageRTL($sLanguageCode);
    }

    /**
     * Returns the "tracking url" for Google Analytics when style is "Survey-SID/GROUP"
     * @param int $surveyId
     * @param string $trackUrlPageName  Specific page name to include in the tracking url. If it's empty, we will try to infer it from the context.
     * @return string The tracking URL as "<survey name>-[<survey ID>]/[<page name|group seq>]-<group name>"
     */
    public static function getGoogleAnalyticsTrackingUrl($surveyId, $trackUrlPageName = '')
    {
        $survey = Survey::model()->findByPk($surveyId);

        $googleAnalyticsAPIKey = $survey->getGoogleanalyticsapikey();
        $googleAnalyticsStyle = isset($survey->googleanalyticsstyle) ? $survey->googleanalyticsstyle : '1';

        // Tracking URL can only be used if there is an API key and the style is set to "Survey-SID/GROUP"
        if ($googleAnalyticsAPIKey == '' || $googleAnalyticsStyle != 2) {
            return '';
        }

        $surveyName = $survey->localizedTitle;
        $groupName = '';

        // If a page name is specified, use that. Otherwise, try to get it from the context.
        if (!empty($trackUrlPageName)) {
            $page = $trackUrlPageName;
        } else {
            $moveInfo = LimeExpressionManager::GetLastMoveResult();
            if (is_null($moveInfo) || (isset($moveInfo['at_start']) && $moveInfo['at_start'])) {
                $page = 'welcome';
            } elseif ($moveInfo['finished']) {
                $page = 'finished';
            } else {
                $showgroupinfo = Yii::app()->getConfig('showgroupinfo');
                if ($survey->format == 'A') {
                    $page = 1;
                } else {
                    if (
                        $showgroupinfo == 'both'
                        || $showgroupinfo == 'name'
                        || ($showgroupinfo == 'choose' && !isset($survey->showgroupinfo))
                        || ($showgroupinfo == 'choose' && $survey->showgroupinfo == 'B')
                        || ($showgroupinfo == 'choose' && $survey->showgroupinfo == 'N')
                    ) {
                        $groupInfo = LimeExpressionManager::GetStepIndexInfo($moveInfo['seq']);
                        $groupName = isset($groupInfo['gname']) ? $groupInfo['gname'] : '';
                    }
                    $page = $moveInfo['gseq'] + 1;
                };
            }
        }

        $trackURL = htmlspecialchars($surveyName . '-[' . $surveyId . ']/[' . $page . ']-' . $groupName);
        return $trackURL;
    }
}
