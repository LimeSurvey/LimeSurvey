<?php
/**
 * HelloWorldModule class file.
 *
 * @author LimeSurvey GmbH  <info@limesurvey.org>
 * @link http://www.limesurvey.org.com/
 * @copyright 2007-2026  The LimeSurvey Project Team
 * @license GNU General Public License See COPYRIGHT.php for copyright notices and details.
 */

 class HelloWorldModule extends CWebModule
 {

   public $defaultController='HelloWorld';

   public function init()
   {
     parent::init();
     Yii::setPathOfAlias('helloworld',dirname(__FILE__));
   }

 }
