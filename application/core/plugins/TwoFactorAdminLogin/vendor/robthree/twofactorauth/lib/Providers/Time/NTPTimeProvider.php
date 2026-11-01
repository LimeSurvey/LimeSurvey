<?php

declare(strict_types=1);

namespace RobThree\Auth\Providers\Time;

use Exception;

use function socket_create;

/**
 * Takes the time from any NTP server
 */
class NTPTimeProvider implements ITimeProvider
{
    public function __construct(public string $host = 'time.google.com', public int $port = 123, public int $timeout = 1)
    {
        if ($this->port <= 0 || $this->port > 65535) {
            throw new TimeException('Port must be 0 < port < 65535');
        }

        if ($this->timeout < 0) {
            throw new TimeException('Timeout must be >= 0');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTime()
    {
        try {
            // Create a socket and connect to NTP server
            $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $this->timeout, 'usec' => 0));
            socket_connect($sock, $this->host, $this->port);

            // Send request
            $msg = "\010" . str_repeat("\0", 47);
            socket_send($sock, $msg, strlen($msg), 0);

            // Receive response and close socket
            if (socket_recv($sock, $recv, 48, MSG_WAITALL) === false) {
                throw new Exception(socket_strerror(socket_last_error($sock)));
            }
            socket_close($sock);

            // Interpret response
            $data = unpack('N12', $recv);
            $timestamp = (int)sprintf('%u', $data[9]);

            // NTP is number of seconds since 0000 UT on 1 January 1900 Unix time is seconds since 0000 UT on 1 January 1970
            return $timestamp - 2208988800;
        } catch (Exception $ex) {
            throw new TimeException(sprintf('Unable to retrieve time from %s (%s)', $this->host, $ex->getMessage()));
        }
    }
}
