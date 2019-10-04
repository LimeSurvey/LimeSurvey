<?php

/**
 * php application/commands/console.php Twig generateTwigTmpFiles
 */

class TwigCommand extends CConsoleCommand  {

    public function actionIndex() {
        echo "Possible action : generateTwigTmpFiles(), ...\n";
    }

    public function actionGenerateTwigTmpFiles( ) {


      Yii::import('application.helpers.surveytranslator_helper', true);
      Yii::import('application.helpers.common_helper', true);
      Yii::import('application.helpers.expressions.em_manager_helper', true);
      Yii::app()->assetManager->setBasePath(realpath(__DIR__.'/../../tmp/'));

      Yii::app()->setConfig('force_xmlsettings_for_survey_rendering', true);

      $aLogs = array();

      // TODO: make this a parameter so the command line can be used for custom themes, question themes, etc; or create subfunctions ; or both
      $sThemeDir = dirname(__FILE__).'/../../themes/survey';

      $oThemeDir = new DirectoryIterator($sThemeDir);

      foreach ($oThemeDir as $fileinfo) {

        if ($fileinfo->getFilename() != ".." && $fileinfo->getFilename() != "." && $fileinfo->getFilename() != "index.html"){
        $templatename = $fileinfo->getFilename();

        $oTemplateForPreview = Template::getInstance($templatename, null, null, true, true)->prepareTemplateRendering($templatename, null, true);
        $thissurvey = $oTemplateForPreview->getDefaultDataForRendering();
        $thissurvey['templatedir'] = $templatename;

        $aScreenList = $oTemplateForPreview->getScreensDetails();

        foreach($aScreenList as $sScreenName => $aTitleAndLayouts){
          foreach($aTitleAndLayouts['layouts'] as $sLayout => $sContent){
            $aLogs[$templatename][$sScreenName][$sLayout] =  $sContent;
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

        // Render all the twig strings inside the XML itself
        $aTwigFromXml = $oTemplateForPreview->getTwigStrings();

        foreach($aTwigFromXml as $sTwig){
          Yii::app()->twigRenderer->convertTwigToHtml($sTwig);
        }

      }
    }
    // Here you can var dump the logs, it will not conflict with header generation
    //var_dump($aLogs);

    $this->actionGenerateQuestionTwigTmpFiles(null);
  }

  /**
   * Generate twig cache files for each question type.
   * NOTE 1: It's a recursive function, since some directories are the question type itself (it has an answer.twig file) but other containes various question types as subdirectories.
   * NOTE 2: Currenlty arrays are skipped. We need to set default data, so it will be done in LS4, at the same time than Question Theme Editor.
   *
   * @param string $sQuestionDir the directory to parse, where to find the answer.twig file.
   */
  public function actionGenerateQuestionTwigTmpFiles( $sQuestionDir=null )
  {
    // Generate cache for question theme
    $sQuestionDir = ($sQuestionDir===null)?dirname(__FILE__).'/../views/survey/questions/answer':$sQuestionDir;

    $oQuestionDir = new DirectoryIterator($sQuestionDir);

    foreach ($oQuestionDir as $fileinfo) {
      if ($fileinfo->getFilename() != ".." && $fileinfo->getFilename() != "." && $fileinfo->getFilename() != "index.html"){
        $sQuestionName = $fileinfo->getFilename();

        $sQuestionDirectory = $sQuestionDir.DIRECTORY_SEPARATOR.$sQuestionName;
        $sTwigFile = $sQuestionDirectory.DIRECTORY_SEPARATOR."answer.twig";
        if (file_exists($sTwigFile)){
          $line       = file_get_contents($sTwigFile);
          $sHtml      = Yii::app()->twigRenderer->convertTwigToHtml($line, array());
        }elseif(is_dir($sQuestionDirectory) && $sQuestionName != "arrays"){
          // Recursive step
          $this->actionGenerateQuestionTwigTmpFiles($sQuestionDirectory);
        }
      }
    }
  }
}
