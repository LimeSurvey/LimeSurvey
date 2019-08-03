<?php
/**
 * HelloWorldModule class file.
 *
 * @author Louis Gac  <louis.gac@limesurvey.org>
 * @link http://www.limesurvey.org.com/
 * @copyright 2007-2019  The LimeSurvey Project Team / Carsten Schmitz
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
