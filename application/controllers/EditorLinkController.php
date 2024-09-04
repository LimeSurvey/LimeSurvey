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

use LimeSurvey\DI;
use LimeSurvey\Api\Authentication\AuthenticationTokenSimple;

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
        $this->setAuthenticationInitCookie();
        $editorUrl = Yii::app()->request->getQuery(
            'url',
            Yii::app()->request->baseUrl
                . static::REACT_APP_BASE_PATH
        );
        $editorRoute = Yii::app()->request->getQuery('route');
        $url = $editorUrl . $editorRoute;
        $this->redirect($url);
    }

    /**
     * Create and set react auth token to cookie.
     *
     * @return void
     */
    private function setAuthenticationInitCookie()
    {
        $diContainer = DI::getContainer();

        $cookieName = 'LS_AUTH_INIT';

        $authTokenSimple = $diContainer->get(
            AuthenticationTokenSimple::class
        );
        $session = $authTokenSimple->createSession(
            Yii::app()->session['user']
        );

        /** @var \LSYii_Application */
        $app = \Yii::app();

        $cookieDataJson = json_encode(
            $authTokenSimple->getTokenData(
                $session,
                $app->user->getId()
            )
        );

        $cookie = new CHttpCookie($cookieName, $cookieDataJson);
        $cookie->expire = time() + (60 * 2); // 2 minutes

        Yii::app()->request->cookies[$cookieName] = $cookie;
    }
}
