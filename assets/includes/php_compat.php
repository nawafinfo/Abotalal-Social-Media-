<?php
if (!function_exists('decryptConfigData')) {
    function decryptConfigData() {
        return true;
    }
}
if (!function_exists('checkHTTPS')) {
    function checkHTTPS() {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        if (!empty($_SERVER['REQUEST_SCHEME']) && strtolower((string) $_SERVER['REQUEST_SCHEME']) === 'https') {
            return true;
        }
        return false;
    }
}
if (!function_exists('full_url')) {
    function full_url($server = array()) {
        $host = !empty($server['HTTP_HOST']) ? $server['HTTP_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
        $uri = !empty($server['REQUEST_URI']) ? $server['REQUEST_URI'] : (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/');
        return $host . $uri;
    }
}
if (!function_exists('each')) {
    function each(&$arr) {
        $key = key($arr);
        if ($key === null) {
            return false;
        }
        $val = current($arr);
        next($arr);
        return array(1 => $val, 'value' => $val, 0 => $key, 'key' => $key);
    }
}
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}
if (!function_exists('create_function')) {
    function create_function($args, $code) {
        return eval('return function(' . $args . ') {' . $code . '};');
    }
}
if (!function_exists('wo_implode_list')) {
    function wo_implode_list($list, $glue = ',') {
        if (is_array($list)) {
            return implode($glue, $list);
        }
        if (is_string($list) || is_numeric($list)) {
            return (string) $list;
        }
        return '';
    }
}
if (!function_exists('loadHTMLPage')) {
    function loadHTMLPage($page_url = '', $replacements = array()) {
        $html = Wo_LoadPage($page_url);
        if (!empty($replacements) && is_array($replacements)) {
            foreach ($replacements as $key => $value) {
                if (!is_array($value) && !is_object($value)) {
                    $html = str_replace('{{' . $key . '}}', (string) $value, $html);
                }
            }
        }
        return $html;
    }
}
if (!function_exists('wo_filter_table_columns')) {
    function wo_filter_table_columns($table, $data = array()) {
        global $sqlConnect;
        static $cache = array();
        if (!is_array($data)) {
            return array();
        }
        if (!isset($cache[$table])) {
            $cache[$table] = array();
            $result = mysqli_query($sqlConnect, "SHOW COLUMNS FROM " . $table);
            if ($result) {
                while ($col = mysqli_fetch_assoc($result)) {
                    $cache[$table][$col['Field']] = true;
                }
            }
        }
        if (empty($cache[$table])) {
            return $data;
        }
        $filtered = array();
        foreach ($data as $key => $value) {
            if (isset($cache[$table][$key])) {
                if ($value === null || is_array($value) || is_object($value)) {
                    $value = '';
                }
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }
}
if (!function_exists('wo_clear_empty_uploads')) {
    function wo_clear_empty_uploads() {
        $single = array('postFile', 'postVideo', 'postMusic', 'video_thumb');
        foreach ($single as $field) {
            if (!isset($_FILES[$field])) {
                continue;
            }
            $name = isset($_FILES[$field]['name']) ? $_FILES[$field]['name'] : '';
            $tmp  = isset($_FILES[$field]['tmp_name']) ? $_FILES[$field]['tmp_name'] : '';
            if (empty($name) || empty($tmp) || !is_uploaded_file($tmp)) {
                unset($_FILES[$field]);
            }
        }
        if (!isset($_FILES['postPhotos']) || !isset($_FILES['postPhotos']['name']) || !is_array($_FILES['postPhotos']['name'])) {
            if (isset($_FILES['postPhotos'])) {
                unset($_FILES['postPhotos']);
            }
            return;
        }
        $keep = array('name' => array(), 'type' => array(), 'tmp_name' => array(), 'error' => array(), 'size' => array());
        foreach ($_FILES['postPhotos']['name'] as $i => $name) {
            $tmp = isset($_FILES['postPhotos']['tmp_name'][$i]) ? $_FILES['postPhotos']['tmp_name'][$i] : '';
            $err = isset($_FILES['postPhotos']['error'][$i]) ? $_FILES['postPhotos']['error'][$i] : 4;
            if (!empty($name) && !empty($tmp) && is_uploaded_file($tmp) && (int) $err === 0) {
                $keep['name'][]     = $_FILES['postPhotos']['name'][$i];
                $keep['type'][]     = $_FILES['postPhotos']['type'][$i];
                $keep['tmp_name'][] = $_FILES['postPhotos']['tmp_name'][$i];
                $keep['error'][]    = $_FILES['postPhotos']['error'][$i];
                $keep['size'][]     = $_FILES['postPhotos']['size'][$i];
            }
        }
        if (empty($keep['name'])) {
            unset($_FILES['postPhotos']);
        } else {
            $_FILES['postPhotos'] = $keep;
        }
    }
}
if (!function_exists('wo_video_mime')) {
    function wo_video_mime($path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $map = array(
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
            '3gp' => 'video/3gpp'
        );
        return isset($map[$ext]) ? $map[$ext] : 'video/mp4';
    }
}
if (!function_exists('Wo_IsFfmpegFileAllowed')) {
    function Wo_IsFfmpegFileAllowed($file_name) {
        if (empty($file_name) || !is_string($file_name)) {
            return false;
        }
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        return in_array($ext, array('mp4', 'm4v', 'webm', 'flv', 'mov', 'mpeg', 'mpg', 'mkv', 'avi', '3gp', 'ogv'));
    }
}
if (!function_exists('Wo_IsVideoNotAllowedMime')) {
    function Wo_IsVideoNotAllowedMime($mime) {
        if (empty($mime)) {
            return false;
        }
        $blocked = array('application/x-executable', 'application/x-msdownload', 'text/html', 'application/javascript');
        return in_array(strtolower($mime), $blocked);
    }
}
if (!function_exists('wo_build_story_view')) {
    function wo_build_story_view($story) {
        global $wo, $db;
        if (empty($story)) {
            return false;
        }
        if (is_object($story)) {
            $story_arr = ToArray($story);
            $story_id = $story->id;
            $story_user_id = $story->user_id;
        } else {
            $story_arr = $story;
            $story_id = $story['id'];
            $story_user_id = $story['user_id'];
        }
        if (empty($story_id)) {
            return false;
        }
        $story_media = Wo_GetStoryMedia($story_id, 'image');
        if (empty($story_media)) {
            $story_media = Wo_GetStoryMedia($story_id, 'video');
        }
        if (empty($story_media) || !is_array($story_media)) {
            $story_media = array();
        }
        $story_arr['story_media'] = $story_media;
        $story_arr['view_count'] = (int) $db->where('story_id', $story_id)->where('user_id', $story_user_id, '!=')->getValue(T_STORY_SEEN, 'COUNT(*)');
        $story_arr['story_views'] = array();
        $story_views = $db->where('story_id', $story_id)->where('user_id', $story_user_id, '!=')->get(T_STORY_SEEN, 10);
        if (!empty($story_views)) {
            foreach ($story_views as $value) {
                $user_ = Wo_UserData($value->user_id);
                if (!empty($user_) && is_array($user_)) {
                    $user_['id'] = $value->id;
                    $story_arr['story_views'][] = $user_;
                }
            }
        }
        $story_arr['is_owner'] = false;
        $user_data = Wo_UserData($story_user_id);
        $story_arr['user_data'] = !empty($user_data) ? $user_data : array('user_id' => 0, 'username' => '', 'name' => '', 'avatar' => '', 'url' => '');
        if (!empty($user_data) && isset($wo['user']['user_id']) && $user_data['user_id'] == $wo['user']['user_id']) {
            $story_arr['is_owner'] = true;
        }
        $viewer_id = isset($wo['user']['user_id']) ? $wo['user']['user_id'] : 0;
        if ($viewer_id > 0) {
            $is_viewed = $db->where('story_id', $story_id)->where('user_id', $viewer_id)->getValue(T_STORY_SEEN, 'COUNT(*)');
            if ($is_viewed == 0) {
                $db->insert(T_STORY_SEEN, array(
                    'story_id' => $story_id,
                    'user_id' => $viewer_id,
                    'time' => time()
                ));
                if (!empty($user_data) && $user_data['user_id'] != $viewer_id) {
                    Wo_RegisterNotification(array(
                        'recipient_id' => $user_data['user_id'],
                        'type' => 'viewed_story',
                        'story_id' => $story_id,
                        'text' => '',
                        'url' => 'index.php?link1=timeline&u=' . $wo['user']['username'] . '&story=true&story_id=' . $story_id
                    ));
                }
            }
        }
        return $story_arr;
    }
}
