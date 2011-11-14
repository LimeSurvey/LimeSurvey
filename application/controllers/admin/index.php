<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CAction
{
	public function run()
	{
        $clang = $this->getController()->lang;

		$this->getController()->_getAdminHeader();
		$this->getController()->_showadminmenu();

        if (Yii::app()->session['just_logged_in'])
        {
            $this->getController()->_showMessageBox($clang->gT("Logged in"), Yii::app()->session['loginsummary']);
            unset(Yii::app()->session['just_logged_in'], Yii::app()->session['loginsummary']);
        }

		if (count(getsurveylist(true)) == 0)
		{
			$data['clang']=$this->lang;
			$this->getController()->render("/admin/super/firststeps", $data);
		}

		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	}

}