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


class LS_Twig_Extension extends Twig_Extension
{

    /**
     * Publish a css file from public style directory, using or not the asset manager (depending on configuration)
     * In any twig file, you can register a public css file doing: {{ registerPublicCssFile($sPublicCssFileName) }}
     * @param string $sPublicCssFileName name of the CSS file to publish in public style directory
     */
    public static function registerPublicCssFile($sPublicCssFileName)
    {
        if (!YII_DEBUG ||  Yii::app()->getConfig('use_asset_manager')){

            // Publish the css file as an asset and then register it
            Yii::app()->getClientScript()->registerCssFile(                     // 2. Register the CSS file (add the <link rel="stylesheet" type="text/css" href=".../tmp/.../file.css"... to the HTML page)
                Yii::app()->getAssetManager()->publish(                         // 1. Publish the asset     (copy the file.css to tmp/.../assets/... directory)
                    Yii::app()->getConfig('publicstylepath') .                  // NOTE: assets needs a path, not an url, because the file is first moved to the tmp directory
                    $sPublicCssFileName
                )
            );
        }else{

            // Directly register the CSS file without using the asset manager
            Yii::app()->getClientScript()->registerCssFile(
                Yii::app()->getConfig('publicstyleurl') .                       // NOTE: URL can be use, because it will only add a link to that url (add the <link rel="stylesheet" type="text/css" href="url.../file.css")
                $sPublicCssFileName
            );
        }
    }

    /**
     * Retreive the question classes for a given question id
     * Use in survey template question.twig file.
     * TODO: we'd rather provide a oQuestion object to the twig view with a method getAllQuestion(). But for now, this public static function respect the old way of doing
     *
     * @param  int      $iQid the question id
     * @return string   the classes
     */
    public static function getAllQuestionClasses($iQid)
    {

        $lemQuestionInfo = LimeExpressionManager::GetQuestionStatus($iQid);
        $sType           = $lemQuestionInfo['info']['type'];
        $aSGQA           = explode( 'X', $lemQuestionInfo['sgqa'] );
        $iSurveyId       = $aSGQA[0];

        $aQuestionClass  = Question::getQuestionClass($sType);

        /* Add the relevance class */
        if (!$lemQuestionInfo['relevant']){
            $aQuestionClass .= ' ls-unrelevant';
            $aQuestionClass .= ' ls-hidden';
        }

        if ($lemQuestionInfo['hidden']){ /* Can use aQuestionAttributes too */
            $aQuestionClass .= ' ls-hidden-attribute';/* another string ? */
            $aQuestionClass .= ' ls-hidden';
        }

        $aQuestionAttributes = getQuestionAttributeValues($iQid);

        //add additional classes
        if(isset($aQuestionAttributes['cssclass']) && $aQuestionAttributes['cssclass']!=""){
            /* Got to use static expression */
            $emCssClass = trim(LimeExpressionManager::ProcessString($aQuestionAttributes['cssclass'], null, array(), false, 1, 1, false, false, true));/* static var is the lmast one ...*/
            if($emCssClass != ""){
                $aQuestionClass .= Chtml::encode($emCssClass);
            }
        }

        if ($lemQuestionInfo['info']['mandatory'] == 'Y'){
            $aQuestionClass .= ' mandatory';
        }

        if ($lemQuestionInfo['anyUnanswered'] && $_SESSION['survey_' . $iSurveyId]['maxstep'] != $_SESSION['survey_' . $iSurveyId]['step']){
            $aQuestionClass .= ' missing';
        }

        return $aQuestionClass;
    }

    public static function renderCaptcha()
    {
        App()->getController()->widget('CCaptcha',array(
        'buttonOptions'=>array('class'=> 'btn btn-xs btn-info'),
        'buttonType' => 'button',
        'buttonLabel' => gt('Reload image','unescaped')
        ));
    }


}
