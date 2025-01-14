<?php
/**
 * HelloWorldModule class file.
 *
 * @author GititSurvey GmbH  <info@gitit-tech.com>
 * @link http://www.gitit-tech.com.com/
 * @copyright 2007-2019  The GititSurvey Project Team / Carsten Schmitz
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
