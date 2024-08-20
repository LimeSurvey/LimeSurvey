<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Stream wrapper for reading data stored in an OLE file.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Structures
 * @package    OLE
 * @author     Christian Schmidt <schmidt@php.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/OLE
 * @since      File available since Release 0.6.0
 */

if (!class_exists('PEAR')) {
    require_once 'PEAR.php';
}

if (!class_exists('OLE')) {
    require_once 'OLE.php';
}


/**
 * Stream wrapper for reading data stored in an OLE file. Implements methods
 * for PHP's stream_wrapper_register(). For creating streams using this
 * wrapper, use OLE_PPS_File::getStream().
 *
 * @category   Structures
 * @package    OLE
 * @author     Christian Schmidt <schmidt@php.net>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/OLE
 * @since      Class available since Release 0.6.0
 */
class OLE_ChainedBlockStream extends PEAR
{
    /**
     * The OLE container of the file that is being read.
     * @var OLE
     */
    var $ole;

    /**
     * Parameters specified by fopen().
     * @var array
     */
    var $params;

    /**
     * The binary data of the file.
     * @var  string
     */
    var $data;

    /**
     * The file pointer.
     * @var  int  byte offset
     */
    var $pos;

    var $context;

    /**
     * Implements support for fopen().
     * For creating streams using this wrapper, use OLE_PPS_File::getStream().
     * @param  string  resource name including scheme, e.g.
     *                 ole-chainedblockstream://oleInstanceId=1
     * @param  string  only "r" is supported
     * @param  int     mask of STREAM_REPORT_ERRORS and STREAM_USE_PATH
     * @param  string  absolute path of the opened stream (out parameter)
     * @return bool    true on success
     */
    function stream_open($path, $mode, $options, &$openedPath)
    {
        if ($mode != 'r') {
            if ($options & STREAM_REPORT_ERRORS) {
                trigger_error('Only reading is supported', E_USER_WARNING);
            }
            return false;
        }

        // 25 is length of "ole-chainedblockstream://"
        parse_str(substr($path, 25), $this->params);
        if (!isset($this->params['oleInstanceId'],
                   $this->params['blockId'],
                   $GLOBALS['_OLE_INSTANCES'][$this->params['oleInstanceId']])) {

            if ($options & STREAM_REPORT_ERRORS) {
                trigger_error('OLE stream not found', E_USER_WARNING);
            }
            return false;
        }
        $this->ole = $GLOBALS['_OLE_INSTANCES'][$this->params['oleInstanceId']];

        $blockId = $this->params['blockId'];
        $this->data = '';
        if (isset($this->params['size']) &&
            $this->params['size'] < $this->ole->bigBlockThreshold &&
            $blockId != $this->ole->root->_StartBlock) {

            // Block id refers to small blocks
            $rootPos = 0;
            while ($blockId != OLE_ENDOFCHAIN) {
                $pos = $rootPos + $blockId * $this->ole->smallBlockSize;

                $blockId = $this->ole->sbat[$blockId];                
                fseek($this->ole->_small_handle, $pos);
                $this->data .= fread($this->ole->_small_handle, $this->ole->smallBlockSize);
            }
        } else {
            // Block id refers to big blocks
            while ($blockId != OLE_ENDOFCHAIN) {
                $pos = $this->ole->_getBlockOffset($blockId);
                fseek($this->ole->_file_handle, $pos);
                $this->data .= fread($this->ole->_file_handle, $this->ole->bigBlockSize);
                $blockId = $this->ole->bbat[$blockId];
            }
        }
        if (isset($this->params['size'])) {
            $this->data = substr($this->data, 0, $this->params['size']);
        }

        if ($options & STREAM_USE_PATH) {
            $openedPath = $path;
        }

        return true;
    }

    /**
     * Implements support for fclose().
     */
    function stream_close()
    {
        $this->ole = null;

        // $GLOBALS is not always defined in stream_close
        if (isset($GLOBALS['_OLE_INSTANCES'])) {
            unset($GLOBALS['_OLE_INSTANCES']);
        }
    }

    /**
     * Implements support for fread(), fgets() etc.
     * @param   int  maximum number of bytes to read
     * @return  string
     */
    function stream_read($count)
    {
        if ($this->stream_eof()) {
            return false;
        }

        $pos = isset($this->pos) ? $this->pos : 0;

        $s = substr($this->data, $pos, $count);
        $this->pos += $count;
        return $s;
    }

    /**
     * Implements support for feof().
     * @return  bool  TRUE if the file pointer is at EOF; otherwise FALSE
     */
    function stream_eof()
    {
        $eof = $this->pos >= strlen($this->data);
        // Workaround for bug in PHP 5.0.x: http://bugs.php.net/27508
        if (version_compare(PHP_VERSION, '5.0', '>=') &&
            version_compare(PHP_VERSION, '5.1', '<')) {

           $eof = !$eof;
        }
        return $eof;
    }

    /**
     * Returns the position of the file pointer, i.e. its offset into the file
     * stream. Implements support for ftell().
     * @return  int
     */
    function stream_tell()
    {
        return $this->pos;
    }

    /**
     * Implements support for fseek().
     * @param   int  byte offset
     * @param   int  SEEK_SET, SEEK_CUR or SEEK_END
     * @return  bool
     */
    function stream_seek($offset, $whence)
    {
        if ($whence == SEEK_SET && $offset >= 0) {
            $this->pos = $offset;
        } elseif ($whence == SEEK_CUR && -$offset <= $this->pos) {
            $this->pos += $offset;
        } elseif ($whence == SEEK_END && -$offset <= strlen($this->data)) {
            $this->pos = strlen($this->data) + $offset;
        } else {
            return false;
        }
        return true;
    }

    /**
     * Implements support for fstat(). Currently the only supported field is
     * "size".
     * @return  array
     */
    function stream_stat()
    {
        return array(
            'size' => strlen($this->data),
            );
    }

    /**
     * PHP 5.6 for some reason wants this to be implemented. Currently returning false as if it wasn't implemented.
     * @return boolean
     */
    function stream_flush()
    {
        // If not implemented, FALSE is assumed as the return value.
        return false;
    }

    // Methods used by stream_wrapper_register() that are not implemented:
    // int stream_write ( string data )
    // bool rename ( string path_from, string path_to )
    // bool mkdir ( string path, int mode, int options )
    // bool rmdir ( string path, int options )
    // bool dir_opendir ( string path, int options )
    // array url_stat ( string path, int flags )
    // string dir_readdir ( void )
    // bool dir_rewinddir ( void )
    // bool dir_closedir ( void )
}
