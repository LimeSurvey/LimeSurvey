<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Time;

use DateTime;
use Exception;

/**
 * Takes the time from any webserver by doing a HEAD request on the specified URL and extracting the 'Date:' header
 */
class HttpTimeProvider implements ITimeProvider
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $url = 'https://google.com',
        public string $expectedtimeformat = 'D, d M Y H:i:s O+',
        public ?array $options = null,
    ) {
        if ($this->options === null) {
            $this->options = array(
                'http' => array(
                    'method' => 'HEAD',
                    'follow_location' => false,
                    'ignore_errors' => true,
                    'max_redirects' => 0,
                    'request_fulluri' => true,
                    'header' => array(
                        'Connection: close',
                        'User-agent: TwoFactorAuth HttpTimeProvider (https://github.com/RobThree/TwoFactorAuth)',
                        'Cache-Control: no-cache',
                    ),
                ),
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTime()
    {
        try {
            $context = stream_context_create($this->options);
            $fd = fopen($this->url, 'rb', false, $context);
            $headers = stream_get_meta_data($fd);
            fclose($fd);

            foreach ($headers['wrapper_data'] as $h) {
                if (strcasecmp(substr($h, 0, 5), 'Date:') === 0) {
                    return DateTime::createFromFormat($this->expectedtimeformat, trim(substr($h, 5)))->getTimestamp();
                }
            }
            throw new Exception('Invalid or no "Date:" header found');
        } catch (Exception $ex) {
            throw new TimeException(sprintf('Unable to retrieve time from %s (%s)', $this->url, $ex->getMessage()));
        }
    }
}
