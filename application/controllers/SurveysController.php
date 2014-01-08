<?php

    /**
     * This class will handle survey creation and manipulation.
     */
    class SurveysController extends LSYii_Controller
    {
        public $layout = 'bare';
        public $defaultAction = 'publicList';
        public function actionPublicList()
        {
            $this->sessioncontrol();
            $this->render('publicSurveyList', array(
                'publicSurveys' => Survey::model()->active()->open()->public()->with('languagesettings')->findAll(),
                'futureSurveys' => Survey::model()->active()->registration()->public()->with('languagesettings')->findAll(),

            ));
            return;
            Yii::import('application.helpers.frontend_helper',  true);
            $languagechanger = makeLanguageChanger(App()->lang->langcode);
            //Find out if there are any publicly available surveys
            $sSqlDateNow=dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", Yii::app()->getConfig("timeadjust"));
            $aActiveSurvey = Yii::app()->db->createCommand()
                                    ->select('sid,surveyls_title,publicstatistics,language')
                                    ->from('{{surveys}}')
                                    ->join('{{surveys_languagesettings}}', 'sid = surveyls_survey_id AND language=surveyls_language')
                                    ->andWhere("active='Y'")
                                    ->andWhere("listpublic='Y'")
                                    ->andWhere("expires >= :expires OR expires is null")
                                    ->andWhere("startdate <= :startdate OR startdate is null")
                                    ->order("surveyls_title ASC")
                                    ->bindParam(':expires',$sSqlDateNow)
                                    ->bindParam(':startdate',$sSqlDateNow)
                                    ->queryAll();
            $list=array();


            foreach($aActiveSurvey as $rows)
            {
                $resultlang=SurveyLanguageSetting::model()->find(
                        "surveyls_survey_id=:surveyls_survey_id AND surveyls_language=:surveyls_language",
                        array(':surveyls_survey_id'=>intval($rows['sid']),':surveyls_language'=>$sDisplayLanguage)
                );
                $langparam=array();
                $langtag = "";
                if ($resultlang )
                {
                    $rows['surveyls_title']=$resultlang->surveyls_title;
                    $langparam=array('lang'=>$sDisplayLanguage);
                }
                else
                {
                    $langtag = "lang=\"{$rows['language']}\"";
                }
                $link = "<li><a href='".$this->getController()->createUrl('/survey/index/sid/'.$rows['sid'],$langparam);
                $link .= "' $langtag class='surveytitle'>".$rows['surveyls_title']."</a>\n";
                if ($rows['publicstatistics'] == 'Y') $link .= "<a href='".$this->getController()->createUrl("/statistics_user/action/surveyid/".$rows['sid'])."/language/".$sDisplayLanguage."'>(".$clang->gT('View statistics').")</a>";
                $link .= "</li>\n";
                $list[]=$link;
            }

            //Check for inactive surveys which allow public registration.
            // TODO add a new template replace {SURVEYREGISTERLIST} ?
#            $squery = "SELECT sid, surveyls_title, publicstatistics, language
#            FROM {{surveys}}
#            INNER JOIN {{surveys_languagesettings}}
#            ON (surveyls_survey_id = sid)
#            AND (surveyls_language=language)
#            WHERE allowregister='Y'
#            AND active='Y'
#            AND listpublic='Y'
#            AND ((expires >= '".date("Y-m-d H:i")."') OR (expires is null))
#            AND (startdate >= '".date("Y-m-d H:i")."')
#            ORDER BY surveyls_title";
#            $sresult = dbExecuteAssoc($squery) or safeDie("Couldn't execute $squery");
#            $aRows=$sresult->readAll();
            $aRegisteringBeforeSurveys = Yii::app()->db->createCommand()
                                    ->select('sid,surveyls_title,publicstatistics,language')
                                    ->from('{{surveys}}')
                                    ->join('{{surveys_languagesettings}}', 'sid = surveyls_survey_id AND language=surveyls_language')
                                    ->andWhere("active='Y'")
                                    ->andWhere("allowregister='Y'")// And if there are no token table ...
                                    ->andWhere("listpublic='Y'")
                                    ->andWhere("expires >= :expires OR expires is null")
                                    ->andWhere("startdate > :startdate")
                                    ->order("surveyls_title ASC")
                                    ->bindParam(':expires',$sSqlDateNow)
                                    ->bindParam(':startdate',$sSqlDateNow)
                                    ->queryAll();

            if(count($aRegisteringBeforeSurveys) > 0)
            {
                $list[] = "</ul>"
                ." <div class=\"survey-list-heading\">".$clang->gT("Following survey(s) are not yet active but you can register for them.")."</div>"
                ." <ul>"; // TODO give it to template
                foreach($aRegisteringBeforeSurveys as $aRegisteringBeforeSurvey)
                {
                    $oSurveyLang=SurveyLanguageSetting::model()->find(
                            "surveyls_survey_id=:surveyls_survey_id AND surveyls_language=:surveyls_language",
                            array(':surveyls_survey_id'=>intval($aRegisteringBeforeSurvey['sid']),':surveyls_language'=>$sDisplayLanguage)
                    );
                    if ($oSurveyLang )
                    {
                        $aRegisteringBeforeSurvey['surveyls_title']=$oSurveyLang->surveyls_title;
                        $langtag = "";
                    }
                    else
                    {
                        $langtag = "lang=\"{$aRegisteringBeforeSurvey['language']}\"";
                    }
                    $link = "<li><a data-inactivesurvey='".$aRegisteringBeforeSurvey['sid']."' $langtag class='surveytitle'> ";
                    $link .= $aRegisteringBeforeSurvey['surveyls_title']."</a>\n";
                    $link .= "</li><div data-regformsurvey='".$aRegisteringBeforeSurvey['sid']."'></div>\n";
                    $list[]=$link;
                }
                $sSendreqJs="$(document).on('click','a[data-inactivesurvey]',function(){\n"
                            ."var surveyid=$(this).data('inactivesurvey');\n"
                            ."var regform=$('[data-regformsurvey='+surveyid+']');\n"
                            ."$.ajax({\n"
                            ."type: 'GET',\n"
                            ."url: '".$this->getController()->createUrl("/register/ajaxregisterform")."',\n"
                            ."data: { 'surveyid' : surveyid}\n"
                            ."}).done(function(msg) {\n"
                            ."regform.html(msg);\n"
                            ."});\n"
                            ."});";
                App()->clientScript->registerScript('sSendreqJs',$sSendreqJs,CClientScript::POS_BEGIN);
            }

            if(count($list) < 1)
            {
                $list[]="<li class='surveytitle'>".$clang->gT("No available surveys")."</li>";
            }
            if(!$surveyid)
            {
                $thissurvey['name']=Yii::app()->getConfig("sitename");
                $nosid=$clang->gT("You have not provided a survey identification number");
            }
            else
            {
                $thissurvey['name']=$clang->gT("The survey identification number is invalid");
                $nosid=$clang->gT("The survey identification number is invalid");
            }
            $surveylist=array(
            "nosid"=>$nosid,
            "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),Yii::app()->getConfig("siteadminname"),encodeEmail(Yii::app()->getConfig("siteadminemail"))),
            "listheading"=>$clang->gT("The following surveys are available:"),
            "list"=>implode("\n",$list),
            );


            $data['thissurvey'] = $thissurvey;
            //$data['privacy'] = $privacy;
            $data['surveylist'] = $surveylist;
            $data['surveyid'] = $surveyid;
            $data['templatedir'] = getTemplatePath(Yii::app()->getConfig("defaulttemplate"));
            $data['templateurl'] = getTemplateURL(Yii::app()->getConfig("defaulttemplate"))."/";
            $data['templatename'] = Yii::app()->getConfig("defaulttemplate");
            $data['sitename'] = Yii::app()->getConfig("sitename");
            $data['languagechanger'] = $languagechanger;

            //A nice exit
            sendCacheHeaders();
            doHeader();
            $this->_printTemplateContent(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/startpage.pstpl", $data, __LINE__);

            $this->_printTemplateContent(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/surveylist.pstpl", $data, __LINE__);

            $this->_printTemplateContent(getTemplatePath(Yii::app()->getConfig("defaulttemplate"))."/endpage.pstpl", $data, __LINE__);
            doFooter();
            return;
        }


        /**
         * Load and set session vars
         * @todo Remove this ugly code. Language settings should be moved to Application instead of Controller.
         * @access protected
         * @return void
         */
        protected function sessioncontrol()
        {
            if (!Yii::app()->session["adminlang"] || Yii::app()->session["adminlang"]=='')
                Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

            Yii::import('application.libraries.Limesurvey_lang');
            Yii::app()->setLang(new Limesurvey_lang(Yii::app()->session['adminlang']));
        }
    }
?>