<?php 

    /**
     * Class containing helper functions for dealing with "big data".
     * @author Sam Mousa <sam@befound.nl>
     */
    class BigData {
        
        /**
         * This function combines json_encode and echo.
         * If a stream is passed (or is part of the array) it's content will be
         * directly streamed instead of reading it into memory first.
         * Supported flags:
         * JSON_FORCE_OBJECT
         * @param array $json
         * @param int $options Same flags used in JSON_ENCODE.
         */
        public static function json_echo($json, $options)
        {
            // Scan array for any streams.
            $hasStream = array_reduce($json, array('BigData', 'hasStream'), false);
            
            // If there is no stream we are done.
            if (!$hasStream)
            {
                echo json_encode($json, $options);
            }
            else
            {
                self::json_echo_data($json, ($options & JSON_FORCE_OBJECT) == JSON_FORCE_OBJECT);
            }
            
        }
        
        protected static function hasStream(&$result, $item)
        {
            if ($result === true)
            {
                return true;
            }
            elseif(is_array($item))
            {
                return array_reduce($item, array('BigData', 'hasStream'), false);
            }
            // Should use get_resource_type to do stricter check.
            elseif (self::isStream($item))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        
        
        protected static function isStream($item)
        {
            return is_resource($item);
        }
        
        protected static function isAssociative($array)
        {
            foreach ($array as $key => $value)
            {
                if (is_string($key))
                {
                    return true;
                }
            }
            return false;
        }
        
        
        protected static function json_echo_data($json)
        {
            if ((is_array($json) && self::isAssociative($json)) || is_object($json))
            {
                self::json_echo_object($json);
            }
            elseif (is_array($json))
            {
                self::json_echo_array($json);
            }
            elseif (is_numeric($json))
            {
                self::json_echo_number($json);
            }
            elseif (is_string($json))
            {
                self::json_echo_string($json);
            }
            elseif (self::isStream($json))
            {
                self::json_echo_stream($json);
            }
        }
        
        private static function json_echo_array($json)
        {
            echo '[';
                foreach ($json as $key => $entry)
                {
                    echo json_encode($key) . ':';
                    self::json_echo_data($entry);
                    echo ', '; // The extra comma is allowed: { 1: 'test', 2: 'test',} is valid.
                }
                echo ']';
        }
        
        private static function json_echo_number($json)
        {
            echo $json;
        }

        private static function json_echo_object($json)
        {
            echo '{';
                foreach ($json as $key => $entry)
                {
                    echo json_encode($key) . ':';
                    self::json_echo_data($entry);
                    echo ', '; // The extra comma is allowed: { 1: 'test', 2: 'test',} is valid.
                }
                echo '}';
        }
        
        private static function json_echo_string($json)
        {
            echo json_encode($json);
        }
        
        private static function json_echo_stream($json)
        {
            // Encode stream to base64.
            echo "'";
            stream_filter_append($json, 'convert.base64-encode', STREAM_FILTER_READ, array('line-length' => 50, 'line-break-chars' => "\n"));
            fpassthru($json);
            echo "'";
        }
    }

?>