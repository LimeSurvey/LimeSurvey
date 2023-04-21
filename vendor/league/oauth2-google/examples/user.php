<?php

$provider = require __DIR__ . '/provider.php';

if (isset($_GET['logout']) && 1 == $_GET['logout']) {
    unset($_SESSION['token']);
}

if (!empty($_SESSION['token'])) {
    $token = unserialize($_SESSION['token']);
}

if (empty($token)) {
    header('Location: /');
    exit;
}

try {
    // We got an access token, let's now get the user's details
    $userDetails = $provider->getResourceOwner($token);

    // Use these details to create a new profile
    printf('Hello %s!<br/>', $userDetails->getFirstname());
} catch (Exception $e) {
    // Failed to get user details
    exit('Something went wrong: ' . $e->getMessage());
}

// Use this to interact with an API on the users behalf
echo "Token is: <tt>", $token->getToken(), "</tt><br/>";

// Use this to get a new access token if the old one expires
echo "Refresh token is: <tt>", $token->getRefreshToken(), "</tt><br/>";

// Number of seconds until the access token will expire, and need refreshing
echo "Expires at ", date('r', $token->getExpires()), "<br/>";

// Allow the user to logout
echo '<a href="?logout=1">Logout</a><br/>';
