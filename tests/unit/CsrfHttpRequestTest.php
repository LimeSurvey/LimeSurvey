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
            '/rest/v1/actionOnItemById/25',
            '/rest/v1/action',
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
            'admin/remotecontrol/actionOnItemById/15',
            'admin/remotecontrol/action',
            '/admin/remotecontrol/actionOnItemById/25',
            '/admin/remotecontrol',
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
            '/plugins/unsecure/action',
            '/plugins/unsecure/actionOnItemById/25',
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
        $routes = array(
            'admin/menus/sa/restore',
            '/admin/menus/sa/restore'
        );

        foreach ($routes as $route) {
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
    }

    /**
     * Testing that remote-like routes don't skip CSRF validation.
     */
    public function testRemoteLikeRoutesDoNotSkipCsrfValidation()
    {
        $routes = array(
            'remote/action',
            '/remote/action'
        );

        foreach ($routes as $route) {
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
    }

    /**
     * Testing that plugins/unsecure-like routes don't skip CSRF validation.
     */
    public function testPluginUnsecureLikeRoutesDoNotSkipCsrfValidation()
    {
        $routes = array(
            'plugins/settings',
            '/plugins/settings'
        );

        foreach ($routes as $route) {
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
}
