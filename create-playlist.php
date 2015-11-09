<?php
session_start();

$session_auth = isset($_SESSION['spotify_auth']) ? $_SESSION['spotify_auth'] : false;
$access_token = isset($session_auth->access_token) ? $session_auth->access_token : false;

$tracks = explode(',', filter_input(INPUT_GET, 'tracks'));

if (!$access_token && !$tracks) die('No session.');

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

$opts = array(
    'http' =>
    array(
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n"
                   . "Authorization: Bearer " . $access_token . "\r\n",
        'content' => json_encode($playlist_data),
    )
);
$context = stream_context_create($opts);

$result = file_get_contents($create_playlist, false, $context);
$playlist = json_decode($result);

if (!isset($playlist->id)) {
    exit;
}

$track_listing = array();
foreach ($tracks as $track) {
    if (!$track) continue;
    $track_listing[] = "spotify:track:{$track}";
}

$track_data = array(
    'uris' => $track_listing,
);

$playlist_endpoint = "https://api.spotify.com/v1/users/{$me->id}/playlists/{$playlist->id}/tracks";
$opts = array(
    'http' =>
        array(
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n"
                . "Authorization: Bearer " . $access_token . "\r\n",
            'content' => json_encode($track_data),
        )
);
$context = stream_context_create($opts);

$result = file_get_contents($playlist_endpoint, false, $context);
$updated_playlist = json_decode($result);
