<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Xavier Noguer <xnoguer@php.net>                              |
// | Based on OLE::Storage_Lite by Kawai, Takanori                        |
// +----------------------------------------------------------------------+
//
// $Id$


/**
* Constants for OLE package
*/
define('OLE_PPS_TYPE_ROOT',        0x05);
define('OLE_PPS_TYPE_DIR',         0x01);
define('OLE_PPS_TYPE_FILE',        0x02);
define('OLE_DATA_SIZE_SMALL',    0x1000);
define('OLE_LONG_INT_SIZE',           4);
define('OLE_PPS_SIZE',             0x80);
define('OLE_DIFSECT',        0xFFFFFFFC);
define('OLE_FATSECT',        0xFFFFFFFD);
define('OLE_ENDOFCHAIN',     0xFFFFFFFE);
define('OLE_FREESECT',       0xFFFFFFFF);
define('OLE_LITTLE_ENDIAN',      0xFFFE);
define('OLE_VERSION_MAJOR_3',    0x0003);
define('OLE_VERSION_MINOR',      0x003E);
define('OLE_SECTOR_SHIFT_3',     0x0009);
define('OLE_MINI_SECTOR_SHIFT',  0x0006);
define('OLE_CFB_SIGNATURE', "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1");

if (!class_exists('PEAR')) {
    require_once 'PEAR.php';
}

/**
* Array for storing OLE instances that are accessed from
* OLE_ChainedBlockStream::stream_open().
* @var  array
*/
$GLOBALS['_OLE_INSTANCES'] = array();

/**
* OLE package base class.
*
* @category Structures
* @package  OLE
* @author   Xavier Noguer <xnoguer@php.net>
* @author   Christian Schmidt <schmidt@php.net>
*/
class OLE extends PEAR
{

    /**
    * The file handle for reading an OLE container
    * @var resource
    */
    var $_file_handle;

    /**
     * Reference to the sbat stream
     * @var resource
     */
    var $_small_handle;

    /**
    * Array of PPS's found on the OLE container
    * @var array
    */
    var $_list;

    /**
    * Root directory of OLE container
    * @var OLE_PPS_Root
    */
    var $root;

    /**
    * Big Block Allocation Table
    * @var array  (blockId => nextBlockId)
    */
    var $bbat;

    /**
    * Short Block Allocation Table
    * @var array  (blockId => nextBlockId)
    */
    var $sbat;

    /**
    * Size of big blocks. This is usually 512.
    * @var  int  number of octets per block.
    */
    var $bigBlockSize;

    /**
    * The big block threshhold.
    * @var int
    */
    var $bigBlockThreshold;

    /**
    * Size of small blocks. This is usually 64.
    * @var  int  number of octets per block
    */
    var $smallBlockSize;

    /**
    * Creates a new OLE object
    * @access public
    */
    function __construct()
    {
        $this->_list = array();
    }

    /**
    * Destructor (using PEAR)
    * Just closes the file handle on the OLE file.
    *
    * @access private
    */
    function _OLE()
    {
        fclose($this->_file_handle);
    }

    /**
    * Reads an OLE container from the contents of the file given.
    *
    * @access public
    * @param string $file
    * @return mixed true on success, PEAR_Error on failure
    */
    function read($file)
    {
        $fh = @fopen($file, "r");
        if (!$fh) {
            return $this->raiseError("Can't open file $file");
        }

        return $this->readStream($fh);
    }

    /**
     * Reads an OLE container from the contents of the stream given.
     *
     * @access public
     * @param resource $fh
     * @return mixed true on success, PEAR_Error on failure
     */
    function readStream($fh)
    {
        $this->_file_handle = $fh;

        $signature = fread($fh, 8);
        if (OLE_CFB_SIGNATURE != $signature) {
            return $this->raiseError("File doesn't seem to be an OLE container.");
        }
        fseek($fh, 28);
        if ($this->_readInt2($fh) != OLE_LITTLE_ENDIAN) {
            // This shouldn't be a problem in practice
            return $this->raiseError("Only Little-Endian encoding is supported.");
        }
        // Size of blocks and short blocks in bytes
        $this->bigBlockSize   = pow(2, $this->_readInt2($fh));
        $this->smallBlockSize = pow(2, $this->_readInt2($fh));

        // Skip UID, revision number and version number
        fseek($fh, 44);
        // Number of blocks in Big Block Allocation Table
        $bbatBlockCount = $this->_readInt4($fh);

        // Root chain 1st block
        $directoryFirstBlockId = $this->_readInt4($fh);

        // Skip unused bytes
        fseek($fh, 56);
        // Streams shorter than this are stored using small blocks
        $this->bigBlockThreshold = $this->_readInt4($fh);
        // Block id of first sector in Short Block Allocation Table
        $sbatFirstBlockId = $this->_readInt4($fh);
        // Number of blocks in Short Block Allocation Table
        $sbbatBlockCount = $this->_readInt4($fh);
        // Block id of first sector in Master Block Allocation Table
        $mbatFirstBlockId = $this->_readInt4($fh);
        // Number of blocks in Master Block Allocation Table
        $mbbatBlockCount = $this->_readInt4($fh);
        $this->bbat = array();

        // Remaining 4 * 109 bytes of current block is beginning of Master
        // Block Allocation Table
        $mbatBlocks = array();
        for ($i = 0; $i < 109; $i++) {
            $mbatBlocks[] = $this->_readInt4($fh);
        }

        // Read rest of Master Block Allocation Table (if any is left)
        $pos = $this->_getBlockOffset($mbatFirstBlockId);
        for ($i = 0; $i < $mbbatBlockCount; $i++) {
            fseek($fh, $pos);
            for ($j = 0; $j < $this->bigBlockSize / 4 - 1; $j++) {
                $mbatBlocks[] = $this->_readInt4($fh); // ffix - invalid block address check
            }
            // Last block id in each block points to next block
            $chainBlock = $this->_readInt4($fh);
            if ($chainBlock === OLE_ENDOFCHAIN) { // ENDOFCHAIN
                break;
            }
            $pos = $this->_getBlockOffset($chainBlock);
        }


        // Read Big Block Allocation Table according to chain specified by
        // $mbatBlocks
        for ($i = 0; $i < $bbatBlockCount; $i++) {
            $pos = $this->_getBlockOffset($mbatBlocks[$i]);
            fseek($fh, $pos);
            for ($j = 0 ; $j < $this->bigBlockSize / 4; $j++) {
                $this->bbat[] = $this->_readInt4($fh);
            }
        }

        // Read short block allocation table (SBAT)
        $this->sbat = array();
        $shortBlockCount = $sbbatBlockCount * $this->bigBlockSize / 4;
        $sbatFh = $this->getStream($sbatFirstBlockId);
        if (!$sbatFh) {
            // Avoid an infinite loop if ChainedBlockStream.php somehow is
            // missing
            return false;
        }

        for ($blockId = 0; $blockId < $shortBlockCount; $blockId++) {
            $this->sbat[$blockId] = $this->_readInt4($sbatFh);
        }
        fclose($sbatFh);

        $this->_readPpsWks($directoryFirstBlockId);

        return true;
    }

    /**
     * @param int $blockId block id
     * @return int byte offset from beginning of file
     * @access private
     */
    function _getBlockOffset($blockId)
    {
        return 512 + $blockId * $this->bigBlockSize;
    }

    /**
     * Returns a stream for use with fread() etc. External callers should
     * use OLE_PPS_File::getStream().
     * @param int|PPS $blockIdOrPps block id or PPS
     * @return resource read-only stream
     */
    function getStream($blockIdOrPps)
    {
        include_once 'OLE/ChainedBlockStream.php';
        static $isRegistered = false;
        if (!$isRegistered) {
            stream_wrapper_register('ole-chainedblockstream',
                                    'OLE_ChainedBlockStream');
            $isRegistered = true;
        }

        // Store current instance in global array, so that it can be accessed
        // in OLE_ChainedBlockStream::stream_open().
        // Object is removed from self::$instances in OLE_Stream::close().
        $GLOBALS['_OLE_INSTANCES'][] = $this;
        $keys = array_keys($GLOBALS['_OLE_INSTANCES']);
        $instanceId = end($keys);

        $path = 'ole-chainedblockstream://oleInstanceId=' . $instanceId;
        if (is_a($blockIdOrPps, 'OLE_PPS')) {
            $path .= '&blockId=' . $blockIdOrPps->_StartBlock;
            $path .= '&size=' . $blockIdOrPps->Size;
        } else {
            $path .= '&blockId=' . $blockIdOrPps;
        }
        return fopen($path, 'r');
    }

    /**
     * Reads a signed char.
     * @param resource $fh file handle
     * @return int
     * @access private
     */
    function _readInt1($fh)
    {
        list(, $tmp) = unpack("c", fread($fh, 1));
        return $tmp;
    }

    /**
     * Reads an unsigned short (2 octets).
     * @param resource $fh file handle
     * @return int
     * @access private
     */
    function _readInt2($fh)
    {
        list(, $tmp) = unpack("v", fread($fh, 2));
        return $tmp;
    }

    /**
     * Reads an unsigned long (4 octets).
     * @param   resource  file handle
     * @return  int
     * @access private
     */
    function _readInt4($fh)
    {
        list(, $tmp) = unpack("V", fread($fh, 4));
        return $tmp;
    }

    /**
    * Gets information about all PPS's on the OLE container from the PPS WK's
    * creates an OLE_PPS object for each one.
    *
    * @access private
    * @param integer $blockId the block id of the first block
    * @return mixed true on success, PEAR_Error on failure
    */
    function _readPpsWks($blockId)
    {
        $fh = $this->getStream($blockId);
        for ($pos = 0; ; $pos += 128) {
            fseek($fh, $pos, SEEK_SET);
            $nameUtf16 = fread($fh, 64);
            $nameLength = $this->_readInt2($fh);
            $nameUtf16 = substr($nameUtf16, 0, $nameLength - 2);
            // Simple conversion from UTF-16LE to ISO-8859-1
            $name = str_replace("\x00", "", $nameUtf16);
            $type = $this->_readInt1($fh);
            switch ($type) {
            case OLE_PPS_TYPE_ROOT:
                require_once 'OLE/PPS/Root.php';
                $pps = new OLE_PPS_Root(null, null, array());
                $this->root = $pps;
                break;
            case OLE_PPS_TYPE_DIR:
                $pps = new OLE_PPS(null, null, null, null, null,
                                   null, null, null, null, array());
                break;
            case OLE_PPS_TYPE_FILE:
                require_once 'OLE/PPS/File.php';
                $pps = new OLE_PPS_File($name);
                break;
            default:
                continue 2;
            }
            fseek($fh, 1, SEEK_CUR); // skip Color Flag
            $pps->Type    = $type;
            $pps->Name    = $name;
            $pps->PrevPps = $this->_readInt4($fh); // Left Sibling ID
            $pps->NextPps = $this->_readInt4($fh); // Right Sibling ID
            $pps->DirPps  = $this->_readInt4($fh); // Child ID
            fseek($fh, 20, SEEK_CUR); // skip CLSID (16 bytes) + State Bits
            $pps->Time1st = OLE::OLE2LocalDate(fread($fh, 8));
            $pps->Time2nd = OLE::OLE2LocalDate(fread($fh, 8));
            $pps->_StartBlock = $this->_readInt4($fh);
            $pps->Size = $this->_readInt4($fh);
            $pps->No = count($this->_list);
            $this->_list[] = $pps;

            if ($type == OLE_PPS_TYPE_ROOT) {
                $this->_small_handle = $this->getStream($pps->_StartBlock);
            }

            // check if the PPS tree (starting from root) is complete
            if (isset($this->root) &&
                $this->_ppsTreeComplete($this->root->No)) {

                break;
            }
        }
        fclose($fh);

        // Initialize $pps->children on directories
        foreach ($this->_list as $pps) {
            if ($pps->Type == OLE_PPS_TYPE_DIR || $pps->Type == OLE_PPS_TYPE_ROOT) {
                $nos = array($pps->DirPps);
                $pps->children = array();
                while ($nos) {
                    $no = array_pop($nos);
                    if ($no != OLE_FREESECT) {
                        $childPps = $this->_list[$no];
                        $nos[] = $childPps->PrevPps;
                        $nos[] = $childPps->NextPps;
                        $pps->children[] = $childPps;
                    }
                }
            }
        }

        return true;
    }

    /**
    * It checks whether the PPS tree is complete (all PPS's read)
    * starting with the given PPS (not necessarily root)
    *
    * @access private
    * @param integer $index The index of the PPS from which we are checking
    * @return boolean Whether the PPS tree for the given PPS is complete
    */
    function _ppsTreeComplete($index)
    {
        return isset($this->_list[$index]) &&
               ($pps = $this->_list[$index]) &&
               ($pps->PrevPps == OLE_FREESECT ||
                $this->_ppsTreeComplete($pps->PrevPps)) &&
               ($pps->NextPps == OLE_FREESECT ||
                $this->_ppsTreeComplete($pps->NextPps)) &&
               ($pps->DirPps == OLE_FREESECT ||
                $this->_ppsTreeComplete($pps->DirPps));
    }

    /** 
    * Checks whether a PPS is a File PPS or not.
    * If there is no PPS for the index given, it will return false.
    * @param integer $index The index for the PPS
    * @return bool true if it's a File PPS, false otherwise
    * @access public
    */
    function isFile($index)
    {
        if (isset($this->_list[$index])) {
            return ($this->_list[$index]->Type == OLE_PPS_TYPE_FILE);
        }
        return false;
    }

    /** 
    * Checks whether a PPS is a Root PPS or not.
    * If there is no PPS for the index given, it will return false.
    * @param integer $index The index for the PPS.
    * @return bool true if it's a Root PPS, false otherwise
    * @access public
    */
    function isRoot($index)
    {
        if (isset($this->_list[$index])) {
            return ($this->_list[$index]->Type == OLE_PPS_TYPE_ROOT);
        }
        return false;
    }

    /** 
    * Gives the total number of PPS's found in the OLE container.
    * @return integer The total number of PPS's found in the OLE container
    * @access public
    */
    function ppsTotal()
    {
        return count($this->_list);
    }

    /**
    * Gets data from a PPS
    * If there is no PPS for the index given, it will return an empty string.
    * @param integer $index    The index for the PPS
    * @param integer $position The position from which to start reading
    *                          (relative to the PPS)
    * @param integer $length   The amount of bytes to read (at most)
    * @return string The binary string containing the data requested
    * @access public
    * @see OLE_PPS_File::getStream()
    */
    function getData($index, $position, $length)
    {
        // if position is not valid return empty string
        if (!isset($this->_list[$index]) ||
            $position >= $this->_list[$index]->Size ||
            $position < 0) {

            return '';
        }
        $fh = $this->getStream($this->_list[$index]);
        $data = stream_get_contents($fh, $length, $position);
        fclose($fh);
        return $data;
    }

    /**
    * Gets the data length from a PPS
    * If there is no PPS for the index given, it will return 0.
    * @param integer $index The index for the PPS
    * @return integer The amount of bytes in data the PPS has
    * @access public
    */
    function getDataLength($index)
    {
        if (isset($this->_list[$index])) {
            return $this->_list[$index]->Size;
        }
        return 0;
    }

    /**
    * Utility function to transform ASCII text to Unicode
    *
    * @access public
    * @static
    * @param string $ascii The ASCII string to transform
    * @return string The string in Unicode
    */
    static function Asc2Ucs($ascii)
    {
        $rawname = '';
        for ($i = 0; $i < strlen($ascii); $i++) {
            $rawname .= $ascii[$i] . "\x00";
        }
        return $rawname;
    }

    /**
    * Utility function
    * Returns a string for the OLE container with the date given
    *
    * @access public
    * @static
    * @param integer $date A timestamp 
    * @return string The string for the OLE container
    */
    static function LocalDate2OLE($date = null)
    {
        if (!isset($date)) {
            return "\x00\x00\x00\x00\x00\x00\x00\x00";
        }

        // factor used for separating numbers into 4 bytes parts
        $factor = pow(2, 32);

        // days from 1-1-1601 until the beggining of UNIX era
        $days = 134774;
        // calculate seconds
        $big_date = $days * 24 * 3600 +
            gmmktime(date("H",$date),date("i",$date),date("s",$date),
                     date("m",$date),date("d",$date),date("Y",$date));
        // multiply just to make MS happy
        $big_date *= 10000000;

        $high_part = floor($big_date / $factor);
        // lower 4 bytes
        $low_part = floor((($big_date / $factor) - $high_part) * $factor);

        // Make HEX string
        $res = '';

        for ($i = 0; $i < 4; $i++) {
            $hex = (int) $low_part % 0x100;
            $res .= pack('c', $hex);
            $low_part /= 0x100;
        }
        for ($i = 0; $i < 4; $i++) {
            $hex = (int) $high_part % 0x100;
            $res .= pack('c', $hex);
            $high_part /= 0x100;
        }
        return $res;
    }

    /**
    * Returns a timestamp from an OLE container's date
    * @param integer $string A binary string with the encoded date
    * @return string The timestamp corresponding to the string
    * @access public
    * @static
    */
    static function OLE2LocalDate($string)
    {
        if (strlen($string) != 8) {
            return new PEAR_Error("Expecting 8 byte string");
        }

        // factor used for separating numbers into 4 bytes parts
        $factor = pow(2,32);
        $high_part = 0;
        for ($i = 0; $i < 4; $i++) {
            list(, $high_part) = unpack('C', $string[(7 - $i)]);
            if ($i < 3) {
                $high_part *= 0x100;
            }
        }
        $low_part = 0;
        for ($i = 4; $i < 8; $i++) {
            list(, $low_part) = unpack('C', $string[(7 - $i)]);
            if ($i < 7) {
                $low_part *= 0x100;
            }
        }
        $big_date = ($high_part * $factor) + $low_part;
        // translate to seconds
        $big_date /= 10000000;
        
        // days from 1-1-1601 until the beggining of UNIX era
        $days = 134774;
        
        // translate to seconds from beggining of UNIX era
        $big_date -= $days * 24 * 3600;
        return floor($big_date);
    }
}
