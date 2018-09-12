<?php

    /**
     *
     */
    class LimeDebug extends CWidget
    {
        public function run()
        {
            if (YII_DEBUG && in_array(getIPAddress(), array("127.0.0.1","::1")))
            {                
                $data = array(
                    'session' => $_SESSION,
                    'server' => $_SERVER
                );
                $json = json_encode($data, JSON_FORCE_OBJECT);
                $script = "LSdebug = $json;\n";
                $script .= "console.dir(LSdebug)\n";
                App()->getClientScript()->registerScript('LimeDebug', $script, CClientScript::POS_HEAD);
            }
        }
    }

?>
