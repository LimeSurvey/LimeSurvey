<?php

    /**
     * Class containing helper functions for dealing with "big data".
     * @author Sam Mousa <sam@befound.nl>
     */
class BigData
{



    /**
     * This function combines json_encode and echo.
     * If a stream is passed (or is part of the array) it's content will be
     * directly streamed instead of reading it into memory first.
     * Supported flags:
     * JSON_FORCE_OBJECT
     * @param array $json
     * @param int $options Same flags used in JSON_ENCODE.
     */
    public static function json_echo($json, $options = 0)
    {
        // Scan array for any streams.
        $hasStream = array_reduce($json, array('BigData', 'hasStream'), false);

        // If there is no stream we are done.
        if (!$hasStream) {
            echo json_encode($json, $options);
        } else {
            self::json_echo_data($json, ($options & JSON_FORCE_OBJECT) == JSON_FORCE_OBJECT);
        }
    }

    protected static function hasStream($result, $item)
    {
        if ($result === true) {
            return true;
        } elseif (is_array($item)) {
            return array_reduce($item, array('BigData', 'hasStream'), false);
        }
        // Should use get_resource_type to do stricter check.
        elseif (self::isStream($item)) {
            return true;
        } else {
            return false;
        }
    }


    protected static function isStream($item)
    {
        return is_object($item) && get_class($item) == 'BigFile';
    }

    protected static function isAssociative($array)
    {
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }


    protected static function json_echo_data($json)
    {
        if (self::isStream($json)) {
            self::json_echo_stream($json);
        } elseif ((is_array($json) && self::isAssociative($json)) || is_object($json)) {
            self::json_echo_object($json);
        } elseif (is_array($json)) {
            self::json_echo_array($json);
        } elseif (is_numeric($json)) {
            self::json_echo_number($json);
        } elseif (is_string($json)) {
            self::json_echo_string($json);
        } elseif (is_null($json)) {
            echo json_encode(null);
        }
    }

    protected static function json_echo_array($json)
    {
        echo '[';
        foreach ($json as $key => $entry) {
            echo json_encode($key) . ':';
            self::json_echo_data($entry);
            echo ', '; // The extra comma is allowed: { 1: 'test', 2: 'test',} is valid.
        }
            echo ']';
    }

    protected static function json_echo_number($json)
    {
        echo $json;
    }

    protected static function json_echo_object($json)
    {
        echo '{';
            end($json);
            $lastKey = key($json);
            reset($json);
        foreach ($json as $key => $entry) {
            echo json_encode($key) . ':';
            self::json_echo_data($entry);
            if ($lastKey !== $key) {
                echo ', '; // The extra comma is allowed: { 1: 'test', 2: 'test',} is valid.
            }
        }
            echo '}';
    }

    /**
     * @param string $json
     */
    protected static function json_echo_string($json)
    {
        echo json_encode($json);
    }

    protected static function json_echo_stream(BigFile $data)
    {
        // Encode stream to base64.
        echo '"';
        $data->render();
        echo '"';
    }


    /**
     * @param string $name
     */
    protected static function tag($name, $data)
    {
        echo "<$name>$data</$name>\n";
    }
    /**
     * This function encodes PHP data to an XMLRPC response.
     */
    public static function xmlrpc_echo($data)
    {
        if (self::isStream($data)) {
            self::xmlrpc_echo_stream($data);
        } elseif ((is_array($data) && self::isAssociative($data)) || is_object($data)) {
            self::xmlrpc_echo_object($data);
        } elseif (is_array($data)) {
            self::xmlrpc_echo_array($data);
        } elseif (is_numeric($data)) {
            self::xmlrpc_echo_number($data);
        } elseif (is_string($data)) {
            self::xmlrpc_echo_string($data);
        }
    }

    protected static function xmlrpc_echo_array($data)
    {
        echo '<array>';
        echo '<data>';
        foreach ($data as $element) {
            echo '<value>';
            self::xmlrpc_echo($element);
            echo '</value>';
        }
        echo '</data>';
        echo '</array>';
    }

    /**
     * Prints XMLRPC numeric types.
     * @param type $data
     */
    protected static function xmlrpc_echo_number($data)
    {
        if (floor($data) == $data) {
            self::tag('int', $data);
        } else {
            self::tag('double', $data);
        }
    }

    protected static function xmlrpc_echo_object($data)
    {
        echo '<struct>';
        foreach ($data as $key => $value) {
            echo '<member>';
            echo self::tag('name', "<![CDATA[$key]]>");
            echo '<value>';
            self::xmlrpc_echo($value);
            echo '</value>';

            echo '</member>';
        }
        echo '</struct>';
    }

    protected static function xmlrpc_echo_stream($data)
    {
        echo '<string>'; // a Base64 tag would be more sensible here but it would break all current implementations
        $data->render();
        echo '</string>';
    }

    /**
     * @param string $data
     */
    protected static function xmlrpc_echo_string($data)
    {
        self::tag('string', "<![CDATA[$data]]>");
    }
}

class BigFile
{

    public $fileName;
    protected $deleteAfterUse;
    protected $defaultEcho;

    public function __construct($fileName, $deleteAfterUse = true, $defaultEcho = 'base64')
    {
        $this->fileName = $fileName;
        $this->deleteAfterUse = $deleteAfterUse;
        $this->defaultEcho = $defaultEcho;
    }

    public function render($type = null)
    {
        if (!isset($type)) {
            $type = $this->defaultEcho;
        }
        // TODO: No other types supported, ever?
        if ($type !== 'base64') {
            throw new Exception('Unsupported echo type');
        }
        $this->echo_base64();
        if ($this->deleteAfterUse) {
            unlink($this->fileName);
        }
    }

    protected function echo_base64()
    {
        $fileHandle = fopen($this->fileName, 'r');
        stream_filter_append($fileHandle, 'convert.base64-encode', STREAM_FILTER_READ);
        fpassthru($fileHandle);
        fclose($fileHandle);
    }
}
