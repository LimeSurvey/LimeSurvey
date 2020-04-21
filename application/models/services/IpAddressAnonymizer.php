<?php


namespace LimeSurvey\Models\Services;

/**
 * This class offers a function to anonymize ip addresses.
 *
 * Class IpAddressAnonymizer
 * @package LimeSurvey\Models\Services
 */
class IpAddressAnonymizer
{

    /** @var string the original ip address */
    private $ipAddress;

    /**
     * IpAddressAnonymizer constructor.
     *
     * @param string $ipAddress
     */
    public function __construct($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Checks if ip is a valid ipv4
     *
     * @return boolean true if it is ipv4, false otherwise
     */
    public function isIpv4()
    {
        if($this->ipAddress === ''){
            $result = false;
        }else{
            $result = (boolean)filter_var($this->ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        return $result;
    }

    /**
     * Checks if ip is a valid ipv6
     *
     * @return boolean true if ipv6 is valid, false otherwise
     */
    public function isIpv6()
    {
        if($this->ipAddress === ''){
            $result = false;
        }else{
            $result = (boolean)filter_var($this->ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        }
        return $result;
    }

    /**
     * Checks if ip is a valid ipAddress
     *
     * @return boolean
     */
    public function isValidIp()
    {
        return $this->isIpv4() || $this->isIpv6();
    }

    /**
     * Anonymizes ip address:
     *
     * For instance, the IPv4 address 192.168.178.123 is anonymized to 192.168.178.0.
     * The IPv6 address 2a03:2880:2117:df07:face:b00c:5:1 is anonymized to 2a03:2880:2117:0:0:0:0:0
     * It also checks before anonymizes if it has already been anonymized and in that case give back
     * the ip address without changing it.
     *
     * @return string|boolean if ip is not anonymized false will be returned (in case of not a valid ip or ip has already been
     *                        anonymized), else the anonymize ip will be returned as a string
     */
    public function anonymizeIpAddress()
    {
        $anonymizedIp = false;

        if ($this->isIpv4()) { //check if it is valid ipv4
            $ipArray = explode('.', $this->ipAddress);
            $last_digit = array_pop($ipArray);
            if ($last_digit != 0) { //check if it has already been anonymized
                //set last number to 0
                $anonymizedIp = implode('.', $ipArray);
                $anonymizedIp .= '.0';
            }
        } elseif ($this->isIpv6()) { //check if it is valid ipv6
            $ipArray = explode(':', $this->ipAddress);
            //the last 5 blocks have to be set to 0 ...
            for ($i = 0; $i < 5; $i++) {
                array_pop($ipArray);
            }

            $anonymizedIp = implode(':', $ipArray);
            //append last 5 blocks with 0
            for ($i = 0; $i < 5; $i++) {
                $anonymizedIp .= ':0';
            }
        }

        return $anonymizedIp;
    }
}
