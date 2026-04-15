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
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Storage
{
    // maildir and IMAP flags, using IMAP names, where possible to be able to distinguish between IMAP
    // system flags and other flags
    public const FLAG_PASSED   = 'Passed';
    public const FLAG_SEEN     = '\Seen';
    public const FLAG_UNSEEN   = '\Unseen';
    public const FLAG_ANSWERED = '\Answered';
    public const FLAG_FLAGGED  = '\Flagged';
    public const FLAG_DELETED  = '\Deleted';
    public const FLAG_DRAFT    = '\Draft';
    public const FLAG_RECENT   = '\Recent';
}
