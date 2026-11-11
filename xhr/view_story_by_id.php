<?php 
if ($f == 'view_story_by_id') {
    $data['status'] = 400;
    $types = array('next', 'previous');
    if (!empty($_POST['story_id']) && is_numeric($_POST['story_id']) && $_POST['story_id'] > 0 && !empty($_POST['type']) && in_array($_POST['type'], $types)) {
        $data['story_id'] = 0;
        $main_story = $db->where('id', Wo_Secure($_POST['story_id']))->getOne(T_USER_STORY);
        $story = null;
        if (!empty($main_story)) {
            if ($_POST['type'] == 'previous') {
                $story = $db->where('id', Wo_Secure($_POST['story_id']), '>')->where('user_id', $main_story->user_id)->orderBy('id', "ASC")->getOne(T_USER_STORY);
                if (empty($story) && !empty($_POST['story_type']) && $_POST['story_type'] == 'friends') {
                    $all_stories = Wo_GetAllStatus();
                    $next_story_id = 0;
                    if (!empty($all_stories) && is_array($all_stories)) {
                        for ($i = 0; $i < count($all_stories); $i++) {
                            if ($i > 0 && $all_stories[$i]->user_id == $main_story->user_id) {
                                $next_story_id = $all_stories[$i - 1]->id;
                                break;
                            }
                        }
                    }
                    if ($next_story_id > 0) {
                        $story = $db->where('id', $next_story_id)->getOne(T_USER_STORY);
                    }
                }
            } else {
                $story = $db->where('id', Wo_Secure($_POST['story_id']), '<')->where('user_id', $main_story->user_id)->orderBy('id', "Desc")->getOne(T_USER_STORY);
                if (empty($story) && !empty($_POST['story_type']) && $_POST['story_type'] == 'friends') {
                    $all_stories = Wo_GetAllStatus();
                    $next_story_id = 0;
                    if (!empty($all_stories) && is_array($all_stories)) {
                        for ($i = 0; $i < count($all_stories); $i++) {
                            if ($i < (count($all_stories) - 1) && $all_stories[$i]->user_id == $main_story->user_id) {
                                $next_story_id = $all_stories[$i + 1]->id;
                                break;
                            }
                        }
                    }
                    if ($next_story_id > 0) {
                        $story = $db->where('id', $next_story_id)->getOne(T_USER_STORY);
                    }
                }
            }
            if (!empty($story)) {
                $wo['story'] = wo_build_story_view($story);
                if (!empty($wo['story']) && !empty($wo['story']['story_media'])) {
                    $wo['story_type'] = !empty($_POST['story_type']) ? Wo_Secure($_POST['story_type']) : '';
                    $data['story_id'] = $wo['story']['id'];
                    $data['html'] = Wo_LoadPage('lightbox/story');
                    $data['status'] = 200;
                }
            }
        }
    }
    Wo_CleanCache();
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
