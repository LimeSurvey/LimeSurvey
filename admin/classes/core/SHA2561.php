<?php


/*******************************************************************************
 *
 *      SHA256 static class for PHP4
 *      implemented by feyd _at_ devnetwork .dot. net
 *      specification from http://csrc.nist.gov/cryptval/shs/sha256-384-512.pdf
 *
 *      ? Copyright 2005 Developer's Network. All rights reserved.
 *      This is licensed under the Lesser General Public License (LGPL)
 *     
 *      Thanks to CertainKey Inc. for providing some example outputs in Javascript
 *
 *----- Version 1.0.1 ----------------------------------------------------------
 *
 *      Syntax:
 *            string SHA256::hash( string message[, string format ])
 *
 *      Description:
 *            SHA256::hash() is a static function that must be called with `message`
 *            and optionally `format`. Possible values for `format` are:
 *            'bin' binary string output
 *            'hex' default; hexidecimal string output (lower case)
 *
 *            Failures return FALSE.
 *
 *      Usage:
 *            $hash = SHA256::hash('string to hash');
 *
 ******************************************************************************/


//      hashing class state and storage object. Abstract base class only.
class hashData
{
        //      final hash
        var $hash = null;
}


//      hashing class. Abstract base class only.
class hash
{
        //      The base modes are:
        //            'bin' - binary output (most compact)
        //            'bit' - bit output (largest)
        //            'oct' - octal output (medium-large)
        //            'hex' - hexidecimal (default, medium)

        //      perform a hash on a string
        function hash($str, $mode = 'hex')
        {
                trigger_error('hash::hash() NOT IMPLEMENTED', E_USER_WARNING);
                return false;
        }

        //      chop the resultant hash into $length byte chunks
        function hashChunk($str, $length, $mode = 'hex')
        {
                trigger_error('hash::hashChunk() NOT IMPLEMENTED', E_USER_WARNING);
                return false;
        }
       
        //      perform a hash on a file
        function hashFile($filename, $mode = 'hex')
        {
                trigger_error('hash::hashFile() NOT IMPLEMENTED', E_USER_WARNING);
                return false;
        }

        //      chop the resultant hash into $length byte chunks
        function hashChunkFile($filename, $length, $mode = 'hex')
        {
                trigger_error('hash::hashChunkFile() NOT IMPLEMENTED', E_USER_WARNING);
                return false;
        }
}


//      ------------


class SHA256Data extends hashData
{
        //      buffer
        var $buf = array();
       
        //      padded data
        var $chunks = null;
       
        function SHA256Data($str)
        {
                $M = strlen($str);      //    number of bytes
                $L1 = ($M >> 28) & 0x0000000F;  //        top order bits
                $L2 = $M << 3;  //        number of bits
                $l = pack('N*', $L1, $L2);
               
                //      64 = 64 bits needed for the size mark. 1 = the 1 bit added to the
                //      end. 511 = 511 bits to get the number to be at least large enough
                //      to require one block. 512 is the block size.
                $k = $L2 + 64 + 1 + 511;
                $k -= $k % 512 + $L2 + 64 + 1;
                $k >>= 3;       //     convert to byte count
               
                $str .= chr(0x80) . str_repeat(chr(0), $k) . $l;
               
                assert('strlen($str) % 64 == 0');
               
                //      break the binary string into 512-bit blocks
                preg_match_all( '#.{64}#', $str, $this->chunks );
                $this->chunks = $this->chunks[0];
               
                //      H(0)
                /*
                $this->hash = array
                (
                        (int)0x6A09E667, (int)0xBB67AE85,
                        (int)0x3C6EF372, (int)0xA54FF53A,
                        (int)0x510E527F, (int)0x9B05688C,
                        (int)0x1F83D9AB, (int)0x5BE0CD19,
                );
                */
               
                $this->hash = array
                (
                        1779033703,          -1150833019,
                        1013904242,          -1521486534,
                        1359893119,          -1694144372,
                        528734635,             1541459225,
                );
        }
}


//      static class. Access via SHA256::hash()
class SHA256 extends hash
{
        function hash($str, $mode = 'hex')
        {
                static $modes = array( 'hex', 'bin', 'bit' );
                $ret = false;
               
                if(!in_array(strtolower($mode), $modes))
                {
                        trigger_error('mode specified is unrecognized: ' . $mode, E_USER_WARNING);
                }
                else
                {
                        $data =& new SHA256Data($str);

                        SHA256::compute($data);

                        $func = array('SHA256', 'hash' . $mode);
                        if(is_callable($func))
                        {
                                $func = 'hash' . $mode;
                                $ret = SHA256::$func($data);
                                //$ret = call_user_func($func, $data);
                        }
                        else
                        {
                                trigger_error('SHA256::hash' . $mode . '() NOT IMPLEMENTED.', E_USER_WARNING);
                        }
                }
               
                return $ret;
        }
       
        //      ------------
        //      begin internal functions
       
        //      32-bit summation
        function sum()
        {
                $T = 0;
                for($x = 0, $y = func_num_args(); $x < $y; $x++)
                {
                        //      argument
                        $a = func_get_arg($x);
                       
                        //      carry storage
                        $c = 0;
                       
                        for($i = 0; $i < 32; $i++)
                        {
                                //      sum of the bits at $i
                                $j = (($T >> $i) & 1) + (($a >> $i) & 1) + $c;
                                //      carry of the bits at $i
                                $c = ($j >> 1) & 1;
                                //      strip the carry
                                $j &= 1;
                                //      clear the bit
                                $T &= ~(1 << $i);
                                //      set the bit
                                $T |= $j << $i;
                        }
                }
               
                return $T;
        }
       
       
        //      compute the hash
        function compute(&$hashData)
        {
                static $vars = 'abcdefgh';
                static $K = null;
               
                if($K === null)
                {
                        /*
                        $K = array(
                                (int)0x428A2F98, (int)0x71374491, (int)0xB5C0FBCF, (int)0xE9B5DBA5,
                                (int)0x3956C25B, (int)0x59F111F1, (int)0x923F82A4, (int)0xAB1C5ED5,
                                (int)0xD807AA98, (int)0x12835B01, (int)0x243185BE, (int)0x550C7DC3,
                                (int)0x72BE5D74, (int)0x80DEB1FE, (int)0x9BDC06A7, (int)0xC19BF174,
                                (int)0xE49B69C1, (int)0xEFBE4786, (int)0x0FC19DC6, (int)0x240CA1CC,
                                (int)0x2DE92C6F, (int)0x4A7484AA, (int)0x5CB0A9DC, (int)0x76F988DA,
                                (int)0x983E5152, (int)0xA831C66D, (int)0xB00327C8, (int)0xBF597FC7,
                                (int)0xC6E00BF3, (int)0xD5A79147, (int)0x06CA6351, (int)0x14292967,
                                (int)0x27B70A85, (int)0x2E1B2138, (int)0x4D2C6DFC, (int)0x53380D13,
                                (int)0x650A7354, (int)0x766A0ABB, (int)0x81C2C92E, (int)0x92722C85,
                                (int)0xA2BFE8A1, (int)0xA81A664B, (int)0xC24B8B70, (int)0xC76C51A3,
                                (int)0xD192E819, (int)0xD6990624, (int)0xF40E3585, (int)0x106AA070,
                                (int)0x19A4C116, (int)0x1E376C08, (int)0x2748774C, (int)0x34B0BCB5,
                                (int)0x391C0CB3, (int)0x4ED8AA4A, (int)0x5B9CCA4F, (int)0x682E6FF3,
                                (int)0x748F82EE, (int)0x78A5636F, (int)0x84C87814, (int)0x8CC70208,
                                (int)0x90BEFFFA, (int)0xA4506CEB, (int)0xBEF9A3F7, (int)0xC67178F2
                                );
                        */
                        $K = array (
                                1116352408,          1899447441,    -1245643825,      -373957723,
                                961987163,            1508970993,      -1841331548,       -1424204075,
                                -670586216,          310598401,      607225278,  1426881987,
                                1925078388,          -2132889090, -1680079193,     -1046744716,
                                -459576895,          -272742522,    264347078,                604807628,
                                770255983,            1249150122,      1555081692,                1996064986,
                                -1740746414,    -1473132947,        -1341970488,    -1084653625,
                                -958395405,          -710438585,    113926993,                338241895,
                                666307205,            773529912,        1294757372,  1396182291,
                                1695183700,          1986661051,    -2117940946,      -1838011259,
                                -1564481375,    -1474664885,        -1035236496,    -949202525,
                                -778901479,          -694614492,    -200395387,              275423344,
                                430227734,            506948616,        659060556,    883997877,
                                958139571,            1322822218,      1537002063,                1747873779,
                                1955562222,          2024104815,    -2067236844,      -1933114872,
                                -1866530822,    -1538233109,        -1090935817,    -965641998,
                                );
                }
               
                $W = array();
                for($i = 0, $numChunks = sizeof($hashData->chunks); $i < $numChunks; $i++)
                {
                        //      initialize the registers
                        for($j = 0; $j < 8; $j++)
                                ${$vars{$j}} = $hashData->hash[$j];
                       
                        //      the SHA-256 compression function
                        for($j = 0; $j < 64; $j++)
                        {
                                if($j < 16)
                                {
                                        $T1  = ord($hashData->chunks[$i]{$j*4  }) & 0xFF; $T1 <<= 8;
                                        $T1 |= ord($hashData->chunks[$i]{$j*4+1}) & 0xFF; $T1 <<= 8;
                                        $T1 |= ord($hashData->chunks[$i]{$j*4+2}) & 0xFF; $T1 <<= 8;
                                        $T1 |= ord($hashData->chunks[$i]{$j*4+3}) & 0xFF;
                                        $W[$j] = $T1;
                                }
                                else
                                {
                                        $W[$j] = SHA256::sum(((($W[$j-2] >> 17) & 0x00007FFF) | ($W[$j-2] << 15)) ^ ((($W[$j-2] >> 19) & 0x00001FFF) | ($W[$j-2] << 13)) ^ (($W[$j-2] >> 10) & 0x003FFFFF), $W[$j-7], ((($W[$j-15] >> 7) & 0x01FFFFFF) | ($W[$j-15] << 25)) ^ ((($W[$j-15] >> 18) & 0x00003FFF) | ($W[$j-15] << 14)) ^ (($W[$j-15] >> 3) & 0x1FFFFFFF), $W[$j-16]);
                                }

                                $T1 = SHA256::sum($h, ((($e >> 6) & 0x03FFFFFF) | ($e << 26)) ^ ((($e >> 11) & 0x001FFFFF) | ($e << 21)) ^ ((($e >> 25) & 0x0000007F) | ($e << 7)), ($e & $f) ^ (~$e & $g), $K[$j], $W[$j]);
                                $T2 = SHA256::sum(((($a >> 2) & 0x3FFFFFFF) | ($a << 30)) ^ ((($a >> 13) & 0x0007FFFF) | ($a << 19)) ^ ((($a >> 22) & 0x000003FF) | ($a << 10)), ($a & $b) ^ ($a & $c) ^ ($b & $c));
                                $h = $g;
                                $g = $f;
                                $f = $e;
                                $e = SHA256::sum($d, $T1);
                                $d = $c;
                                $c = $b;
                                $b = $a;
                                $a = SHA256::sum($T1, $T2);
                        }
                       
                        //      compute the next hash set
                        for($j = 0; $j < 8; $j++)
                                $hashData->hash[$j] = SHA256::sum(${$vars{$j}}, $hashData->hash[$j]);
                }
        }
       
       
        //      set up the display of the hash in hex.
        function hashHex(&$hashData)
        {
                $str = '';
               
                reset($hashData->hash);
                do
                {
                        $str .= sprintf('%08x', current($hashData->hash));
                }
                while(next($hashData->hash));
               
                return $str;
        }
       
       
        //      set up the output of the hash in binary
        function hashBin(&$hashData)
        {
                $str = '';
               
                reset($hashData->hash);
                do
                {
                        $str .= pack('N', current($hashData->hash));
                }
                while(next($hashData->hash));
               
                return $str;
        }
}


//--------------
//      REMOVAL ALL FUNCTIONS AFTER THIS WHEN NOT TESTING
//--------------

//      format a string into 4 byte hex chunks
function hexerize($str)
{
        $n = 0;
        $b = 0;
        if(is_array($str))
        {
                reset($str);
                $o = 'array(' . sizeof($str) . ')::' . "\n\n";
                while($s = current($str))
                {
                        $o .= hexerize($s);
                        next($str);
                }
                $o .= 'end array;'."\n";
        }
        else
        {
                if(is_integer($str) || is_float($str))
                        $str = pack('N',$str);
                $o = 'string(' . strlen($str) . ')' . "::\n";
                for($i = 0, $j = strlen($str); $i < $j; $i++, $b = $i % 4)
                {
                        $o .= sprintf('%02X', ord($str{$i}));
                        //      only process when 32-bits have passed through
                        if($i != 0 && $b == 3)
                        {
                                //      process new line points
                                if($n == 3)
                                        $o .= "\n";
                                else
                                        $o .= ' ';
                                ++$n;
                                $n %= 4;
                        }
                }
        }
       
        return $o . "\n";
}


//      testing functions

function test1()
{
        $it = 1;
       
        echo '<pre>';

        $test = array('abc','abcdbcdecdefdefgefghfghighijhijkijkljklmklmnlmnomnopnopq');
       
        foreach($test as $str)
        {
                echo 'Testing ' . var_export($str,true) . "\n";
                list($s1,$s2) = explode(' ', microtime());
                for($x = 0; $x < $it; $x++)
                        $data =& new SHA256Data($str);
                list($e1,$e2) = explode(' ', microtime());
                echo hexerize($data->chunks);
                echo hexerize($data->hash);
                echo 'processing took ' . (($e2 - $s2 + $e1 - $s1) / $it) . ' seconds.' . "\n\n\n";
        }

        echo '</pre>';
}

function test2()
{
        $it = 1;
       
        echo '<pre>';
       
        $test = array('abc','abcdbcdecdefdefgefghfghighijhijkijkljklmklmnlmnomnopnopq');
       
        foreach($test as $str)
        {
                echo 'Testing ' . var_export($str,true) . "\n";
                list($s1,$s2) = explode(' ', microtime());
                for($x = 0; $x < $it; $x++)
                        $o = SHA256::hash($str);
                list($e1,$e2) = explode(' ', microtime());
                echo $o;
                echo 'processing took ' . (($e2 - $s2 + $e1 - $s1) / $it) . ' seconds.' . "\n\n\n";
        }
       
        echo '</pre>';
}

function testSum()
{
        echo '<pre>';
       
        echo SHA256::sum(1,2,3,4,5,6,7,8,9,10);
       
        echo '</pre>';
}

function testSpeedHash($it = 10)
{
        $it = intval($it);
        if($it === 0)
                $it = 10;
       
        set_time_limit(-1);
       
        echo '<pre>' . "\n";
       
        $test = array(
                ''=>'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855',
                'abc'=>'ba7816bf8f01cfea414140de5dae2223b00361a396177a9cb410ff61f20015ad',
                'message digest'=>'f7846f55cf23e14eebeab5b4e1550cad5b509e3348fbc4efa3a1413d393cb650',
                'secure hash algorithm'=>'f30ceb2bb2829e79e4ca9753d35a8ecc00262d164cc077080295381cbd643f0d',
                'SHA256 is considered to be safe'=>'6819d915c73f4d1e77e4e1b52d1fa0f9cf9beaead3939f15874bd988e2a23630',
                'abcdbcdecdefdefgefghfghighijhijkijkljklmklmnlmnomnopnopq'=>'248d6a61d20638b8e5c026930c3e6039a33ce45964ff2167f6ecedd419db06c1',
                'For this sample, this 63-byte string will be used as input data'=>'f08a78cbbaee082b052ae0708f32fa1e50c5c421aa772ba5dbb406a2ea6be342',
                'This is exactly 64 bytes long, not counting the terminating byte'=>'ab64eff7e88e2e46165e29f2bce41826bd4c7b3552f6b382a9e7d3af47c245f8',
                );
       
        foreach($test as $str => $hash)
        {
                echo 'Testing ' . var_export($str,true) . "\n";
                echo 'Start time: ' . date('Y-m-d H:i:s') . "\n";
                if($it > 1)
                {
                        list($s1,$s2) = explode(' ', microtime());
                        $o = SHA256::hash($str);
                        list($e1,$e2) = explode(' ', microtime());
                        echo 'estimated time to perform test: ' . (($e2 - $s2 + $e1 - $s1) * $it) . ' seconds for ' . $it . ' iterations.' . "\n";
                }
               
                $t = 0;
                for($x = 0; $x < $it; $x++)
                {
                        list($s1,$s2) = explode(' ', microtime());
                        $o = SHA256::hash($str);
                        list($e1,$e2) = explode(' ', microtime());
                        $t += $e2 - $s2 + $e1 - $s1;
                }
                echo var_export($o,true) . ' == ' . var_export($hash,true) . ' ' . (strcasecmp($o,$hash)==0 ? 'PASSED' : 'FAILED') . "\n";
                echo 'processing took ' . ($t / $it) . ' seconds.' . "\n\n\n";
        }
       
        echo '</pre>';
}

//testSpeedHash(1);

//--------------
//      END REMOVAL HERE
//--------------

/* EOF :: Document Settings: tab:4; */

?>