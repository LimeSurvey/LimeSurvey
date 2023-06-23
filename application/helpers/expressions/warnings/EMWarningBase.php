<?php

/**
 * LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

class EMWarningBase implements EMWarningInterface
{
    /**
     * @var array
     */
    public $token;

    /**
     * @var string
     */
    public $msg;

    /**
     * @var string
     */
    public $helpLink;

    /**
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->msg;
    }

    /**
     * @return string
     */
    public function getHelpLink()
    {
        return $this->helpLink;
    }

    /**
     * @return boolean
     */
    public function hasHelpLink()
    {
        return !empty($this->helpLink);
    }

    /**
     * @return string
     */
    public function bakeHelpLink()
    {
        if ($this->hasHelpLink()) {
            return CHtml::link(
                $this->msg,
                $this->helpLink,
                array("target" => "_blank",'class' => 'text-danger')
            );
        } else {
            return '[no help link]';
        }
    }
}
