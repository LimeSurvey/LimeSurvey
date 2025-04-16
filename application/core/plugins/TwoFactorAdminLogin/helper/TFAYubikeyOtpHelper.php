<?php

class TFAYubikeyOtpHelper
{
    /** @var string the OTP code to be validated */
    private $otpCode;
    /** @var string the client ID for the YubiCloud API */
    private $clientId;
    /** @var string the client secret for the YubiCloud API */
    private $clientSecret;
    /** @var string the last error message, if any */
    private $lastError = "";

    /** @var bool If true, already seen OTPs will not be rejected. */
    private $allowReplayedOtp = false;

    /**
     * @param string $otpCode   The full Yubikey OTP code
     * @param string $clientId  The client ID. If not set, the test mode ID will be used.
     * @param string $clientSecret  The client secret. The request won't be signed and the response signature won't be verified.
     */
    function __construct($otpCode, $clientId = null, $clientSecret = null)
    {
        $this->otpCode = trim($otpCode);
        $this->clientId = !empty($clientId) ? $clientId : 1;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Returns the public ID part of the OTP code (in modhex format)
     * @return string
     */
    public function getPublicId()
    {
        if (strlen($this->otpCode) !== 44) {
            $this->setLastError(gT("Yubikey OTP code must be 44 characters long."));
            return null;
        }
        return substr($this->otpCode, 0, 12);
    }

    /**
     * Validates the OTP code against the YubiCloud API.
     * If the code is not valid, the error can be retrieved using getLastError().
     * @return bool true if the OTP code is valid, false otherwise
     */
    public function verifyOtp()
    {
        $this->clearLastError();
        if (strlen($this->otpCode) !== 44) {
            $this->setLastError(gT("Yubikey OTP code must be 44 characters long."));
            return false;
        }
        $endpoint = 'https://api.yubico.com/wsapi/2.0/verify';
        $nonce = $this->generateNonce();
        $params = [];
        if (!empty($this->clientId)) {
            $params['id'] = $this->clientId;
        }
        $params['otp'] = $this->otpCode;
        $params['nonce'] = $nonce;
        if (!empty($this->clientSecret)) {
            $params['h'] = $this->getHmacSignature($params);
        }
        $paramsString = http_build_query($params);

        $url = $endpoint . '?' . $paramsString;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result) {
            $response = $this->parseResponse($result);
            $this->validateResponse($response, $nonce);

            $status = $response['status'];
            if ($status == 'OK' || ($this->allowReplayedOtp && $status == 'REPLAYED_OTP')) {
                return true;
            }

            switch ($status) {
                case 'BAD_OTP':
                    $this->setLastError(gT("The OTP has an invalid format."));
                    break;
                case 'BAD_SIGNATURE':
                    $this->setLastError(gT("The HMAC signature verification failed."));
                    break;
                case 'MISSING_PARAMETER':
                    $this->setLastError(gT("The request lacks a parameter."));
                    break;
                case 'NO_SUCH_CLIENT':
                    $this->setLastError(gT("The request ID does not exist."));
                    break;
                case 'OPERATION_NOT_ALLOWED':
                    $this->setLastError(gT("The request ID is not allowed to verify OTPs."));
                    break;
                case 'BACKEND_ERROR':
                    $this->setLastError(gT("Unexpected error in YubiCloud server."));
                    break;
                case 'NOT_ENOUGH_ANSWERS':
                    $this->setLastError(gT("Server could not get requested number of syncs during before timeout"));
                    break;
                case 'REPLAYED_REQUEST':
                    $this->setLastError(gT("Server has seen the OTP/nonce combination before"));
                    break;
                case 'REPLAYED_OTP':
                    // We should only get here if $this->allowReplayedOtp is false.
                    $this->setLastError(gT("The OTP has already been seen by the service."));
                    break;
                default:
                    $this->setLastError(gT("Unexpected error."));
                    break;
            }
        }
        return false;
    }

    /**
     * Parses the YubiCloud response into an associative array.
     * @param string $response the YubiCloud response
     * @return array the parsed response
     */
    private function parseResponse($response)
    {
        $response = trim($response);
        $lines = explode("\n", $response);
        $result = [];
        foreach ($lines as $line) {
            $parts = explode("=", $line, 2);
            if (count($parts) == 2) {
                $result[trim($parts[0])] = trim($parts[1]);
            }
        }
        return $result;
    }

    private function generateNonce()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $nonce = '';
        for ($i = 0; $i < 20; $i++) {
            $nonce .= $characters[rand(0, $charactersLength - 1)];
        }
        return $nonce;
    }

    /**
     * Calculates the HMAC signature for the given parameters.
     * @param array $params the parameters to be signed
     * @return string the HMAC signature
     */
    private function getHmacSignature($params)
    {
        unset($params["h"]);
        ksort($params);
        $paramsString = http_build_query($params);
        return base64_encode(hash_hmac('sha1', $paramsString, base64_decode($this->clientSecret), true));
    }

    /**
     * Validates the authenticity of the YubiCloud response.
     * @param array $response the YubiCloud response
     * @param string $originalNonce the original nonce included in the request
     * @return bool true if the response is valid, false otherwise
     */
    private function validateResponse($response, $originalNonce)
    {
        if (empty($response)) {
            $this->setLastError(gT("YubiCloud response is empty."));
            return false;
        }

        if (empty($response['status'])) {
            $this->setLastError(gT("YubiCloud response does not contain a status."));
            return false;
        }

        $status = $response['status'];

        // If the status is OK, the response must contain the same nonce as the request.
        if ($status == 'OK' || $status == 'REPLAYED_OTP') {
            $responseNonce = $response['nonce'] ?? null;
            if ($responseNonce != $originalNonce) {
                $this->setLastError(gT("Could not verify the authenticity of the YubiCloud response."));
                return false;
            }
        }

        // If there is a client secret, verify the response signature.
        if (!empty($this->clientSecret)) {
            if (empty($response['h'])) {
                $this->setLastError(gT("Could not verify the authenticity of the YubiCloud response."));
                return false;
            }
            $responseSignature = $response['h'];
            $calculatedSignature = $this->getHmacSignature($response);
            if ($responseSignature != $calculatedSignature) {
                $this->setLastError(gT("Could not verify the authenticity of the YubiCloud response."));
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the last error message, if any.
     * @return string the last error message
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Clears the last error message.
     */
    public function clearLastError()
    {
        $this->lastError = "";
    }

    /**
     * Sets the last error message.
     * @param string $error the error message
     */
    public function setLastError($error)
    {
        $this->lastError = $error;
    }
}
