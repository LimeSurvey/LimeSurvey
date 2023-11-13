<?php

namespace ls\tests;

class CsrfHttpRequestTest extends TestBaseClass
{
    public static $noCsrfValidationRoutes;

    public static function setUpBeforeClass(): void
    {
        self::$noCsrfValidationRoutes = \Yii::app()->request->noCsrfValidationRoutes;
    }

    /**
     * Testing that rest routes skip CSRF validation.
     */
    public function testRestRoutesSkipCsrfValidation()
    {
        // This test is skipped since there are no rest routes on v5, hence not expected to skip CSRF validation.
        $this->markTestSkipped();
        $routes = array(
            'rest/v1/actionOnItemById/15',
            'rest/v1/action',
        );

        foreach ($routes as $route) {
            $skipValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
                $route,
                self::$noCsrfValidationRoutes[0]
            );

            $this->assertSame(
                1,
                $skipValidation,
                'CSRF validation should be skipped since the route ' . $route . ' is a rest route.'
            );
        }
    }

    /**
     * Testing that remotecontrol routes skip CSRF validation.
     */
    public function testRemoteControlRoutesSkipCsrfValidation()
    {
        $routes = array(
            'remotecontrol/actionOnItemById/15',
            'remotecontrol/action',
        );

        foreach ($routes as $route) {
            $skipValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
                $route,
                self::$noCsrfValidationRoutes[0]
            );

            $this->assertSame(
                1,
                $skipValidation,
                'CSRF validation should be skipped since the route ' . $route . ' is a remote control route.'
            );
        }
    }

    /**
     * Testing that plugins/unsecure routes skip CSRF validation.
     */
    public function testPluginsUnsecureRoutesSkipCsrfValidation()
    {
        $routes = array(
            'plugins/unsecure/action',
            'plugins/unsecure/actionOnItemById/15',
        );

        foreach ($routes as $route) {
            $skipValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
                $route,
                self::$noCsrfValidationRoutes[1]
            );

            $this->assertSame(
                1,
                $skipValidation,
                'CSRF validation should be skipped since the route ' . $route . ' is a plugins/unsecure route.'
            );
        }
    }

    /**
     * Testing that rest-like routes don't skip CSRF validation.
     */
    public function testRestLikeRoutesDoNotSkipCsrfValidation()
    {
        // This test is skipped since there are no rest routes on v5, hence not expected to skip CSRF validation.
        $this->markTestSkipped();
        $route = 'admin/menus/sa/restore';

        $routeValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $route,
            self::$noCsrfValidationRoutes[0]
        );

        $this->assertSame(
            0,
            $routeValidation,
            'CSRF validation should not be skipped since the route ' . $route . ' is not a rest route.'
        );
    }

    /**
     * Testing that remote-like routes don't skip CSRF validation.
     */
    public function testRemoteLikeRoutesDoNotSkipCsrfValidation()
    {
        $route = 'remote/action';

        $routeValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $route,
            self::$noCsrfValidationRoutes[0]
        );

        $this->assertSame(
            0,
            $routeValidation,
            'CSRF validation should not be skipped since the route ' . $route . ' is not a rest route.'
        );
    }

    /**
     * Testing that plugins/unsecure-like routes don't skip CSRF validation.
     */
    public function testPluginUnsecureLikeRoutesDoNotSkipCsrfValidation()
    {
        $route = 'plugins/settings';

        $routeValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $route,
            self::$noCsrfValidationRoutes[1]
        );

        $this->assertSame(
            0,
            $routeValidation,
            'CSRF validation should not be skipped since the route ' . $route . ' is not a remote control route.'
        );
    }
}
