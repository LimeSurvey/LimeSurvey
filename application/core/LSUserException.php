<?php

class LSUserException extends CHttpException
{
    /** @var string */
    private $redirectUrl = null;

    /** @var boolean */
    private $noReload = false;

    /** @var string[] */
    private $detailedErrors = [];

    /**
     * Alternating constructor for json compatible error handling
     *
     * @param integer $status
     * @param string $message
     * @param integer $code
     * @param string $redirectUrl
     * @param boolean $noReload
     */
    public function __construct($status, $message = null, $code = 0, $redirectUrl = null, $noReload = false)
    {
        parent::__construct($status, $message, $code);
        $this->redirectUrl = $redirectUrl;
        $this->noReload = $noReload;
    }

    /**
     * Sets the redirect URL
     * @param string $redirectUrl
     * @return static   Return self instance to allow method chaining
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;
        return $this;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Sets the "No Reload" property
     * @param boolean $noReload
     * @return static   Return self instance to allow method chaining
     */
    public function setNoReload($noReload)
    {
        $this->noReload = $noReload;
        return $this;
    }

    public function getNoReload()
    {
        return $this->noReload;
    }

    /**
     * Sets the detailed errors array from model errors
     * @param string[] $errors
     * @return static   Return self instance to allow method chaining
     */
    public function setDetailedErrors($errors)
    {
        $this->detailedErrors = $errors;
        return $this;
    }

    /**
     * Sets the detailed errors array from model errors
     * @param CModel $model
     * @return static   Return self instance to allow method chaining
     */
    public function setDetailedErrorsFromModel($model)
    {
        $errors = [];
        foreach ($model->getErrors() as $attributeErrors) {
            $errors = array_merge($errors, $attributeErrors);
        }
        $this->detailedErrors = $errors;
        return $this;
    }

    /**
     * Returns the detailed errors array
     * @return string[]
     */
    public function getDetailedErrors()
    {
        return $this->detailedErrors;
    }

    public function getDetailedErrorSummary($header = '', $htmlOptions = [])
    {
        $content = '';
        foreach($this->getDetailedErrors() as $error)
        {
            if($error != '') {
                if (!isset($htmlOptions['encode']) || $htmlOptions['encode']) {
                    $error = CHtml::encode($error);
                }
                $content .= '<li>' . $error . "</li>\n";
            }
        }
        if($content !== '') {
            if(!isset($htmlOptions['class'])) {
                $htmlOptions['class'] = CHtml::$errorSummaryCss;
            }
            return CHtml::tag('div', $htmlOptions, $header . "<ul>\n$content</ul>");
        } else {
            return '';
        }
    }
}
