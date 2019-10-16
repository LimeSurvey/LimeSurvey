<?php

namespace ls\tests\controllers;

use ls\tests\TestBaseClassView;

class NoGreenBarTest extends TestBaseClassWeb
{
	public function testNoGreenBar()
	{
		// Import suprvey.
		$surveyFile =  'tests/data/surveys/survey_archive_358746_no_green_bar.lsa';
		self::importSurvey($surveyFile);

		$web = self::$webDriver;

		 try {
		    $urlMan = \Yii::app()->urlManager;
		    $urlMan->setBaseUrl('http://' . self::$domain . '/index.php');
		    //http://localhost/limesurvey/index.php?r=admin/tokens/sa/addnew/surveyid/358746
		    $url = $urlMan->createUrl(
			'admin/tokens',
			array(
				'sa'=>'addnew',
				'surveyid'=>self::$testSurvey->sid,
			)
		    );
		    $web = self::$webDriver;
		    $web->get($url);
		    $input = $web->findById('firstname');
		    $input->sendKey('dummy name');
		    $savebutton = $web->findById('save');
		    $savebutton->click();
		    sleep(1);
		    try {
			    $web->findById('greenbar');
			    $this->assertTrue(true, 'all ok');
		    } catch (NoSuchElementException $ex) {
			    $this->assertTrue(false, 'could not find green bar');
		    }
		 } catch (\Exception $exception) {
		    self::$testHelper->takeScreenshot(self::$webDriver, __CLASS__ . '_' . __FUNCTION__);
		    $this->assertFalse(
			true,
			self::$testHelper->javaTrace($ex)
            );
		 }
	}
}
