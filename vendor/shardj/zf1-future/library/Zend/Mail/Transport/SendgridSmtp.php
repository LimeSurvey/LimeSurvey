<?php

class Zend_Mail_Transport_SendgridSmtp extends Zend_Mail_Transport_Smtp
{
    /**
     * ClickTracking status
     *
     * @var bool
     */
    protected $clickTracking = false;

    /**
     * API Options
     *
     * @var array
     */
    private $apiOptions = [];

    public function __construct($options)
    {
        if (!isset($options['host'])) {
            throw new Zend_Application_Resource_Exception(
                'A host is necessary for smtp transport,'
                . ' but none was given'
            );
        }
        $this->setClickTracking($options['clicktrack'] ?? false);

        $options['port'] = $options['port'] ?? 465;
        $options['username'] = $options['apikey'] ?? $options['username'];
        $options['ssl'] = true;
        parent::__construct($options);
    }

    /**
     * Set ClickTracking status
     *
     * @param bool $status
     * @return void
     */ 
    public function setClickTracking($bool)
    {
        $this->clickTracking = $bool;

        $overrideOptions = [
            "filters" => [
                "clicktrack" => [
                    "settings" => [
                        "enable" => $this->clickTracking,
                        "enable_text" => $this->clickTracking,
                    ]
                ]
            ]
        ];

        $this->apiOptions = array_merge_recursive($this->apiOptions, $overrideOptions);
    }

    /**
     * Get ClickTracking status
     *
     * @return bool
     */ 
    public function getClickTracking()
    {
        return $this->clickTracking;
    }

    /**
     * Set API option
     *
     * @param   string $key
     * @param   mixed $value
     */ 
    public function setOption($key, $value)
    {
        $this->apiOptions[$key] = $value;
    }

    /**
     * Get API options
     *
     * @return array
     */ 
    public function getOptions()
    {
        return $this->apiOptions;
    }

    /**
     * Set API options
     *
     * @param   array $options
     * @return  void
     */ 
    public function setOptions(array $options)
    {
        $this->apiOptions = $options;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareHeaders($headers)
    {
        $headers['X-SMTPAPI'] = Zend_Json::encode($this->apiOptions);
        parent::_prepareHeaders($headers);
    }
}
