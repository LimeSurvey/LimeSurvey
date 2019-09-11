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

        $aScreenList = $oTemplateForPreview->getScreenListWithLayoutAndContent();

        foreach($aScreenList as $sScreenName => $aLayoutAndContent){
          foreach($aLayoutAndContent as $sLayout => $sContent){
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
        }
      }

      // Here you can var dump the logs, it will not conflict with header generation
    //  var_dump($aLogs);
    }
}
