<?php

namespace ls\tests;

class DummyController extends \CController
{
    /**
     * @var string
     */
    public $sTemplate = 'dummyvalue';

    /**
     * Contains info of last method called.
     *
     * @var array<string, array>
     */
    public $lastAction = [];

    /**
     * Do nothing.
     */
    public function redirect($url, $terminate = true, $statusCode = 302)
    {
        $this->lastAction = [
            'redirect' => [
                'url'        => $url,
                'terminate'  => $terminate,
                'statusCode' => $statusCode
            ]
        ];
    }
}
