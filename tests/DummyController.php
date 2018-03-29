<?php

namespace ls\tests;

class DummyController extends \CController
{
    /**
     * @var string
     */
    public $sTemplate = 'dummyvalue';

    /**
     * Do nothing.
     */
    public function redirect($url, $terminate = true, $statusCode = 302)
    {
    }
}
