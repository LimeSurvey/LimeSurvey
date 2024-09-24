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

if (!class_exists('OLE_PPS')) {
    require_once 'OLE/PPS.php';
}

if (!class_exists('System')) {
    require_once 'System.php';
}

/**
* Class for creating Root PPS's for OLE containers
*
* @author   Xavier Noguer <xnoguer@php.net>
* @category Structures
* @package  OLE
*/
class OLE_PPS_Root extends OLE_PPS
{
    /**
    * Flag to enable new logic
    * @var bool
    */
    var $new_func = true;

    /**
    * The temporary dir for storing the OLE file
    * @var string
    */
    var $_tmp_dir;

    var $_BIG_BLOCK_SIZE;

    var $_SMALL_BLOCK_SIZE;

    var $_tmp_filename;

    var $_FILEH_;

    /**
    * Constructor
    *
    * @access public
    * @param integer $time_1st A timestamp
    * @param integer $time_2nd A timestamp
    */
    function __construct($time_1st, $time_2nd, $raChild)
    {
        $system = new System();
        $this->_tmp_dir = $system->tmpdir();
        parent::__construct(
           null, 
           OLE::Asc2Ucs('Root Entry'),
           OLE_PPS_TYPE_ROOT,
           null,
           null,
           null,
           $time_1st,
           $time_2nd,
           null,
           $raChild);
    }

    /**
    * Sets the temp dir used for storing the OLE file
    *
    * @access public
    * @param string $dir The dir to be used as temp dir
    * @return true if given dir is valid, false otherwise
    */
    function setTempDir($dir)
    {
        if (is_dir($dir)) {
            $this->_tmp_dir = $dir;
            return true;
        }
        return false;
    }

    /**
    * Method for saving the whole OLE container (including files).
    * In fact, if called with an empty argument (or '-'), it saves to a
    * temporary file and then outputs it's contents to stdout.
    *
    * @param string $filename The name of the file where to save the OLE container
    * @access public
    * @return mixed true on success, PEAR_Error on failure
    */
    function save($filename)
    {
        // Initial Setting for saving
        $this->_BIG_BLOCK_SIZE  = pow(2,
                      ((isset($this->_BIG_BLOCK_SIZE))? $this->_adjust2($this->_BIG_BLOCK_SIZE)  : 9));
        $this->_SMALL_BLOCK_SIZE= pow(2, 
                      ((isset($this->_SMALL_BLOCK_SIZE))?  $this->_adjust2($this->_SMALL_BLOCK_SIZE): 6));
 
        // Open temp file if we are sending output to stdout
        if (($filename == '-') || ($filename == '')) {
            $this->_tmp_filename = tempnam($this->_tmp_dir, "OLE_PPS_Root");
            $this->_FILEH_ = @fopen($this->_tmp_filename,"w+b");
            if ($this->_FILEH_ == false) {
                return $this->raiseError("Can't create temporary file.");
            }
        } else {
            $this->_FILEH_ = @fopen($filename, "wb");
            if ($this->_FILEH_ == false) {
                return $this->raiseError("Can't open $filename. It may be in use or protected.");
            }
        }
        // Make an array of PPS's (for Save)
        $aList = array();
        OLE_PPS_Root::_savePpsSetPnt($aList, array($this));
        // calculate values for header
        list($iSBDcnt, $iBBcnt, $iPPScnt) = $this->_calcSize($aList); //, $rhInfo);
        // Save Header
        $this->_saveHeader($iSBDcnt, $iBBcnt, $iPPScnt);
  
        // Make Small Data string (write SBD)
        $this->_data = $this->_makeSmallData($aList);
  
        // Write BB
        $this->_saveBigData($iSBDcnt, $aList);
        // Write PPS
        $this->_savePps($aList);
        // Write Big Block Depot and BDList and Adding Header informations
        $this->_saveBbd($iSBDcnt, $iBBcnt, $iPPScnt);
        // Close File, send it to stdout if necessary
        if (($filename == '-') || ($filename == '')) {
            fseek($this->_FILEH_, 0);
            fpassthru($this->_FILEH_);
            @fclose($this->_FILEH_);
            // Delete the temporary file.
            @unlink($this->_tmp_filename);
        } else {
            @fclose($this->_FILEH_);
        }

        return true;
    }

    /**
    * Calculate some numbers
    *
    * @access private
    * @param array $raList Reference to an array of PPS's
    * @return array The array of numbers
    */
    function _calcSize(&$raList) 
    {
        // Calculate Basic Setting
        $iBBcnt = 0;
        $iSBcnt = 0;
        for ($i = 0; $i < count($raList); $i++) {
            if ($raList[$i]->Type == OLE_PPS_TYPE_FILE) {
                $raList[$i]->Size = $raList[$i]->_DataLen();
                if ($raList[$i]->Size < OLE_DATA_SIZE_SMALL) {
                    $iSBcnt += floor($raList[$i]->Size / $this->_SMALL_BLOCK_SIZE)
                                  + (($raList[$i]->Size % $this->_SMALL_BLOCK_SIZE)? 1: 0);
                } else {
                    $iBBcnt += (floor($raList[$i]->Size / $this->_BIG_BLOCK_SIZE) +
                        (($raList[$i]->Size % $this->_BIG_BLOCK_SIZE)? 1: 0));
                }
            }
        }
        $iSmallLen = $iSBcnt * $this->_SMALL_BLOCK_SIZE;
        $iSlCnt = floor($this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE);
        $iSBDcnt = floor($iSBcnt / $iSlCnt) + (($iSBcnt % $iSlCnt)? 1:0);
        $iBBcnt +=  (floor($iSmallLen / $this->_BIG_BLOCK_SIZE) +
                      (( $iSmallLen % $this->_BIG_BLOCK_SIZE)? 1: 0));
        $iCnt = count($raList);
        $iBdCnt = $this->_BIG_BLOCK_SIZE / OLE_PPS_SIZE;
        $iPPScnt = (floor($iCnt/$iBdCnt) + (($iCnt % $iBdCnt)? 1: 0));
   
        return array($iSBDcnt, $iBBcnt, $iPPScnt);
    }

    /**
    * Helper function for caculating a magic value for block sizes
    *
    * @access private
    * @param integer $i2 The argument
    * @see save()
    * @return integer
    */
    function _adjust2($i2)
    {
        $iWk = log($i2)/log(2);
        return ($iWk > floor($iWk))? floor($iWk)+1:$iWk;
    }

    /**
    * Save OLE header
    *
    * @access private
    * @param integer $iSBDcnt
    * @param integer $iBBcnt
    * @param integer $iPPScnt
    */
    function _saveHeader($iSBDcnt, $iBBcnt, $iPPScnt)
    {
        $FILE = $this->_FILEH_;
  
        if($this->new_func)
          return $this->_create_header($iSBDcnt, $iBBcnt, $iPPScnt);

        // Calculate Basic Setting
        $iBlCnt = $this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE;
        $i1stBdL = ($this->_BIG_BLOCK_SIZE - 0x4C) / OLE_LONG_INT_SIZE;
  
        $iBdExL = 0;
        $iAll = $iBBcnt + $iPPScnt + $iSBDcnt;
        $iAllW = $iAll;
        $iBdCntW = floor($iAllW / $iBlCnt) + (($iAllW % $iBlCnt)? 1: 0);
        $iBdCnt = floor(($iAll + $iBdCntW) / $iBlCnt) + ((($iAllW+$iBdCntW) % $iBlCnt)? 1: 0);
  
        // Calculate BD count
        if ($iBdCnt > $i1stBdL) {
            while (1) {
                $iBdExL++;
                $iAllW++;
                $iBdCntW = floor($iAllW / $iBlCnt) + (($iAllW % $iBlCnt)? 1: 0);
                $iBdCnt = floor(($iAllW + $iBdCntW) / $iBlCnt) + ((($iAllW+$iBdCntW) % $iBlCnt)? 1: 0);
                if ($iBdCnt <= ($iBdExL*$iBlCnt+ $i1stBdL)) {
                    break;
                }
            }
        }
  
        // Save Header
        fwrite($FILE,
            OLE_CFB_SIGNATURE // Header Signature (8 bytes)
            . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" // Header CLSID (16 bytes)
            . pack("v", OLE_VERSION_MINOR) // Minor Version (2 bytes)
            . pack("v", OLE_VERSION_MAJOR_3) // Major Version (2 bytes)
            . pack("v", OLE_LITTLE_ENDIAN) // Byte Order (2 bytes)
            . pack("v", OLE_SECTOR_SHIFT_3) // Sector Shift (2 bytes)
            . pack("v", OLE_MINI_SECTOR_SHIFT) // Mini Sector Shift (2 bytes)
            . "\x00\x00\x00\x00\x00\x00" // Reserved (6 bytes)
            . "\x00\x00\x00\x00" // Number of Directory Sectors (4 bytes)
            . pack("V", $iBdCnt) // Number of FAT Sectors (4 bytes)
            . pack("V", $iBBcnt+$iSBDcnt) //ROOT START, First Directory Sector Location (4 bytes)
            . pack("V", 0) // Transaction Signature Number (4 bytes)
            . pack("V", 0x00001000) // Mini Stream Cutoff Size (4 bytes)
            . pack("V", $iSBDcnt ? 0 : OLE_ENDOFCHAIN) // First Mini FAT Sector Location (4 bytes)
            . pack("V", $iSBDcnt) // Number of Mini FAT Sectors (4 bytes)
          );
        // Extra BDList Start, Count
        if ($iBdCnt < $i1stBdL) {
            fwrite($FILE,
                      pack("V", OLE_ENDOFCHAIN).      // Extra BDList Start
                      pack("V", 0)        // Extra BDList Count
                  );
        } else {
            fwrite($FILE, pack("V", $iAll+$iBdCnt) . pack("V", $iBdExL));
        }

        // BDList
        for ($i = 0; $i < $i1stBdL && $i < $iBdCnt; $i++) {
            fwrite($FILE, pack("V", $iAll+$i));
        }
        if ($i < $i1stBdL) { // free sectors
            for ($j = 0; $j < ($i1stBdL-$i); $j++) {
                fwrite($FILE, (pack("V", OLE_FREESECT)));
            }
        }
    }

    /**
    * Saving big data (PPS's with data bigger than OLE_DATA_SIZE_SMALL)
    *
    * @access private
    * @param integer $iStBlk
    * @param array &$raList Reference to array of PPS's
    */
    function _saveBigData($iStBlk, &$raList)
    {
        $FILE = $this->_FILEH_;
   
        // cycle through PPS's
        for ($i = 0; $i < count($raList); $i++) {
            if ($raList[$i]->Type != OLE_PPS_TYPE_DIR) {
                $raList[$i]->Size = $raList[$i]->_DataLen();
                if (($raList[$i]->Size >= OLE_DATA_SIZE_SMALL) ||
                    (($raList[$i]->Type == OLE_PPS_TYPE_ROOT) && isset($raList[$i]->_data)))
                {
                    // Write Data
                    if (isset($raList[$i]->_PPS_FILE)) {
                        $iLen = 0;
                        fseek($raList[$i]->_PPS_FILE, 0); // To The Top
                        while($sBuff = fread($raList[$i]->_PPS_FILE, 4096)) {
                            $iLen += strlen($sBuff);
                            fwrite($FILE, $sBuff);
                        }
                    } else {
                        fwrite($FILE, $raList[$i]->_data);
                    }
           
                    if ($raList[$i]->Size % $this->_BIG_BLOCK_SIZE) {
                        for ($j = 0; $j < ($this->_BIG_BLOCK_SIZE - ($raList[$i]->Size % $this->_BIG_BLOCK_SIZE)); $j++) {
                            fwrite($FILE, "\x00");
                        }
                    }
                    // Set For PPS
                    $raList[$i]->_StartBlock = $iStBlk;
                    $iStBlk += 
                            (floor($raList[$i]->Size / $this->_BIG_BLOCK_SIZE) +
                                (($raList[$i]->Size % $this->_BIG_BLOCK_SIZE)? 1: 0));
                }
                // Close file for each PPS, and unlink it
                if (isset($raList[$i]->_PPS_FILE)) {
                    @fclose($raList[$i]->_PPS_FILE);
                    $raList[$i]->_PPS_FILE = null;
                    @unlink($raList[$i]->_tmp_filename);
                }
            }
        }
    }

    /**
    * get small data (PPS's with data smaller than OLE_DATA_SIZE_SMALL)
    *
    * @access private
    * @param array &$raList Reference to array of PPS's
    */
    function _makeSmallData(&$raList)
    {
        $sRes = '';
        $FILE = $this->_FILEH_;
        $iSmBlk = 0;
   
        for ($i = 0; $i < count($raList); $i++) {
            // Make SBD, small data string
            if ($raList[$i]->Type == OLE_PPS_TYPE_FILE) {
                if ($raList[$i]->Size <= 0) {
                    continue;
                }
                if ($raList[$i]->Size < OLE_DATA_SIZE_SMALL) {
                    $iSmbCnt = floor($raList[$i]->Size / $this->_SMALL_BLOCK_SIZE)
                                  + (($raList[$i]->Size % $this->_SMALL_BLOCK_SIZE)? 1: 0);
                    // Add to SBD
                    for ($j = 0; $j < ($iSmbCnt-1); $j++) {
                        fwrite($FILE, pack("V", $j+$iSmBlk+1));
                    }
                    fwrite($FILE, pack("V", OLE_ENDOFCHAIN));
                   
                    // Add to Data String(this will be written for RootEntry)
                    if ($raList[$i]->_PPS_FILE) {
                        fseek($raList[$i]->_PPS_FILE, 0); // To The Top
                        while ($sBuff = fread($raList[$i]->_PPS_FILE, 4096)) {
                            $sRes .= $sBuff;
                        }
                    } else {
                        $sRes .= $raList[$i]->_data;
                    }
                    if ($raList[$i]->Size % $this->_SMALL_BLOCK_SIZE) {
                        for ($j = 0; $j < ($this->_SMALL_BLOCK_SIZE - ($raList[$i]->Size % $this->_SMALL_BLOCK_SIZE)); $j++) {
                            $sRes .= "\x00";
                        }
                    }
                    // Set for PPS
                    $raList[$i]->_StartBlock = $iSmBlk;
                    $iSmBlk += $iSmbCnt;
                }
            }
        }
        $iSbCnt = floor($this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE);
        if ($iSmBlk % $iSbCnt) {
            for ($i = 0; $i < ($iSbCnt - ($iSmBlk % $iSbCnt)); $i++) {
                fwrite($FILE, pack("V", OLE_FREESECT));
            }
        }
        return $sRes;
    }

    /**
    * Saves all the PPS's WKs
    *
    * @access private
    * @param array $raList Reference to an array with all PPS's
    */
    function _savePps(&$raList) 
    {
        // Save each PPS WK
        for ($i = 0; $i < count($raList); $i++) {
            fwrite($this->_FILEH_, $raList[$i]->_getPpsWk());
        }
        // Adjust for Block
        $iCnt = count($raList);
        $iBCnt = $this->_BIG_BLOCK_SIZE / OLE_PPS_SIZE;
        if ($iCnt % $iBCnt) {
            for ($i = 0; $i < (($iBCnt - ($iCnt % $iBCnt)) * OLE_PPS_SIZE); $i++) {
                fwrite($this->_FILEH_, "\x00");
            }
        }
    }

    /**
    * Saving Big Block Depot
    *
    * @access private
    * @param integer $iSbdSize
    * @param integer $iBsize
    * @param integer $iPpsCnt
    */
    function _saveBbd($iSbdSize, $iBsize, $iPpsCnt) 
    {
      if($this->new_func)
        return $this->_create_big_block_chain($iSbdSize, $iBsize, $iPpsCnt);

        $FILE = $this->_FILEH_;
        // Calculate Basic Setting
        $iBbCnt = $this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE;
        $i1stBdL = ($this->_BIG_BLOCK_SIZE - 0x4C) / OLE_LONG_INT_SIZE;
      
        $iBdExL = 0;
        $iAll = $iBsize + $iPpsCnt + $iSbdSize;
        $iAllW = $iAll;
        $iBdCntW = floor($iAllW / $iBbCnt) + (($iAllW % $iBbCnt)? 1: 0);
        $iBdCnt = floor(($iAll + $iBdCntW) / $iBbCnt) + ((($iAllW+$iBdCntW) % $iBbCnt)? 1: 0);
        // Calculate BD count
        if ($iBdCnt >$i1stBdL) {
            while (1) {
                $iBdExL++;
                $iAllW++;
                $iBdCntW = floor($iAllW / $iBbCnt) + (($iAllW % $iBbCnt)? 1: 0);
                $iBdCnt = floor(($iAllW + $iBdCntW) / $iBbCnt) + ((($iAllW+$iBdCntW) % $iBbCnt)? 1: 0);
                if ($iBdCnt <= ($iBdExL*$iBbCnt+ $i1stBdL)) {
                    break;
                }
            }
        }
      
        // Making BD
        // Set for SBD
        if ($iSbdSize > 0) {
            for ($i = 0; $i < ($iSbdSize - 1); $i++) {
                fwrite($FILE, pack("V", $i+1));
            }
            fwrite($FILE, pack("V", OLE_ENDOFCHAIN));
        }
        // Set for B
        for ($i = 0; $i < ($iBsize - 1); $i++) {
            fwrite($FILE, pack("V", $i+$iSbdSize+1));
        }
        fwrite($FILE, pack("V", OLE_ENDOFCHAIN));
      
        // Set for PPS
        for ($i = 0; $i < ($iPpsCnt - 1); $i++) {
            fwrite($FILE, pack("V", $i+$iSbdSize+$iBsize+1));
        }
        fwrite($FILE, pack("V", OLE_ENDOFCHAIN));
        // Set for BBD itself ( 0xFFFFFFFD : BBD)
        for ($i = 0; $i < $iBdCnt; $i++) {
            fwrite($FILE, pack("V", OLE_FATSECT));
        }
        // Set for ExtraBDList
        for ($i = 0; $i < $iBdExL; $i++) {
            fwrite($FILE, pack("V", OLE_DIFSECT));
        }
        // Adjust for Block
        if (($iAllW + $iBdCnt) % $iBbCnt) {
            for ($i = 0; $i < ($iBbCnt - (($iAllW + $iBdCnt) % $iBbCnt)); $i++) {
                fwrite($FILE, pack("V", OLE_FREESECT));
            }
        }
        // Extra BDList
        if ($iBdCnt > $i1stBdL) {
            $iN=0;
            $iNb=0;
            for ($i = $i1stBdL;$i < $iBdCnt; $i++, $iN++) {
                if ($iN >= ($iBbCnt - 1)) {
                    $iN = 0;
                    $iNb++;
                    fwrite($FILE, pack("V", $iAll+$iBdCnt+$iNb));
                }
                fwrite($FILE, pack("V", $iBsize+$iSbdSize+$iPpsCnt+$i));
            }
            if (($iBdCnt-$i1stBdL) % ($iBbCnt-1)) {
                for ($i = 0; $i < (($iBbCnt - 1) - (($iBdCnt - $i1stBdL) % ($iBbCnt - 1))); $i++) {
                    fwrite($FILE, pack("V", OLE_FREESECT));
                }
            }
            fwrite($FILE, pack("V", OLE_ENDOFCHAIN));
        }
    }



    /**
     * New method to store Bigblock chain
     *
     * @access private
     * @param integer $num_sb_blocks - number of Smallblock depot blocks
     * @param integer $num_bb_blocks - number of Bigblock depot blocks
     * @param integer $num_pps_blocks - number of PropertySetStorage blocks
     */
    function _create_big_block_chain($num_sb_blocks, $num_bb_blocks, $num_pps_blocks) 
    {
      $FILE = $this->_FILEH_;

      $bbd_info = $this->_calculate_big_block_chain($num_sb_blocks, $num_bb_blocks, $num_pps_blocks);
          
      $data = "";

      if($num_sb_blocks > 0)
        {
          for($i = 0; $i<($num_sb_blocks-1); $i++)
            $data .= pack("V", $i+1);
          $data .= pack("V", OLE_ENDOFCHAIN);
        }

      for($i = 0; $i<($num_bb_blocks-1); $i++)
        $data .= pack("V", $i + $num_sb_blocks + 1);
      $data .= pack("V", OLE_ENDOFCHAIN);

      for($i = 0; $i<($num_pps_blocks-1); $i++)
        $data .= pack("V", $i + $num_sb_blocks + $num_bb_blocks + 1);
      $data .= pack("V", OLE_ENDOFCHAIN);

      for($i = 0; $i < $bbd_info["0xFFFFFFFD_blockchain_entries"]; $i++)
        $data .= pack("V", OLE_FATSECT);

      for($i = 0; $i < $bbd_info["0xFFFFFFFC_blockchain_entries"]; $i++)
        $data .= pack("V", OLE_DIFSECT);

      // Adjust for Block
      $all_entries = $num_sb_blocks + $num_bb_blocks + $num_pps_blocks + $bbd_info["0xFFFFFFFD_blockchain_entries"] + $bbd_info["0xFFFFFFFC_blockchain_entries"];
      if($all_entries % $bbd_info["entries_per_block"])
        {
          $rest = $bbd_info["entries_per_block"] - ($all_entries % $bbd_info["entries_per_block"]);
          for($i = 0; $i < $rest; $i++)
            $data .= pack("V", OLE_FREESECT);
        }

      // Extra BDList
      if($bbd_info["blockchain_list_entries"] > $bbd_info["header_blockchain_list_entries"])
        {
          $iN=0;
          $iNb=0;
          for($i = $bbd_info["header_blockchain_list_entries"]; $i < $bbd_info["blockchain_list_entries"]; $i++, $iN++)
            {
              if($iN >= ($bbd_info["entries_per_block"]-1))
                {
                  $iN = 0;
                  $iNb++;
                  $data .= pack("V", $num_sb_blocks + $num_bb_blocks + $num_pps_blocks + $bbd_info["0xFFFFFFFD_blockchain_entries"] + $iNb);
                }

              $data .= pack("V", $num_bb_blocks + $num_sb_blocks + $num_pps_blocks + $i);
            }

          $all_entries = $bbd_info["blockchain_list_entries"] - $bbd_info["header_blockchain_list_entries"];
          if(($all_entries % ($bbd_info["entries_per_block"] - 1)))
            {
              $rest = ($bbd_info["entries_per_block"] - 1) - ($all_entries % ($bbd_info["entries_per_block"] - 1));
              for($i = 0; $i < $rest; $i++)
                $data .= pack("V", OLE_FREESECT);
            }

          $data .= pack("V", OLE_ENDOFCHAIN);
        }

      /*
        $this->dump($data, 0, strlen($data));
        die;
      */

      fwrite($FILE, $data);
    }

    /**
     * New method to store Header
     *
     * @access private
     * @param integer $num_sb_blocks - number of Smallblock depot blocks
     * @param integer $num_bb_blocks - number of Bigblock depot blocks
     * @param integer $num_pps_blocks - number of PropertySetStorage blocks
     */
    function _create_header($num_sb_blocks, $num_bb_blocks, $num_pps_blocks) 
    {
      $FILE = $this->_FILEH_;

      $bbd_info = $this->_calculate_big_block_chain($num_sb_blocks, $num_bb_blocks, $num_pps_blocks);
  
      // Save Header
      fwrite($FILE,
          OLE_CFB_SIGNATURE // Header Signature (8 bytes)
          . "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00" // Header CLSID (16 bytes)
          . pack("v", OLE_VERSION_MINOR) // Minor Version (2 bytes)
          . pack("v", OLE_VERSION_MAJOR_3) // Major Version (2 bytes)
          . pack("v", OLE_LITTLE_ENDIAN) // Byte Order (2 bytes)
          . pack("v", OLE_SECTOR_SHIFT_3) // Sector Shift (2 bytes)
          . pack("v", OLE_MINI_SECTOR_SHIFT) // Mini Sector Shift (2 bytes)
          . "\x00\x00\x00\x00\x00\x00" // Reserved (6 bytes)
          . "\x00\x00\x00\x00" // Number of Directory Sectors (4 bytes)
          . pack("V", $bbd_info["blockchain_list_entries"]) // Number of FAT Sectors (4 bytes)
          . pack("V", $num_sb_blocks + $num_bb_blocks) //ROOT START, First Directory Sector Location (4 bytes)
          . pack("V", 0) // Transaction Signature Number (4 bytes)
          . pack("V", 0x00001000) // Mini Stream Cutoff Size (4 bytes)
          . pack("V", $num_sb_blocks > 0 ? 0 : OLE_ENDOFCHAIN) // First Mini FAT Sector Location (4 bytes)
          . pack("V", $num_sb_blocks) // Number of Mini FAT Sectors (4 bytes)
          );

      // Extra BDList Start, Count
      if($bbd_info["blockchain_list_entries"] < $bbd_info["header_blockchain_list_entries"])
        {
          fwrite($FILE,
                 pack("V", OLE_ENDOFCHAIN).      // Extra BDList Start
                 pack("V", 0)        // Extra BDList Count
                 );
        }
      else
        {
          fwrite($FILE, pack("V", $num_sb_blocks + $num_bb_blocks + $num_pps_blocks + $bbd_info["0xFFFFFFFD_blockchain_entries"]) . pack("V", $bbd_info["0xFFFFFFFC_blockchain_entries"]));
        }

      // BDList
      for ($i=0; $i < $bbd_info["header_blockchain_list_entries"] and $i < $bbd_info["blockchain_list_entries"]; $i++) 
        {
          fwrite($FILE, pack("V", $num_bb_blocks + $num_sb_blocks + $num_pps_blocks + $i));
        }

      if($i < $bbd_info["header_blockchain_list_entries"])
        {
          for($j = 0; $j < ($bbd_info["header_blockchain_list_entries"]-$i); $j++) 
            {
              fwrite($FILE, (pack("V", OLE_FREESECT)));
            }
        }
    }

    /**
     * New method to calculate Bigblock chain
     *
     * @access private
     * @param integer $num_sb_blocks - number of Smallblock depot blocks
     * @param integer $num_bb_blocks - number of Bigblock depot blocks
     * @param integer $num_pps_blocks - number of PropertySetStorage blocks
     */
    function _calculate_big_block_chain($num_sb_blocks, $num_bb_blocks, $num_pps_blocks)
    {
      $bbd_info["entries_per_block"] = $this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE;
      $bbd_info["header_blockchain_list_entries"] = ($this->_BIG_BLOCK_SIZE - 0x4C) / OLE_LONG_INT_SIZE;
      $bbd_info["blockchain_entries"] = $num_sb_blocks + $num_bb_blocks + $num_pps_blocks;
      $bbd_info["0xFFFFFFFD_blockchain_entries"] = $this->get_number_of_pointer_blocks($bbd_info["blockchain_entries"]);
      $bbd_info["blockchain_list_entries"] = $this->get_number_of_pointer_blocks($bbd_info["blockchain_entries"] + $bbd_info["0xFFFFFFFD_blockchain_entries"]);

      // do some magic
      $bbd_info["ext_blockchain_list_entries"] = 0;
      $bbd_info["0xFFFFFFFC_blockchain_entries"] = 0;
      if($bbd_info["blockchain_list_entries"] > $bbd_info["header_blockchain_list_entries"])
        {
          do
            {
              $bbd_info["blockchain_list_entries"] = $this->get_number_of_pointer_blocks($bbd_info["blockchain_entries"] + $bbd_info["0xFFFFFFFD_blockchain_entries"] + $bbd_info["0xFFFFFFFC_blockchain_entries"]);
              $bbd_info["ext_blockchain_list_entries"] = $bbd_info["blockchain_list_entries"] - $bbd_info["header_blockchain_list_entries"];
              $bbd_info["0xFFFFFFFC_blockchain_entries"] = $this->get_number_of_pointer_blocks($bbd_info["ext_blockchain_list_entries"]);
              $bbd_info["0xFFFFFFFD_blockchain_entries"] = $this->get_number_of_pointer_blocks($num_sb_blocks + $num_bb_blocks + $num_pps_blocks + $bbd_info["0xFFFFFFFD_blockchain_entries"] + $bbd_info["0xFFFFFFFC_blockchain_entries"]);
            }
          while($bbd_info["blockchain_list_entries"] < $this->get_number_of_pointer_blocks($bbd_info["blockchain_entries"] + $bbd_info["0xFFFFFFFD_blockchain_entries"] + $bbd_info["0xFFFFFFFC_blockchain_entries"]));
        }

      return $bbd_info;
    }

    /**
     * Calculates number of pointer blocks
     *
     * @access public
     * @param integer $num_pointers - number of pointers
     */
    function get_number_of_pointer_blocks($num_pointers)
    {
      $pointers_per_block = $this->_BIG_BLOCK_SIZE / OLE_LONG_INT_SIZE;
      
      return floor($num_pointers / $pointers_per_block) + (($num_pointers % $pointers_per_block)? 1: 0);
    }

    /**
     * Support method for some hexdumping
     *
     * @access public
     * @param string $data - Binary data
     * @param integer $from - Start offset of data to dump
     * @param integer $to - Target offset of data to dump
     */
    function dump($data, $from, $to)
    {
      $chars = array();
      for($i = $from; $i < $to; $i++)
        {
          if(sizeof($chars) == 16)
            {
              printf("%08X (% 12d) |", $i-16, $i-16);
              foreach($chars as $char)
                printf(" %02X", $char);
              print " |\n";

              $chars = array();
            }

          $chars[] = ord($data[$i]);
        }

      if(sizeof($chars))
        {
          printf("%08X (% 12d) |", $i-sizeof($chars), $i-sizeof($chars));
          foreach($chars as $char)
            printf(" %02X", $char);
          print " |\n";
        }
    }
}
