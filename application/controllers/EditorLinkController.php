<?php

/*
 * LimeSurvey
 * Copyright (C) 2007-2026 The LimeSurvey Project Team
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
    public const REACT_APP_BASE_PATH = '/editor/#/';

    /**
     * Create react auth key cookie and redirect.
     *
     * @return void
     */
    public function run($action)
    {
        if (App()->user->isGuest) {
            throw new CHttpException(403, 'Access denied');
        }

        $this->setAuthenticationInitCookie();

        $editorUrl = rtrim(
            App()->request->baseUrl
            . static::REACT_APP_BASE_PATH,
            '/'
        );

        $editorRoute = App()->request->getQuery('route');
        $url = $editorUrl . '/' . $editorRoute;
        $this->redirect($url);
    }

    /**
     * Create and set react auth token to cookie.
     *
     * The cookie also carries the entry script name, see getScriptName().
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
            App()->session['user']
        );

        /** @var \LSYii_Application */
        $app = \App();

        $cookieData = $authTokenSimple->getTokenData(
            $session,
            $app->user->getId()
        );
        $cookieData['scriptName'] = $this->getScriptName();

        $cookieDataJson = json_encode($cookieData);

        $cookie = new CHttpCookie($cookieName, $cookieDataJson);
        $cookie->expire = time() + 10;

        App()->request->cookies[$cookieName] = $cookie;
    }

    /**
     * Returns the entry script name the editor has to put into its URLs.
     *
     * The editor is a static page, so it cannot know whether the URL manager
     * is configured to show the script name. Without the script name, the
     * URLs of the editor (REST API, links to the administration) only work
     * if the web server rewrites them to the entry script.
     *
     * @return string The script name (e.g. "index.php"), or an empty string if "showScriptName" is disabled
     */
    private function getScriptName()
    {
        if (!App()->getUrlManager()->showScriptName) {
            return '';
        }

        return basename(App()->request->getScriptUrl());
    }
}
