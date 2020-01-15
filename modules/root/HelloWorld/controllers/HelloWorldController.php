<?php

class HelloWorldController extends CController
{
	protected function _init()
	{
			parent::_init();
	}

	public function actionIndex()
	{
		 echo "Hello World";
	}

	public function actionHelloAdmin()
	{
		if (Permission::model()->hasGlobalPermission('superadmin')){
			echo "Hello Super Admin";
		}else{
			echo "you must first login as super admin";
		}

	}
}
