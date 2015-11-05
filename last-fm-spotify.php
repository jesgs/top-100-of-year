<?php
require 'includes/_header.php';

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
<div class="jumbotron">
    <ol>
    <?php foreach ($lastfm_json['toptracks']['track'] as $track) :
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

        $track['image'] = array_reverse($track['image']); ?>
        <li class="media">
            <div class="media-left">
                <img class="media-object" src="<?php echo $track['image'][2]['#text']; ?>" alt="">
            </div>
            <div class="media-body">
                <h4 class="media-title">
                    <?php echo $track['name'] ?> &mdash; <?php echo $track['artist']['name']; ?>
                </h4>
                <ul>
                    <li>Rank: <?php echo intval($track['@attr']['rank']); ?></li>
                    <li>Playcount: <?php echo intval($track['playcount']); ?></li>
                </ul>
            </div>
        </li>
    <?php endforeach; ?>
    </ol>
    <a href="create-playlist.php?tracks=<?php echo urlencode(implode(',', $spotify_track_ids)); ?>">Create Playlist</a>
</div>

<?php require 'includes/_footer.php';