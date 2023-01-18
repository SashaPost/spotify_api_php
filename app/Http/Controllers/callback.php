<?php


require 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    '861ef84774c6406d8a8e9bff16b83fec',
    '667825e0a76a4a17b9f403dc84a9bfed',
    'http://127.0.0.1:8000/'
);

$state = $_GET['state'];

// Fetch the stored state value from somewhere. A session for example

if ($state !== $storedState) {
    // The state returned isn't the same as the one we've stored, we shouldn't continue
    die('State mismatch');
}

// Request a access token using the code from Spotify and the previously created code verifier
$session->requestAccessToken($_GET['code'], $verifier);

$accessToken = $session->getAccessToken();
$refreshToken = $session->getRefreshToken();

// Store the access and refresh tokens somewhere. In a session for example

// Send the user along and fetch some data!
header('Location: app.php');
die();