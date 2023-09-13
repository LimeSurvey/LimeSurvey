<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */


use LimeSurvey\Api\Auth\AuthSession;

// phpcs:ignore
class ReactLinkController extends LSYii_Controller
{
    /**
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users' => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => [
                    'goto',
                ],
                'users' => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }

    /**
     * Create react auth key cookie and redirect.
     *
     * @return void
     */
    public function actionTo()
    {
        $this->setReactAuthKeyInitCookie();
        $route = Yii::app()->request->getQuery('route');
        $path = '/app/#' . $route;
        $url = Yii::app()->request->baseUrl . $path;
        $this->redirect($url);
    }
    /**
     * Create and set react auth token to cookie.
     *
     * @return void
     */
    private function setReactAuthKeyInitCookie()
    {
        $authSession = new AuthSession();
        $cookieName = 'reactAuthKeyInit';
        $reactAuthKeyInitCookie = $authSession->createSessionKey(
            Yii::app()->session['user']
        );
        $cookie = new CHttpCookie($cookieName, $reactAuthKeyInitCookie);
        $cookie->expire = time() + (60 * 2);
        Yii::app()->request->cookies[$cookieName] = $cookie;
    }
}
