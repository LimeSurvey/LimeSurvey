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
                self::$noCsrfValidationRoutes[1]
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
                self::$noCsrfValidationRoutes[2]
            );

            $this->assertSame(
                1,
                $skipValidation,
                'CSRF validation should be skipped since the route ' . $route . ' is a plugins/unsecure route.'
            );
        }
    }

    /**
     * Testing that similar routes don't skip CSRF validation.
     */
    public function testSimilarRoutesDoNotSkipCsrfValidation()
    {
        $routes = array(
            'admin/menus/sa/restore',
            'admin/remote/action',
            'plugins/settings',
            '/admin/menus/sa/restore',
            '/admin/remote/action',
            '/plugins/settings',
        );

        // Asserting that a restlike route doesn't skip validation.
        $restRouteValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $routes[0],
            self::$noCsrfValidationRoutes[0]
        );

        $this->assertSame(
            0,
            $restRouteValidation,
            'CSRF validation should not be skipped since the route ' . $routes[0] . ' is not a rest route.'
        );

        // Asserting that a remotecontrol-like route doesn't skip validation.
        $remoteControlRouteValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $routes[1],
            self::$noCsrfValidationRoutes[1]
        );

        $this->assertSame(
            0,
            $remoteControlRouteValidation,
            'CSRF validation should not be skipped since the route ' . $routes[1] . ' is not a remote control route.'
        );

        // Asserting that a plugins/unsecure-like route doesn't skip validation.
        $pluginUnsecureRouteValidation = \LSHttpRequest::routeMatchesNoCsrfValidationRule(
            $routes[2],
            self::$noCsrfValidationRoutes[2]
        );

        $this->assertSame(
            0,
            $pluginUnsecureRouteValidation,
            'CSRF validation should not be skipped since the route ' . $routes[2] . ' is not a remote control route.'
        );
    }
}
