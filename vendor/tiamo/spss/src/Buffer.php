<?php

namespace SPSS;

class Buffer
{
    /**
     * @var mixed
     */
    public $context;

    /**
     * @var bool
     */
    public $isBigEndian = false;

    /**
     * @var string
     */
    public $charset;

    /**
     * @var resource
     */
    private $_stream;

    /**
     * @var int
     */
    private $_position = 0;

    /**
     * Buffer constructor.
     *
     * @param resource $stream  stream resource to wrap
     * @param array    $options associative array of options
     */
    private function __construct($stream, $options = [])
    {
        if (!\is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource.');
        }
        $this->_stream = $stream;

        if (isset($options['context'])) {
            $this->context = $options['context'];
        }
    }

    /**
     * Create a new stream based on the input type.
     *
     * @param resource|string $resource Entity body data
     * @param array           $options  Additional options
     *
     * @return Buffer
     */
    public static function factory($resource = '', $options = [])
    {
        $type = \gettype($resource);

        switch ($type) {
            case 'string':
                $stream = isset($options['memory']) ?
                    fopen('php://memory', 'rb+') :
                    fopen('php://temp', 'rb+');
                if ('' !== $resource) {
                    fwrite($stream, $resource);
                    fseek($stream, 0);
                }

                return new self($stream, $options);
            case 'resource':
                return new self($resource, $options);
            case 'object':
                if (method_exists($resource, '__toString')) {
                    return self::factory((string) $resource, $options);
                }
        }

        throw new \InvalidArgumentException(sprintf('Invalid resource type: %s.', $type));
    }

    /**
     * @param int  $length
     * @param bool $skip
     *
     * @throws Exception
     *
     * @return Buffer
     */
    public function allocate($length, $skip = true)
    {
        $stream = fopen('php://memory', 'rb+');
        if (stream_copy_to_stream($this->_stream, $stream, $length)) {
            if ($skip) {
                $this->skip($length);
            }

            return new self($stream);
        }
        throw new Exception('Buffer allocation failed.');
    }

    /**
     * @param int $length
     *
     * @return void
     */
    public function skip($length)
    {
        $this->_position += $length;
    }

    /**
     * @param string $file Path to file
     *
     * @return false|int
     */
    public function saveToFile($file)
    {
        rewind($this->_stream);

        return file_put_contents($file, $this->_stream);
    }

    /**
     * @param resource $resource
     * @param int|null $maxlength
     *
     * @return false|int
     */
    public function writeStream($resource, $maxlength = null)
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Invalid resource type.');
        }

        if ($maxlength) {
            $length = stream_copy_to_stream($resource, $this->_stream, $maxlength);
        } else {
            $length = stream_copy_to_stream($resource, $this->_stream);
        }

        $this->_position += $length;

        return $length;
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->_stream;
    }

    /**
     * @param int  $length
     * @param int  $round
     * @param null $charset
     *
     * @return false|string
     */
    public function readString($length, $round = 0, $charset = null)
    {
        if ($bytes = $this->readBytes($length)) {
            if ($round !== 0) {
                $this->skip(Utils::roundUp($length, $round) - $length);
            }
            $str = Utils::bytesToString($bytes);
            if ($charset) {
                $str = mb_convert_encoding($str, 'utf8', $charset);
            } elseif (!empty($this->charset)) {
                $str = mb_convert_encoding($str, 'utf8', $this->charset);
            }

            return $str;
        }

        return false;
    }

    /**
     * @param $length
     *
     * @return false|array
     */
    public function readBytes($length)
    {
        $bytes = $this->read($length);
        if (false !== $bytes) {
            return array_values(unpack('C*', $bytes));
        }

        return false;
    }

    /**
     * @param int $length
     *
     * @return false|string
     */
    public function read($length = null)
    {
        $bytes = stream_get_contents($this->_stream, $length, $this->_position);
        if (false !== $bytes) {
            $this->_position += $length;
        }

        return $bytes;
    }

    /**
     * @param $data
     * @param int|string $length
     * @param null       $charset
     *
     * @return false|int
     */
    public function writeString($data, $length = '*', $charset = null)
    {
        if ($charset) {
            $data = mb_convert_encoding($data, 'utf8', $charset);
        } elseif (!empty($this->charset)) {
            $data = mb_convert_encoding($data, 'utf8', $this->charset);
        }

        return $this->write(pack('A' . $length, $data));
    }

    /**
     * @param string   $data
     * @param int|null $length
     *
     * @return false|int
     */
    public function write($data, $length = null)
    {
        $length = $length ? fwrite($this->_stream, $data, $length) : fwrite($this->_stream, $data);
        $this->_position += $length;

        return $length;
    }

    /**
     * @return float
     */
    public function readDouble()
    {
        return $this->readNumeric(8, 'd');
    }

    /**
     * @param $data
     *
     * @return false|int
     */
    public function writeDouble($data)
    {
        return $this->writeNumeric($data, 'd', 8);
    }

    /**
     * @param $data
     * @param $format
     * @param null $length
     *
     * @return false|int
     */
    public function writeNumeric($data, $format, $length = null)
    {
        return $this->write(pack($format, $data), $length);
    }

    /**
     * @return false|float
     */
    public function readFloat()
    {
        return $this->readNumeric(4, 'f');
    }

    /**
     * @param $data
     *
     * @return false|int
     */
    public function writeFloat($data)
    {
        return $this->writeNumeric($data, 'f', 4);
    }

    /**
     * @return int
     */
    public function readInt()
    {
        return $this->readNumeric(4, 'i');
    }

    /**
     * @param $data
     *
     * @return false|int
     */
    public function writeInt($data)
    {
        return $this->writeNumeric($data, 'i', 4);
    }

    /**
     * @return false|int
     */
    public function readShort()
    {
        return $this->readNumeric(2, 'v');
    }

    /**
     * @param $data
     *
     * @return false|int
     */
    public function writeShort($data)
    {
        return $this->writeNumeric($data, 'v', 2);
    }

    /**
     * @param $length
     *
     * @return false|int
     */
    public function writeNull($length)
    {
        return $this->write(pack('x' . $length));
    }

    /**
     * @return int
     */
    public function position()
    {
        // return ftell($this->_stream);
        return $this->_position;
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return int
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->_position = $offset;

        return fseek($this->_stream, $offset, $whence);
    }

    /**
     * @return void
     */
    public function rewind()
    {
        if (rewind($this->_stream)) {
            $this->_position = 0;
        }
    }

    /**
     * @return void
     */
    public function truncate()
    {
        ftruncate($this->_stream, 0);
        $this->_position = 0;
    }

    /**
     * @return true|false
     */
    public function close()
    {
        return fclose($this->_stream);
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return stream_get_meta_data($this->_stream);
    }

    /**
     * @param int    $length
     * @param string $format
     *
     * @return false|int|float|float
     */
    private function readNumeric($length, $format)
    {
        $bytes = $this->read($length);
        if (false !== $bytes) {
            if ($this->isBigEndian) {
                $bytes = strrev($bytes);
            }
            $data = unpack($format, $bytes);

            return $data[1];
        }

        return false;
    }
}
