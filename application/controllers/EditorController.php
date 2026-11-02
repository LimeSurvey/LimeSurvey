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

/**
 * Serves the entry page of the survey editor single page application under the administration URL prefix
 *
 * The page is editor/index.html, built with asset paths relative to the editor directory.
 * Serving it from /index.php/admin/editor puts the editor behind the same URL path as the rest
 * of the administration (web server authentication protecting /index.php/admin covers it)
 * while the built assets stay in /editor/build: a <base> tag pointing to the editor directory
 * resolves the relative paths, the editor's hash router takes a <base> tag into account.
 * EditorLinkController sets the editor's authentication cookie and redirects here.
 */
class EditorController extends LSBaseController implements AdminControllerInterface
{
    /**
     * Output the editor page
     *
     * @return void
     * @throws CHttpException 404 when the editor is not installed
     */
    public function actionIndex()
    {
        $indexFile = dirname(Yii::app()->getBasePath()) . DIRECTORY_SEPARATOR . 'editor' . DIRECTORY_SEPARATOR . 'index.html';
        if (!is_file($indexFile)) {
            throw new CHttpException(404, gT("The survey editor is not installed."));
        }
        $baseHref = CHtml::encode(App()->request->baseUrl . '/editor/');
        $html = preg_replace(
            '/<head(\s[^>]*)?>/i',
            '$0<base href="' . $baseHref . '">',
            file_get_contents($indexFile),
            1
        );
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
    }
}
