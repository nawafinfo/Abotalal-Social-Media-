<?php
$uri = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$uri = $uri ? urldecode($uri) : '/';
$file = __DIR__ . $uri;
if ($uri !== '/' && $uri !== '' && is_file($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $media_mimes = array(
        'mp4' => 'video/mp4',
        'm4v' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'ogv' => 'video/ogg',
        'mov' => 'video/quicktime',
        'mkv' => 'video/x-matroska',
        'avi' => 'video/x-msvideo',
        'flv' => 'video/x-flv',
        'mpeg' => 'video/mpeg',
        'mpg' => 'video/mpeg',
        '3gp' => 'video/3gpp',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'm4a' => 'audio/mp4'
    );
    if (isset($media_mimes[$ext])) {
        $size = filesize($file);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $range)) {
            if ($range[1] !== '') {
                $start = (int) $range[1];
            }
            if ($range[2] !== '') {
                $end = (int) $range[2];
            }
            if ($end >= $size) {
                $end = $size - 1;
            }
            if ($start > $end || $start < 0) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header('Content-Range: bytes */' . $size);
                return true;
            }
            $status = 206;
        }
        $length = $end - $start + 1;
        if ($status === 206) {
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
        } else {
            header('HTTP/1.1 200 OK');
        }
        header('Content-Type: ' . $media_mimes[$ext]);
        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $length);
        header('Cache-Control: public, max-age=31536000');
        header('Content-Disposition: inline; filename="' . basename($file) . '"');
        $fp = fopen($file, 'rb');
        if ($fp) {
            fseek($fp, $start);
            $left = $length;
            while ($left > 0 && !feof($fp) && !connection_aborted()) {
                $chunk = ($left > 8192) ? 8192 : $left;
                echo fread($fp, $chunk);
                $left -= $chunk;
                flush();
            }
            fclose($fp);
        }
        return true;
    }
    return false;
}

$path = trim($uri, '/');
if ($path === '' || $path === 'index.php') {
    $_GET['link1'] = empty($_GET['link1']) ? 'home' : $_GET['link1'];
    require __DIR__ . '/index.php';
    return true;
}

$php_files = array(
    'requests.php' => 'requests.php',
    '_' => 'requests.php',
    'api' => 'api-v2.php',
    'admin-cp' => 'admincp.php',
    'admincp' => 'admincp.php',
    'admin-panel' => 'admincp.php',
    'update.php' => 'update.php',
    'updater.php' => 'updater.php',
    'login-with.php' => 'login-with.php',
);

if (isset($php_files[$path])) {
    require __DIR__ . '/' . $php_files[$path];
    return true;
}

if (preg_match('#^adminPages/(.*)$#', $path, $m)) {
    $target = __DIR__ . '/admin-panel/' . $m[1];
    if (is_file($target)) {
        return false;
    }
}

if (preg_match('#^admin-cp/(.*)$#', $path, $m) || preg_match('#^admincp/(.*)$#', $path, $m)) {
    $_GET['page'] = $m[1];
    require __DIR__ . '/admincp.php';
    return true;
}

if (preg_match('#^api/([^/]+)/?$#', $path, $m)) {
    $_GET['type'] = $m[1];
    require __DIR__ . '/api-v2.php';
    return true;
}

$rules = array(
    '#^password-reset/([^/]+)/?$#' => array('link1' => 'welcome', 'link2' => 'password_reset', 'user_id' => 1),
    '#^saved-posts/?$#' => array('link1' => 'saved-posts'),
    '#^start-up/?$#' => array('link1' => 'start-up'),
    '#^unusual-login/?$#' => array('link1' => 'unusual-login'),
    '#^activated/([^/]+)/?$#' => array('link1' => 'activate', 'link2' => 1),
    '#^search/([^/]+)/?$#' => array('link1' => 'search', 'query' => 1),
    '#^search/?$#' => array('link1' => 'search'),
    '#^app/([^/]+)/?$#' => array('link1' => 'app', 'app_id' => 1),
    '#^messages/([^/]+)/?$#' => array('link1' => 'messages', 'user' => 1),
    '#^messages/?$#' => array('link1' => 'messages'),
    '#^terms/([^/]+)/?$#' => array('link1' => 'terms', 'type' => 1),
    '#^video-call/([^/]+)/?$#' => array('link1' => 'video-call', 'call_id' => 1),
    '#^video-call-api/([^/]+)/?$#' => array('link1' => 'video-call-api', 'call_id' => 1),
    '#^post/([^/]+)/?$#' => array('link1' => 'post', 'id' => 1),
    '#^game/([^/]+)/?$#' => array('link1' => 'game', 'id' => 1),
    '#^hashtag/([^/]+)/?$#' => array('link1' => 'hashtag', 'hash' => 1),
    '#^follow-requests/?$#' => array('link1' => 'follow-requests'),
    '#^p/([^/]+)/?$#' => array('link1' => 'page', 'p' => 1),
    '#^g/([^/]+)/?$#' => array('link1' => 'group', 'g' => 1),
    '#^welcome.*$#' => array('link1' => 'welcome'),
    '#^register/?$#' => array('link1' => 'register'),
    '#^login/?$#' => array('link1' => 'welcome'),
    '#^confirm-sms-password/?$#' => array('link1' => 'confirm-sms-password'),
    '#^confirm-sms/?$#' => array('link1' => 'confirm-sms'),
    '#^forgot-password/?$#' => array('link1' => 'forgot-password'),
    '#^activate/?$#' => array('link1' => 'activate'),
    '#^liked-pages/?$#' => array('link1' => 'liked-pages'),
    '#^suggested-pages/?$#' => array('link1' => 'suggested-pages'),
    '#^pages/?$#' => array('link1' => 'pages'),
    '#^joined_groups/?$#' => array('link1' => 'joined_groups'),
    '#^suggested-groups/?$#' => array('link1' => 'suggested-groups'),
    '#^create-group/?$#' => array('link1' => 'create-group'),
    '#^groups/?$#' => array('link1' => 'groups'),
    '#^create-page/?$#' => array('link1' => 'create-page'),
    '#^logout/?$#' => array('link1' => 'logout'),
    '#^contact-us/?$#' => array('link1' => 'contact-us'),
    '#^setting/([A-Za-z0-9_]+)/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'setting', 'user' => 1, 'page' => 2),
    '#^setting/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'setting', 'page' => 1),
    '#^setting/?$#' => array('link1' => 'setting'),
    '#^page-setting/([A-Za-z0-9_]+)/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'page-setting', 'page' => 1, 'link3' => 2),
    '#^page-setting/([^/]+)/?$#' => array('link1' => 'page-setting', 'page' => 1),
    '#^group-setting/([A-Za-z0-9_]+)/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'group-setting', 'group' => 1, 'link3' => 2),
    '#^group-setting/([^/]+)/?$#' => array('link1' => 'group-setting', 'group' => 1),
    '#^new-product/?$#' => array('link1' => 'new-product'),
    '#^edit-product/([A-Za-z0-9_]+)/?$#' => array('link1' => 'edit-product', 'id' => 1),
    '#^products/([A-Za-z0-9_-]+)/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'products', 'c_id' => 1, 'sub_id' => 2),
    '#^products/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'products', 'c_id' => 1),
    '#^products/?$#' => array('link1' => 'products'),
    '#^my-products/?$#' => array('link1' => 'my-products'),
    '#^site-pages/(.*)$#' => array('link1' => 'site-pages', 'page_name' => 1),
    '#^blogs/?$#' => array('link1' => 'blogs'),
    '#^sharer/?$#' => array('link1' => 'sharer'),
    '#^blog-category/(\d+)/?$#' => array('link1' => 'blog-category', 'id' => 1),
    '#^create-blog/?$#' => array('link1' => 'create-blog'),
    '#^edit-blog/(\d+)/?$#' => array('link1' => 'edit-blog', 'id' => 1),
    '#^my-blogs/?$#' => array('link1' => 'my-blogs'),
    '#^read-blog/(.*)$#' => array('link1' => 'read-blog', 'id' => 1),
    '#^albums/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'albums', 'user' => 1),
    '#^albums/?$#' => array('link1' => 'albums'),
    '#^create-album/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'create-album', 'album' => 1),
    '#^create-album/?$#' => array('link1' => 'create-album'),
    '#^album/([A-Za-z0-9_-]+)/?$#' => array('link1' => 'album', 'id' => 1),
    '#^upgraded/?$#' => array('link1' => 'upgraded'),
    '#^get_news_feed/?$#' => array('link1' => 'get_news_feed'),
    '#^new-game/?$#' => array('link1' => 'new-game'),
    '#^games/?$#' => array('link1' => 'games'),
    '#^go-pro/?$#' => array('link1' => 'go-pro'),
    '#^oops/?$#' => array('link1' => 'oops'),
    '#^user-activation/?$#' => array('link1' => 'user-activation'),
    '#^home/?$#' => array('link1' => 'home'),
    '#^404/?$#' => array('link1' => '404'),
    '#^app_api/?$#' => array('link1' => 'app_api'),
    '#^api_request/?$#' => array('link1' => 'app_api'),
    '#^authorize/?$#' => array('link1' => 'authorize'),
    '#^poke/?$#' => array('link1' => 'poke'),
    '#^most_liked/?$#' => array('link1' => 'most_liked'),
    '#^jobs/?$#' => array('link1' => 'jobs'),
    '#^common_things/?$#' => array('link1' => 'common_things'),
    '#^funding/?$#' => array('link1' => 'funding'),
    '#^my_funding/?$#' => array('link1' => 'my_funding'),
    '#^create_funding/?$#' => array('link1' => 'create_funding'),
    '#^edit_fund/(.*)$#' => array('link1' => 'edit_fund', 'id' => 1),
    '#^show_fund/(.*)$#' => array('link1' => 'show_fund', 'id' => 1),
    '#^live/?$#' => array('link1' => 'live'),
    '#^reels/?$#' => array('link1' => 'reels'),
    '#^memories/?$#' => array('link1' => 'memories'),
    '#^offers/?$#' => array('link1' => 'offers'),
    '#^nearby_shops/?$#' => array('link1' => 'nearby_shops'),
    '#^nearby_business/?$#' => array('link1' => 'nearby_business'),
    '#^refund/?$#' => array('link1' => 'refund'),
    '#^advertise/?$#' => array('link1' => 'advertise'),
    '#^forum/members/([a-zA-Z]{0,1})/?$#' => array('link1' => 'forum-members-byname', 'char' => 1),
    '#^forum/members/?$#' => array('link1' => 'forum-members'),
    '#^forum/search-result/?$#' => array('link1' => 'forum-search-result'),
    '#^forum/search/?$#' => array('link1' => 'forum-search'),
    '#^forum/events/?$#' => array('link1' => 'forum-events'),
    '#^forum/help/?$#' => array('link1' => 'forum-help'),
    '#^forum/?$#' => array('link1' => 'forum'),
    '#^forums/add/(\d+)/?$#' => array('link1' => 'forumaddthred', 'fid' => 1),
    '#^forums/thread/reply/(\d+)/?$#' => array('link1' => 'threadreply', 'tid' => 1),
    '#^forums/thread/quote/(\d+)/?$#' => array('link1' => 'threadquote', 'tid' => 1),
    '#^forums/thread/edit/(\d+)/?$#' => array('link1' => 'editreply', 'tid' => 1),
    '#^forums/thread/(\d+)/?$#' => array('link1' => 'showthread', 'tid' => 1),
    '#^forums/user/threads/edit/(\d+)/?$#' => array('link1' => 'edithread', 'tid' => 1),
    '#^forums/user/threads/?$#' => array('link1' => 'mythreads'),
    '#^forums/user/messages/?$#' => array('link1' => 'mymessages'),
    '#^forums/(\d+)/?$#' => array('link1' => 'forums', 'fid' => 1),
    '#^create-event/?$#' => array('link1' => 'create-event'),
    '#^events/create-event/?$#' => array('link1' => 'create-event'),
    '#^notifications/?$#' => array('link1' => 'notifications'),
    '#^events/edit/(\d+)/?$#' => array('link1' => 'edit-event', 'eid' => 1),
    '#^events/my/?$#' => array('link1' => 'my-events'),
    '#^events/going/?$#' => array('link1' => 'events-going'),
    '#^events/invited/?$#' => array('link1' => 'events-invited'),
    '#^events/interested/?$#' => array('link1' => 'events-interested'),
    '#^events/past/?$#' => array('link1' => 'events-past'),
    '#^events/(\d+)/?$#' => array('link1' => 'show-event', 'eid' => 1),
    '#^events/?$#' => array('link1' => 'events'),
    '#^movies/genre/([A-Za-z-]+)/?$#' => array('link1' => 'movies-genre', 'genre' => 1),
    '#^movies/country/([A-Za-z-]+)/?$#' => array('link1' => 'movies-country', 'country' => 1),
    '#^movies/watch/(\d+)/?$#' => array('link1' => 'watch-film', 'film-id' => 1),
    '#^movies/?$#' => array('link1' => 'movies'),
    '#^create-ads/?$#' => array('link1' => 'create-ads'),
    '#^ads/create/?$#' => array('link1' => 'create-ads'),
    '#^ads/edit/(\d+)/?$#' => array('link1' => 'edit-ads', 'id' => 1),
    '#^ads/chart/(\d+)/?$#' => array('link1' => 'chart-ads', 'id' => 1),
    '#^ads/?$#' => array('link1' => 'ads'),
    '#^wallet/?$#' => array('link1' => 'wallet'),
    '#^send_money/?$#' => array('link1' => 'send_money'),
    '#^admin/ads/edit/(\d+)/?$#' => array('link1' => 'manage-ads', 'id' => 1),
    '#^status/create/?$#' => array('link1' => 'create-status'),
    '#^more-status/?$#' => array('link1' => 'more-status'),
    '#^friends-nearby/?$#' => array('link1' => 'friends-nearby'),
    '#^graph-success/?$#' => array('link1' => 'graph-success'),
    '#^developers/?$#' => array('link1' => 'developers'),
    '#^apps/?$#' => array('link1' => 'apps'),
    '#^create-app/?$#' => array('link1' => 'create-app'),
    '#^graph/?$#' => array('link1' => 'graph'),
    '#^oauth/?$#' => array('link1' => 'oauth'),
    '#^boosted-pages/?$#' => array('link1' => 'boosted-pages'),
    '#^boosted-posts/?$#' => array('link1' => 'boosted-posts'),
    '#^ads-create/?$#' => array('link1' => 'ads-create'),
);

foreach ($rules as $pattern => $map) {
    if (preg_match($pattern, $path, $m)) {
        foreach ($map as $key => $value) {
            if (is_int($value)) {
                $_GET[$key] = isset($m[$value]) ? $m[$value] : '';
            } else {
                $_GET[$key] = $value;
            }
        }
        require __DIR__ . '/index.php';
        return true;
    }
}

if (preg_match('#^@([^/]+)/?$#', $path, $m)) {
    $_GET['link1'] = 'timeline';
    $_GET['u'] = $m[1];
    require __DIR__ . '/index.php';
    return true;
}

if (preg_match('#^([A-Za-z0-9_]+)/([^/]+)/?$#', $path, $m)) {
    $_GET['link1'] = 'timeline';
    $_GET['u'] = $m[1];
    $_GET['type'] = $m[2];
    require __DIR__ . '/index.php';
    return true;
}

$_GET['link1'] = 'timeline';
$_GET['u'] = $path;
require __DIR__ . '/index.php';
return true;
