<?php

if (empty($_GET))
    die();

if (filter_input(INPUT_GET, 'code') == '') {
   // authorize
    $url = 'https://accounts.spotify.com/authorize';

    $params = array(
        'client_id' => SPOTIFY_CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => 'http://localhost:8080/lastfm-spotify/create-playlist.php',
        'scopes' => 'playlist-modify-private,playlist-modify-public',
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
        'redirect_uri' => 'http://localhost:8080/lastfm-spotify/create-playlist.php',
    );

    $postdata = http_build_query($params);

    $opts = array('http' =>
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

    var_dump($auth);
}