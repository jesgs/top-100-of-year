<?php
require 'config.php';

if (filter_input(INPUT_GET, 'code') == '') {
   // authorize
    $url = 'https://accounts.spotify.com/authorize';

    $params = array(
        'client_id' => SPOTIFY_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => 'http://' . $_SERVER['HTTP_HOST'] . '/lastfm-spotify/authorize.php',
        'scope' => 'playlist-modify-public playlist-modify-private playlist-read-private user-read-email',
        'show_dialog' => true,
    );

    $auth_url = $url . '?' . http_build_query($params);
    header("Location: {$auth_url}");
}

if (filter_input(INPUT_GET, 'code') !== '') {
    $token_url = 'https://accounts.spotify.com/api/token';

    $params = array(
        'grant_type' => 'authorization_code',
        'code' => filter_input(INPUT_GET, 'code'),
        'redirect_uri' => 'http://' . $_SERVER['HTTP_HOST'] . '/lastfm-spotify/authorize.php',
    );

    $postdata = http_build_query($params);

    $opts = array(
        'http' =>
            array(
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"
                           . "Authorization: Basic " . base64_encode( SPOTIFY_CLIENT_ID . ':' . SPOTIFY_SECRET) . "\r\n",
                'content' => $postdata
            )
    );

    $context  = stream_context_create($opts);

    $result = @file_get_contents($token_url, false, $context);
    $auth = json_decode($result);

    if (!$auth) {
        die('Denied');
    }

    session_start();
    $_SESSION['spotify_auth'] = $auth;
    header('Location: last-fm-spotify.php');
}