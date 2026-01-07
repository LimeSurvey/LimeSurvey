<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
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

class LSYii_HtmlPurifier extends CHtmlPurifier
{

    /**
     * Get the config object for the HTML Purifier instance.
     * @return mixed the HTML Purifier instance config
     */
    public function getConfig()
    {
        $purifier = $this->getPurifier();
        if ($purifier !== null) {
            return $purifier->config;
        }
    }

    /**
     * Get an instance of LSYii_HtmlPurifier configured for XSS filtering
     */
    public static function getXssPurifier()
    {
        $instance = new self();
        $instance->options = array(
            'AutoFormat.RemoveEmpty' => false,
            'Core.NormalizeNewlines' => false,
            'CSS.AllowTricky' => true, // Allow display:none; (and other)
            'HTML.SafeObject' => true, // To allow including youtube
            'Output.FlashCompat' => true,
            'Attr.EnableID' => true, // Allow to set id
            'Attr.AllowedFrameTargets' => array('_blank', '_self'),
            'HTML.TargetNoopener' => true,
            'HTML.TargetNoreferrer' => true,
            'URI.AllowedSchemes' => array(
                'http' => true,
                'https' => true,
                'mailto' => true,
                'ftp' => true,
                'nntp' => true,
                'news' => true,
                )
        );
        // To allow script BUT purify : HTML.Trusted=true (plugin idea for admin or without XSS filtering ?)

        // Enable video
        $config = $instance->getConfig();

        if (!empty($config)) {
            $config->set('HTML.DefinitionID', 'html5-definitions');
            $def = $config->maybeGetRawHTMLDefinition();
            $max = $config->get('HTML.MaxImgLength');
            if ($def) {
                $def->addElement(
                    'video',   // name
                    'Inline',  // content set
                    'Flow', // allowed children
                    'Common', // attribute collection
                    array( // attributes
                        'src' => 'URI',
                        'id' => 'Text',
                        'poster' => 'Text',
                        'width' => 'Pixels#' . $max,
                        'height' => 'Pixels#' . $max,
                        'controls' => 'Bool#controls',
                        'autobuffer' => 'Bool#autobuffer',
                        'autoplay' => 'Bool#autoplay',
                        'loop' => 'Bool#loop',
                        'muted' => 'Bool#muted'
                    )
                );
                $def->addElement(
                    'source',   // name
                    'Inline',  // content set
                    'Empty', // allowed children
                    null, // attribute collection
                    array( // attributes
                        'src*' => 'URI',
                        'type' => 'Enum#video/mp4,video/webm',
                    )
                );
            }
        }
        
        return $instance;
    }
}
