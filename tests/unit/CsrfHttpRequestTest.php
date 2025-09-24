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

    /**
     * Test CSRF token validation
     * @dataProvider csrfValidationDataProvider
     */
    public function testCsrfTokenValidation($cookieValue, $shouldPass, $shouldBeSanitized)
    {
        $request = new \LSHttpRequest();
        $tokenName = $request->csrfTokenName;
        // Keep the original state
        $originalRequestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $originalPostToken = $_POST[$tokenName] ?? null;
        $originalCsrfCookie = $request->getCookies()->itemAt($tokenName);

        // If the cookie value is null, unset the cookie
        if (!isset($cookieValue)) {
            $request->getCookies()->remove($tokenName);
        } else {
            // Set the CSRF cookie to the provided value
            $request->getCookies()->add($tokenName, new \CHttpCookie($tokenName, $cookieValue));
        }

        // Get the CSRF token from the request
        // This should pick the value from the cookie or create a new one if the cookie is not set
        $tokenValue = $request->getCsrfToken();

        // Check sanitization. If sanitization was expected, the sanitized value should be different than the original
        if (isset($cookieValue)) {
            $this->assertEquals(
                $shouldBeSanitized,
                $tokenValue !== $cookieValue,
                'The CSRF token should ' . ($shouldBeSanitized ? '' : 'not ') . 'be sanitized.'
            );
        }

        // Simulate the POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[$tokenName] = $tokenValue;
        // Run the validation
        $validationPassed = false;
        try {
            $request->validateCsrfToken(new \CEvent());
            $validationPassed = true;
        } catch (\Exception $e) {
        }

        // Restore the original state
        $_SERVER['REQUEST_METHOD'] = $originalRequestMethod;
        $_POST[$tokenName] = $originalPostToken;
        if (!isset($originalCsrfCookie)) {
            $request->getCookies()->remove($tokenName);
        } else {
            $request->getCookies()->add($tokenName, $originalCsrfCookie);
        }

        $this->assertEquals($validationPassed, $shouldPass, 'CSRF validation should ' . ($shouldPass ? '' : 'not ') . 'pass.');
    }

    /**
     * Provides test data for the testCsrfTokenValidation method.
     * Returns an array of arrays in the form [cookieValue, shouldPass, shouldBeSanitized]
     */
    public function csrfValidationDataProvider()
    {
        $testData = [
            // Basic case (no cookie present, new token created)
            [
                null, // Cookie value
                true, // Should pass
                false // Should not be sanitized
            ]
        ];

        $securityManager = \Yii::app()->getSecurityManager();
        // Generate 20 random valid CSRF tokens
        $validTokens = [];
        for ($i = 0; $i < 20; $i++) {
            $token = $securityManager->generateRandomBytes(32);
            $maskedToken = $securityManager->maskToken($token);
            $validTokens[] = $maskedToken;
            $testData[] = [
                $maskedToken, // Cookie value
                true, // Should pass
                false // Should not be sanitized
            ];
        }

        // Make sure we test values with underscores and hyphens
        $testData[] = [
            'valid_token-with-mixed-characters123',
            true,
            false // Should not be sanitized
        ];

        // Add some invalid tokens
        $testData[] = [
            $validTokens[0] . '!',
            true, // Should pass after sanitization
            true // Should be sanitized
        ];
        $testData[] = [
            $validTokens[1] . '$',
            true, // Should pass after sanitization
            true // Should be sanitized
        ];
        $testData[] = [
            $validTokens[2] . '<script>',
            true, // Should pass after sanitization
            true // Should be sanitized
        ];
        // Add a token that becomes empty after sanitization
        $testData[] = [
            '<>!@#$', // Invalid token that will be sanitized to an empty string
            true,    // Should pass because a new token will be generated
            true     // Should be sanitized
        ];

        return $testData;
    }
}
