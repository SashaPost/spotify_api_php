<?php

require 'vendor/autoload.php';

$session = new SpotifyWebAPI\Session(
    '861ef84774c6406d8a8e9bff16b83fec',
    '667825e0a76a4a17b9f403dc84a9bfed', // Normally the client secret, but this value can be omitted when using the PKCE flow
    'http://127.0.0.1:8000/'
);

$verifier = $session->generateCodeVerifier(); // Store this value somewhere, a session for example
$challenge = $session->generateCodeChallenge($verifier);
$state = $session->generateState();

$options = [
    'code_challenge' => $challenge,
    'scope' => [
        'playlist-read-private',
        'user-read-private',
    ],
    'state' => $state,
];

header('Location: ' . $session->getAuthorizeUrl($options));
die();