<?php

/**
 * This class will generate all the twig cache file from command line, even if LimeSurvey is not installed.
 * The current use case is to generate the translation files using Glot Press.
 * In the future, it could be use to boost performance of first survey rendering (can be useful after a tmp cleanup, an update, etc).
 *
 * To execute this command :
 * php application/commands/console.php Twig generateTwigTmpFiles
 *
 * It will execute all the functions
 */

class TwigCommand extends CConsoleCommand  {

    public $aLogs; // Array of logs

    /**
     * Load the needed helpers, set default vaules, etc
     */
    public function init()
    {
      // Needed helpers for correct rendering
      Yii::import('application.helpers.surveytranslator_helper', true);
      Yii::import('application.helpers.common_helper', true);
      Yii::import('application.helpers.expressions.em_manager_helper', true);
      Yii::import('application.helpers.admin.htmleditor_helper', true);

      // Directories where the assets will be created.
      Yii::app()->assetManager->setBasePath(realpath(__DIR__.'/../../tmp/assets/'));

      // This command can be used even with no DB installed. So we force the usage of config.xml rather than DB entries
      Yii::app()->setConfig('force_xmlsettings_for_survey_rendering', true);
    }

    public function actionIndex()
    {
        echo "This class will generate all the twig cache file from command line, even if LimeSurvey is not installed.\n";
        echo "The current use case is to generate the translation files using Glot Press.\n";
        echo "In the future, it could be use to boost performance of first survey rendering (can be useful after a tmp cleanup, an update, etc).\n";
        echo "\n";
        echo "\n";
        echo "To execute this command :\n";
        echo "php application/commands/console.php Twig generateTwigTmpFiles \n";

    }

    /**
     * Generate twig cache files for each core Survey Theme and core questions views.
     *
     */
    public function actionGenerateTwigTmpFiles( $sThemeDir=null, $bGenerateSurveyCache=true, $bGenerateQuestionsCache=true, $bGenerateAdminCache=true, $bShowLogs=false )
    {
      $this->aLogs = array();
      $this->aLogs["action"] = "actionGenerateTwigTmpFiles $sThemeDir $bGenerateSurveyCache $bGenerateQuestionsCache $bGenerateAdminCache $bShowLogs";
      if ($bGenerateSurveyCache){
        $this->actionGenerateSurveyThemesCache($sThemeDir );
      }

      if ($bGenerateQuestionsCache){
        $this->actionGenerateQuestionsCache(null);
      }

      if ($bGenerateAdminCache){
        $this->actionGenerateAdminCache(null);
      }

      // TODO: here add something more complex to create a file log on the server, something that can be return to the CU server at release creation, etc
      if ($bShowLogs){
        var_dump($this->aLogs);
      }
    }

    /**
     * Generate twig cache files for each core survey theme
     *
     * @param string $sThemeDir the directory to parse, where to find the manifests.
     */
    public function actionGenerateSurveyThemesCache($sThemeDir=null)
    {
      $this->aLogs["action"] = "actionGenerateSurveyThemesCache $sThemeDir";

      // NOTE 1: by default used only for core theme.
      // NOTE 2: Later, we'll can use this function to offer to generate .po files for themes developers
      $sThemeDir = ($sThemeDir==null) ? dirname(__FILE__).'/../../themes/survey':$sThemeDir;
      $oThemeDir = new DirectoryIterator($sThemeDir);

      foreach ($oThemeDir as $fileinfo) {
        if ($fileinfo->getFilename() != ".." && $fileinfo->getFilename() != "." && $fileinfo->getFilename() != "index.html"){

          $templatename = $fileinfo->getFilename();
          $oTemplateForPreview = Template::getInstance($templatename, null, null, true, true)->prepareTemplateRendering($templatename, null, true);

          // Render Survey theme
          $this->renderSurveyTheme($oTemplateForPreview);

          // Render all the twig strings inside the XML itself
          $this->renderSurveyThemeManifest($oTemplateForPreview);
        }
      }
    }

    /**
    * Generate twig cache files for each question type.
    * NOTE 1: It's a recursive function, since some directories are the question type itself (it has an answer.twig file) but other containes various question types as subdirectories.
    * NOTE 2: Currenlty arrays are skipped. We need to set default data, so it will be done in LS4, at the same time than Question Theme Editor.
    *
    * @param string $sQuestionDir the directory to parse, where to find the answer.twig file.
    */
    public function actionGenerateQuestionsCache( $sQuestionDir=null )
    {

      $this->aLogs["action"] = "actionGenerateQuestionsCache $sQuestionDir";

      // Generate cache for question theme
      $sQuestionDir = ($sQuestionDir===null)?dirname(__FILE__).'/../views/survey/questions/answer':$sQuestionDir;
      $oQuestionDir = new DirectoryIterator($sQuestionDir);

      foreach ($oQuestionDir as $fileinfo) {
        if ($fileinfo->getFilename() != ".." && $fileinfo->getFilename() != "." && $fileinfo->getFilename() != "index.html"){
          $sQuestionName = $fileinfo->getFilename();

          $sQuestionDirectory = $sQuestionDir.DIRECTORY_SEPARATOR.$sQuestionName;

          /**
           * TODO for ls4/ls5:
           *  - get the Question XML rather than answer.twig
           *  - load the default data from the XML
           *
           * NOTE 1: as long as this is not done, it's highly probable that some twig files will never be reached.
           *
           * NOTE 2: It should be possible to parse the XML to get the different values for the attributes, and then to generate a cache file for each attribue possible value.
           *         Doing this could allow to test easely the rendering for all question type, with all question attribute variations.
           *         Since we're very far to get this with Unit Test (it will imlpy to write around 1000 tests in a row), it could be a first step.
           *         One way to do: for a stable version, save the rendered HTML somwhere, then in unitest, call this function, compare the rendered HTML to the saved one.
           *         Enjoy the 1000 test in a single one :) (sadly, only for HTML rendering, not for JS or DB saving)
           */
          $sTwigFile = $sQuestionDirectory.DIRECTORY_SEPARATOR."answer.twig";
          $aQuestionData = array(); // See todo
          if (file_exists($sTwigFile)){
            $this->aLogs[$sQuestionName] = "$sTwigFile";
            $line       = file_get_contents($sTwigFile);
            $sHtml      = Yii::app()->twigRenderer->convertTwigToHtml($line, $aQuestionData);
          }elseif(is_dir($sQuestionDirectory) && $sQuestionName != "arrays"){
            // Recursive step
            $this->actionGenerateQuestionsCache($sQuestionDirectory);
          }
        }
      }
    }

    /**
    * Generate twig cache files for admin views.
    * NOTE: It's a recursive function which build every twig file in admin area.
    *
    * @param string $sAdminDir the directory to parse, where to find the twig files.
    */
    public function actionGenerateAdminCache( $sAdminDir=null )
    {

      $this->aLogs["action"] = "actionGenerateAdminCache $sAdminDir";

      // Generate cache for admin area
      $sAdminDir = ($sAdminDir===null)?dirname(__FILE__).'/../views/admin':$sAdminDir;
      $oAdminDirectory = new RecursiveDirectoryIterator($sAdminDir);
      $oAdminIterator = new RecursiveIteratorIterator($oAdminDirectory);
      $oAdminRegex = new RegexIterator($oAdminIterator, '/^.+\.twig$/i', RecursiveRegexIterator::GET_MATCH);

      $aAdminData = array();
      foreach ($oAdminRegex as $oTwigFile) {
        $sTwigFile = $oTwigFile[0];
        if (file_exists($sTwigFile)){
          $this->aLogs["twig"] = "$sTwigFile";
          $line       = file_get_contents($sTwigFile);
          $sHtml      = Yii::app()->twigRenderer->convertTwigToHtml($line);
        }
      }
    }

    /**
     * Generate the cache for a given survey theme
     * @param TemplateManifest
     */
    private function renderSurveyTheme($oTemplateForPreview)
    {
      $thissurvey = $oTemplateForPreview->getDefaultDataForRendering();
      $thissurvey['templatedir'] = $oTemplateForPreview->sTemplateName; // $templatename;

      $aScreenList = $oTemplateForPreview->getScreensDetails();

      foreach($aScreenList as $sScreenName => $aTitleAndLayouts){
        foreach($aTitleAndLayouts['layouts'] as $sLayout => $sContent){
          $this->aLogs[$oTemplateForPreview->sTemplateName][$sScreenName][$sLayout] =  $sContent;
          $sLayoutFile  = $sLayout ;
          $thissurvey['include_content'] = $sContent;

          $myoutput = Yii::app()->twigRenderer->renderTemplateForTemplateEditor(
                $sLayoutFile,
                array(
                    'aSurveyInfo' =>$thissurvey,
                  ),
                  $oTemplateForPreview
          );
        }
      }
    }

    /**
     * Generate the cache for the twig strings inside the manifest itself
     * @param TemplateManifest
     */
    private function renderSurveyThemeManifest($oTemplateForPreview)
    {
      // So the twig string inside the theme manifest will be added to the .po file
      $aTwigFromXml = $oTemplateForPreview->getTwigStrings();

      foreach($aTwigFromXml as $sTwig){
        Yii::app()->twigRenderer->convertTwigToHtml($sTwig);
      }

      $this->aLogs[$oTemplateForPreview->sTemplateName]['manifest'] =  "done";
    }
}
