<?php

$access_token = filter_input(INPUT_GET, 'access_token');
$tracks = explode(',', filter_input(INPUT_GET, 'tracks'));

if (!$access_token && !$tracks) die();

$me_url = 'https://api.spotify.com/v1/me';
// get user profile first
$opts = array('http' =>
    array(
        'method'  => 'GET',
        'header'  => "Authorization: Bearer " . $access_token . "\r\n",
    )
);

$context = stream_context_create($opts);
$result = @file_get_contents($me_url, false, $context);
$me = json_decode($result);

// make playlist
$create_playlist = "https://api.spotify.com/v1/users/{$me->id}/playlists";

$playlist_data = array(
    'name' => 'Top 100 of 2015',
    'public' => true,
);

$opts = array('http' =>
    array(
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n"
                   . "Authorization: Bearer " . $access_token . "\r\n",
        'content' => addslashes(json_encode($playlist_data)),
    )
);
$context = stream_context_create($opts);

$result = file_get_contents($create_playlist, false, $context);
$playlist = json_decode($result);

var_dump($me, $result, $playlist);