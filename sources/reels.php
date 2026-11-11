<?php
if (!isset($wo['config']['reels_upload'])) {
    $wo['config']['reels_upload'] = 1;
}
if (!isset($wo['config']['have_reels'])) {
    $wo['config']['have_reels'] = 1;
}
$wo['config']['second_post_button'] = 'disabled';
$wo['watched_reels'] = array();
$html = '';
$main_reel = 0;

$reelOwnerName = '';
$getPosts = false;
$postsData = array(
    'limit' => 4,
    'filter_by' => 'local_video',
    'order' => 'rand',
    'is_reel' => 'only'
);

if (!empty($_GET['user'])) {
    $reelOwnerName = Wo_Secure($_GET['user']);
    $reelOwnerId = Wo_UserIdFromUsername(Wo_Secure($_GET['user']));
    $postsData['publisher_id'] = $reelOwnerId;
    $getPosts = true;
}

if (empty($_GET['id'])) {
    $getPosts = true;
}

if (!empty($wo['watched_reels']) && empty($_GET['id'])) {
    $postsData['not_in'] = $wo['watched_reels'];
}

$reels = [];

if ($getPosts) {
    $reels = Wo_GetPosts($postsData);
    if (empty($reels)) {
        setcookie('watched_reels', json_encode(array()), time()+(60 * 60 * 24),'/');
        $wo['watched_reels'] = array();
        $postsData['not_in'] = $wo['watched_reels'];
        $reels = Wo_GetPosts($postsData);
    }
    $id = !empty($reels[0]['id']) ? $reels[0]['id'] : 0;
}
if (!empty($_GET['id'])) {
    $id = Wo_Secure($_GET['id']);

    $wo['story'] = Wo_PostData($id);

    if (empty($wo['story'])) {
        header("Location: " . $wo['config']['site_url']);
        exit();
    }

    $reels[] = $wo['story'];

    setcookie('watched_reels', json_encode(array()), time()+(60 * 60 * 24),'/');
    $wo['watched_reels'] = array($wo['story']['id']);
    $postsData['not_in'] = $wo['watched_reels'];
    $nextReels = Wo_GetPosts($postsData);
    if (!empty($nextReels)) {
        $reels = array_merge($reels, $nextReels);
    }
    
}

$wo['page_url'] = $wo['config']['site_url']. "/reels" . (!empty($id) ? "/" . $id : '');
if (!empty($reelOwnerName)) {
    $wo['page_url'] .= "/".$reelOwnerName;
}
$wo['reelOwnerName'] = $reelOwnerName;
$wo['page'] = 'reels';

if (!empty($reels) && is_array($reels)) {
    foreach ($reels as $key => $wo['story']) {
        if (empty($wo['story']['id'])) {
            continue;
        }
        if (!in_array($wo['story']['id'], $wo['watched_reels'])) {
            $wo['watched_reels'][] = $wo['story']['id'];
        }
        $wo['story']['likeCount'] = Wo_CountLikes($wo['story']['id']);
        $wo['story']['commentCount'] = Wo_CountPostComment($wo['story']['id']);

        $media = array(
            'type' => 'post',
            'storyId' => $wo['story']['id'],
            'filename' => !empty($wo['story']['postFile']) ? $wo['story']['postFile'] : '',
            'name' => !empty($wo['story']['postFileName']) ? $wo['story']['postFileName'] : '',
            'postFileThumb' => !empty($wo['story']['postFileThumb']) ? $wo['story']['postFileThumb'] : '',
        );
        $video = Wo_DisplaySharedFile($media, '', !empty($wo['story']['cache']) ? $wo['story']['cache'] : '');

        $userUrl = '';
        if (!empty($reelOwnerName)) {
            $userUrl = "/".$reelOwnerName;
        }
        $wo['reel_class'] = ($main_reel == 0 ? '' : 'hidden');
        $wo['reel_url'] = $wo['config']['site_url']. "/reels/" . $wo['story']['id'].$userUrl;
        $wo['reel_video'] = $video;
        $html .= Wo_LoadPage('reels/list');
        $main_reel = 1;
    }
}

if (!empty($wo['watched_reels'])) {
    setcookie('watched_reels', json_encode($wo['watched_reels']), time()+(60 * 60 * 24),'/');
}

if (empty($html)) {
    $html = '<div class="empty_state">' . (!empty($wo['lang']['no_videos_found']) ? $wo['lang']['no_videos_found'] : 'No reels found') . '</div>';
}

$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords'] = $wo['config']['siteKeywords'];
$wo['title'] = !empty($wo['lang']['reels']) ? $wo['lang']['reels'] : 'Reels';
$wo['content'] = str_replace('{{html}}', $html, Wo_LoadPage('reels/content'));
