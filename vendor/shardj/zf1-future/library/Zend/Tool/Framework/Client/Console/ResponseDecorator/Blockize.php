<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once "Zend/Tool/Framework/Client/Response/ContentDecorator/Interface.php";

/**
 * Take a text and block it into several lines of a fixed length.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Tool_Framework_Client_Console_ResponseDecorator_Blockize
    implements Zend_Tool_Framework_Client_Response_ContentDecorator_Interface
{
    public function getName()
    {
        return 'blockize';
    }

    /**
     *
     * @param  string $content
     * @param  int $lineLength
     * @return string
     */
    public function decorate($content, $lineLength)
    {
        if((int)(string) $lineLength != $lineLength) {
            $lineLength = 72;
        }

        // break apart the message into wrapped chunks
        $lines = explode(PHP_EOL, wordwrap($content, $lineLength, PHP_EOL, false));
        $content = [];

        foreach($lines AS $line) {
            if(trim($line) === '') {
                continue;
            }

            if(strlen($line) < $lineLength) {
                $line .= str_repeat(" ", $lineLength-strlen($line));
            }

            $content[] = $line;
        }

        return implode(PHP_EOL, $content);
    }
}
