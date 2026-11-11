<?php 
if ($f == 'view_all_stories') {
    $data['status'] = 400;
    if (!empty($_POST['user_id']) && is_numeric($_POST['user_id']) && $_POST['user_id'] > 0) {
        $story = $db->where('user_id', Wo_Secure($_POST['user_id']))->orderBy('id', "Desc")->getOne(T_USER_STORY);
        if (!empty($story)) {
            $wo['story'] = wo_build_story_view($story);
            if (!empty($wo['story']) && !empty($wo['story']['story_media'])) {
                $wo['story_type'] = !empty($_POST['type']) ? Wo_Secure($_POST['type']) : 'friends';
                $data['story_id'] = $wo['story']['id'];
                $data['html'] = Wo_LoadPage('lightbox/story');
                $data['status'] = 200;
            }
        }
    }
    Wo_CleanCache();
    header("Content-type: application/json");
    echo json_encode($data);
    exit();
}
