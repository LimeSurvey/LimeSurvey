<?php

class SiteController extends CController
{
    public function actionIndex()
    {
        $this->render('hello-world', ['test' => 'LO']);
    }
}
