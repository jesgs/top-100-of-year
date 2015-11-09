<?php
require 'includes/_header.php';

$session_auth = isset($_SESSION['spotify_auth']) ? $_SESSION['spotify_auth'] : false;
$lastfm_cache_file = 'cache/lastfm.json';

$lastfm_url = 'http://ws.audioscrobbler.com/2.0/?method=user.gettoptracks'
            . '&user=' . LASTFM_USER
            . '&api_key=' . LASTFM_API_KEY
            . '&limit=100' 
            . '&period=12month'
            . '&format=json';

$spotify_search_url = 'https://api.spotify.com/v1/search?q={query}&type=track,artist&market=US';

// cache
$contents = @file_get_contents($lastfm_cache_file);
if (!$contents) {    
    $contents = file_get_contents($lastfm_url);
    file_put_contents($lastfm_cache_file, $contents);
}
$lastfm_json = json_decode($contents, true); 
$spotify_track_ids = array();
?>
<div class="container-fluid">
    <div class="row">
        <?php foreach ($lastfm_json['toptracks']['track'] as $track) {
            $track_artist = $track['name'] . ' ' . $track['artist']['name'];
            $cache_file = 'track-' . strtolower(str_replace(' ', '-', $track_artist)) . '.json';

            $url_data =  urlencode($track_artist);
            $url = str_replace('{query}', $url_data, $spotify_search_url);

            // cache...again
            $data = @file_get_contents('cache/' . $cache_file);
            if (!$data) {
                $data = file_get_contents($url);
                file_put_contents('cache/' . $cache_file, $data);
            }

            $data = json_decode($data, true);

            if (isset($data['tracks']['items'][0])) {
                $spotify_track_ids[] = $data['tracks']['items'][0]['id'];
            } else {
                $spotify_track_ids[] = '';
            }

            $track['image'] = array_reverse($track['image']);
$track_html .= <<<TRACKHTML
            <li class="media">
                <div class="media-left">
                    <img class="media-object" src="{$track['image'][2]['#text']}" alt="">
                </div>
                <div class="media-body">
                    <h4 class="media-title">
                        {$track['name']} &mdash; {$track['artist']['name']}
                    </h4>
                    <ul>
                        <li>Rank: {$track['@attr']['rank']}</li>
                        <li>Playcount: {$track['playcount']}</li>
                    </ul>
                </div>
            </li>
TRACKHTML;
        }
        ?>
        <nav class="navbar navbar-default col-md-12">
            <div class="container-fluid">
                <div class="navbar-header">
                    <span class="navbar-brand">Top 100 of <?php echo date('Y');?></span>
                    <p>
                        Top 100 tracks for 12 month time-period ending <?php echo date('F jS, Y', filemtime($lastfm_cache_file)); ?>
                    </p>
                </div>
            <?php if (!isset($session_auth->access_token)) : ?>
                <a class="btn btn-primary navbar-btn" href="authorize.php">Authorize with Spotify</a>
            <?php else :?>
                <a class="btn btn-default navbar-btn" href="create-playlist.php?tracks=<?php echo urlencode(implode(',', $spotify_track_ids)); ?>">Create Playlist</a>
            <?php endif; ?>
            </div>
        </nav>

        <div class="col-md-12">
            <ol>
            <?php echo $track_html; ?>
            </ol>
        </div>
    </div>
</div>
<?php require 'includes/_footer.php';