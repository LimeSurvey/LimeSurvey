<?php

class DevController extends CController {
    
    public function actionIndex() {
        $lines = file(__DIR__ . '/../config/locales.php', FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES); 
        $regex = '/^\$supportedLanguages\[\'(..)\'\]\[\'nativedescription\'\].*\'(.*)\';$/';
        $closure = function($matches) {
            return strtr($matches[0], [$matches[2] => html_entity_decode($matches[2])]);
        };
        foreach ($lines as &$line) {
            $line = preg_replace_callback($regex, $closure, $line);
        }
        file_put_contents(__DIR__ . '/../config/locales.php', implode("\n", $lines));
//        $this->renderText('<pre>' . $result . '</pre>');
    }
}