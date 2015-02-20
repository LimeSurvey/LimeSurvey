<?php
    
    foreach($authenticators as $authenticator) {
        /* @var ls\pluginmanager\iAuthenticationPlugin $authenticator */
        if ($authenticator->enumerable()) {
            $this->renderPartial('userList', ['authenticator' => $authenticator]);
        } else {
            echo TbHtml::tag('h3', [], "Authenticator {$authenticator->name} does not support enumeration.");
        }
    }
