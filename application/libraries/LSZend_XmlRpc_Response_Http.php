<?php 
    Yii::import('application.libraries.BigData', true);
    
    class LSZend_XmlRpc_Response_Http extends Zend_XmlRpc_Response_Http
    {
        
        
        // Output content in $this->_return.
        public function printXml()
        {
            echo '<methodResponse>';
            echo '<params>';
            echo '<param>';
            echo '<value>';
            BigData::xmlrpc_echo($this->_return);
            echo '</value>';
            echo '</param>';
            echo '</params>';
            echo '</methodResponse>';
        }
    }
