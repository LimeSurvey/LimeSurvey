<?php

namespace League\OAuth2\Client\Provider;

class GoogleUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->response['sub'];
    }

    /**
     * Get preferred display name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->response['name'];
    }

    /**
     * Get preferred first name.
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getResponseValue('given_name');
    }

    /**
     * Get preferred last name.
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getResponseValue('family_name');
    }

    /**
     * Get locale.
     *
     * @return string|null
     */
    public function getLocale()
    {
        return $this->getResponseValue('locale');
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getResponseValue('email');
    }

    /**
     * Get hosted domain.
     *
     * @return string|null
     */
    public function getHostedDomain()
    {
        return $this->getResponseValue('hd');
    }

    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->getResponseValue('picture');
    }

    /**
     * Get user data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    private function getResponseValue($key)
    {
        if (array_key_exists($key, $this->response)) {
            return $this->response[$key];
        }
        return null;
    }
}
