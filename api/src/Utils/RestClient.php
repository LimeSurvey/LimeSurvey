<?php

namespace App\Utils;

use Exception;
use CurlHandle;

class RestClient
{
    public const JSON = "JSON";
    public const FORM_DATA = "FORM_DATA";

    public function __construct()
    {
    }

    /**
     * For debug, use:
     * curl_setopt($client, CURLOPT_VERBOSE, TRUE);
     */
    public function call(string $method, string $url, array $headers, mixed $data, string $format = self::FORM_DATA): RestClientResult
    {
        $client = curl_init();

        if ($format == self::JSON) {
            $data = json_encode($data);
            array_push($headers, "Content-Type: application/json");
            array_push($headers, "Accept: application/json");
        }

        switch ($method) {
            case "POST":
                curl_setopt($client, CURLOPT_POST, 1);
                if ($data) {
                    curl_setopt($client, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PUT":
                curl_setopt($client, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($client, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "PATCH":
                curl_setopt($client, CURLOPT_CUSTOMREQUEST, "PATCH");
                if ($data) {
                    curl_setopt($client, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }

        $this->setCurlOptions($client, $url, $headers);

        /** @var bool|string */
        $result = curl_exec($client);
        /** @var string */
        $error = curl_error($client);

        if ($result === false) {
            throw new Exception($error);
        }

        if ($result === true) {
            throw new Exception('curl returned true, CURLOPT_RETURNTRANSFER missing?');
        }

        $httpcode = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        $res = new RestClientResult();
        $res->setCode($httpcode);
        $res->setResult($result);

        return $res;
    }

    public static function convertArrayToQueryParameters(array $params): string
    {
        $res = "";
        if (count($params) > 0) {
            $res .= "?";

            foreach ($params as $pkey => $pval) {
                if (strlen($res) > 1) {
                    $res .= "&";
                }
                $res .= "${pkey}=${pval}";
            }
        }

        return $res;
    }

    /**
     * Setup the CURL
     */
    private function setCurlOptions(CurlHandle $client, string $url, array $headers): void
    {
        // OPTIONS:
        curl_setopt($client, CURLOPT_URL, $url);
        curl_setopt($client, CURLOPT_FRESH_CONNECT, 1);
        curl_setopt($client, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($client, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($client, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false);
    }
}
