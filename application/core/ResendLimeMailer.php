<?php

/**
 * Extension of LimeMailer, but for resending emails after failure.
 * If $resendVars is not set, it will default to parent behaviour (LimeMailer).
 */
class ResendLimeMailer extends LimeMailer
{
    private $resendVars = [];

    public function setResendVars($vars)
    {
        $this->resendVars = $vars;
    }

    /**
     * @return string
     */
    public function createBody()
    {
        if ($this->resendVars) {
            return $this->resendVars['MIMEBody'];
        } else {
            return parent::createBody();
        }
    }

    protected function setMessageType()
    {
        if ($this->resendVars) {
            $this->message_type = $this->resendVars['message_type'];
        } else {
            parent::setMessageType();
        }
    }

    protected function generateId()
    {
        if ($this->resendVars) {
            return $this->resendVars['uniqueid'];
        } else {
            return $this->generateId();
        }
    }
}
