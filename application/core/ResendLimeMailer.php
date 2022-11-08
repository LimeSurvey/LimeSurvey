<?php

/**
 * Extension of LimeMailer, but for resending emails after failure.
 */
class ResendLimeMailer extends LimeMailer
{
    private $resendVars = [];

    public function setResendVars($vars)
    {
        $this->resendVars = $vars;
    }

    protected function setMessageType()
    {
        $this->message_type = $this->resendVars['message_type'];
    }
}
