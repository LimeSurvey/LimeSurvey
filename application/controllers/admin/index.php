<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CAction
{
	public function run()
	{
        $clang = $this->getController()->lang;

        if (Yii::app()->session['just_logged_in'])
        {
            $this->getController()->_showMessageBox($clang->gT("Logged in"), Yii::app()->session['loginsummary']);
            unset(Yii::app()->session['just_logged_in'], Yii::app()->session['loginsummary']);
        }

		//!!! Conversion pending
		if (count(getsurveylist(true)) == 0)
		{
			$data['clang']=$this->lang;
			$this->render("/admin/super/firststeps", $data);
		}
		else
			$this->getController()->render('/admin/index', array('clang' => $clang));
	}

}