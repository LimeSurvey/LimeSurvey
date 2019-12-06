<?php

class LSJsonException extends CHttpException {

    private $redirectUrl = null;
    private $noReload = false;

    /**
     * Alternating constructor for json compatible error handling
     *
     * @param integer $status
     * @param string $message
     * @param integer $code
     * @param string $redirectUrl
     * @param boolean $noReload
     */
    public function __construct($status, $message = null, $code = 0, $redirectUrl = null, $noReload = false) {
        parent::__construct($status, $message, $code);
        $this->redirectUrl = $redirectUrl;
        $this->noReload = $noReload;
    }

    public function setRedirectUrl($redirectUrl) {
        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    public function setNoReload($noReload) {
        $this->noReload = $noReload;
    }

    public function getNoReload() {
        return $this->noReload;
    }

}
