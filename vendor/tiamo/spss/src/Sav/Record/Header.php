<?php

namespace SPSS\Sav\Record;

use SPSS\Buffer;
use SPSS\Exception;
use SPSS\Sav\Record;

class Header extends Record
{
    const NORMAL_REC_TYPE = '$FL2';
    const ZLIB_REC_TYPE   = '$FL3';

    /**
     * @var string Record type code,
     *             either ‘$FL2’ for system files with uncompressed data or data compressed with simple bytecode compression,
     *             or ‘$FL3’ for system files with ZLIB compressed data.
     *             This is truly a character field that uses the character encoding as other strings.
     *             Thus, in a file with an ASCII-based character encoding this field contains 24 46 4c 32 or 24 46 4c 33,
     *             and in a file with an EBCDIC-based encoding this field contains 5b c6 d3 f2.
     *             (No EBCDIC-based ZLIB-compressed files have been observed.)
     */
    public $recType = self::NORMAL_REC_TYPE;

    /**
     * @var string Product identification string.
     *             This always begins with the characters ‘@(#) SPSS DATA FILE’.
     *             The string is truncated if it would be longer than 60 characters;
     *             otherwise it is padded on the right with spaces.
     */
    public $prodName = '@(#) SPSS DATA FILE';

    /**
     * @var int normally set to 2, although a few system files have been spotted in the wild with a value of 3 here
     */
    public $layoutCode = 2;

    /**
     * @var int Number of data elements per case.
     *          This is the number of variables, except that long string variables add extra data elements (one for every 8 characters after the first 8).
     *          However, string variables do not contribute to this value beyond the first 255 bytes.
     *          Further, system files written by some systems set this value to -1.
     *          In general, it is unsafe for systems reading system files to rely upon this value.
     */
    public $nominalCaseSize = 0;

    /**
     * @var int
     *          Set to
     *          0 if the data in the file is not compressed,
     *          1 if the data is compressed with simple bytecode compression,
     *          2 if the data is ZLIB compressed.
     *          This field has value 2 if and only if recType is ‘$FL3’.
     */
    public $compression = 1;

    /**
     * @var int If one of the variables in the data set is used as a weighting variable,
     *          set to the dictionary index of that variable, plus 1 (see Dictionary Index).
     *          Otherwise, set to 0.
     */
    public $weightIndex = 0;

    /**
     * @var int Set to the number of cases in the file if it is known, or -1 otherwise.
     *          In the general case it is not possible to determine the number of cases that will be output to a system file at the time
     *          that the header is written.
     *          The way that this is dealt with is by writing the entire system file,
     *          including the header, then seeking back to the beginning of the file and writing just the ncases field.
     *          For files in which this is not valid, the seek operation fails.
     *          In this case, ncases remains -1.
     */
    public $casesCount = -1;

    /**
     * @var int Compression bias, ordinarily set to 100.
     *          Only integers between 1 - bias and 251 - bias can be compressed.
     *          By assuming that its value is 100.
     */
    public $bias = 100;

    /**
     * @var string Date of creation of the system file, in ‘dd mmm yy’ format,
     *             with the month as standard English abbreviations, using an initial capital letter and following with lowercase.
     *             If the date is not available then this field is arbitrarily set to ‘01 Jan 70’.
     */
    public $creationDate = '01 Jan 70';

    /**
     * @var string Time of creation of the system file, in ‘hh:mm:ss’ format and using 24-hour time.
     *             If the time is not available then this field is arbitrarily set to ‘00:00:00’.
     */
    public $creationTime = '00:00:00';

    /**
     * @var string File label declared by the user (64 chars).
     *             Padded on the right with spaces.
     */
    public $fileLabel;

    /**
     * @param  Buffer  $buffer
     */
    public function read(Buffer $buffer)
    {
        $this->recType = $buffer->readString(4);
        if (self::NORMAL_REC_TYPE !== $this->recType && self::ZLIB_REC_TYPE !== $this->recType) {
            throw new Exception('Read header error: this is not a valid SPSS file. Does not start with $FL2 or $FL3.');
        }
        $this->prodName   = trim($buffer->readString(60));
        $this->layoutCode = $buffer->readInt();

        // layoutCode should be 2 or 3.
        // If not swap bytes and check again which would then indicate big-endian
        if (2 !== $this->layoutCode && 3 !== $this->layoutCode) {
            // try to flip to big-endian mode and read again
            $buffer->isBigEndian = true;
            $buffer->skip(-4);
            $this->layoutCode = $buffer->readInt();
        }

        $this->nominalCaseSize = $buffer->readInt();
        $this->compression     = $buffer->readInt();
        $this->weightIndex     = $buffer->readInt();
        $this->casesCount      = $buffer->readInt();
        $this->bias            = $buffer->readDouble();
        $this->creationDate    = $buffer->readString(9);
        $this->creationTime    = $buffer->readString(8);
        $this->fileLabel       = trim($buffer->readString(64));

        // 3-byte padding to make the header a multiple of 32 bits in length.
        $buffer->skip(3);
    }

    public function write(Buffer $buffer)
    {
        $buffer->write($this->recType);
        $buffer->writeString($this->prodName, 60);
        $buffer->writeInt($this->layoutCode);
        $buffer->writeInt($this->nominalCaseSize);
        $buffer->writeInt($this->compression);
        $buffer->writeInt($this->weightIndex);
        $buffer->writeInt($this->casesCount);
        $buffer->writeDouble($this->bias);
        $buffer->writeString($this->creationDate, 9);
        $buffer->writeString($this->creationTime, 8);
        $buffer->writeString($this->fileLabel, 64);
        $buffer->writeNull(3);
    }

    public function increaseCasesCount(Buffer $buffer)
    {
        // Jump to the position of the casesCount in the header, re-write it and keep the current position.
        // recType + prodName + layoutCode + nominalCaseSize + compression + weightIndex
        // 4       + 60       + 4          + 4               + 4           + 4 = 80
        $this->casesCount++;
        $pos = $buffer->position();
        $buffer->seek(80);
        $buffer->writeInt($this->casesCount);
        $buffer->seek($pos);
    }
}
