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

class EditorLinkController extends LSYii_Controller
{
    const REACT_APP_BASE_PATH = '/editor/#/';

    /**
     * Access Rules
     *
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
    public function run($action)
    {
        $this->setAuthInitCookie();
        $route = Yii::app()->request->getQuery('route');
        $path = static::REACT_APP_BASE_PATH . $route;
        $url = Yii::app()->request->baseUrl . $path;
        $this->redirect($url);
    }

    /**
     * Create and set react auth token to cookie.
     *
     * @return void
     */
    private function setAuthInitCookie()
    {
        $cookieName = 'LS_AUTH_INIT';

        $authSession = new AuthSession();
        $session = $authSession->createSession(
            Yii::app()->session['user']
        );

        $sessionExpires = new \DateTime(
            date('c', $session->expire)
        );

        $cookieDataJson = json_encode([
            'token' => $session->id,
            'expires' => $sessionExpires->format('Y-m-d\TH:i:s.000\Z')
        ]);

        $cookie = new CHttpCookie($cookieName, $cookieDataJson);
        $cookie->expire = time() + (60 * 2); // 2 minutes

        Yii::app()->request->cookies[$cookieName] = $cookie;
    }
}
