<?php 
if ($f == 'story_view') {
    $data['status'] = 400;
    if (!empty($_POST['id']) && is_numeric($_POST['id']) && $_POST['id'] > 0) {
        $story_id = Wo_Secure($_POST['id']);
        $story = $db->where('id', $story_id)->getOne(T_USER_STORY);
        if (!empty($story)) {
            $wo['story'] = wo_build_story_view($story);
            if (!empty($wo['story']) && !empty($wo['story']['story_media'])) {
                $wo['story_type'] = !empty($_POST['type']) ? Wo_Secure($_POST['type']) : 'user';
                $data['story_id'] = $story_id;
                $data['html'] = Wo_LoadPage('lightbox/story');
                $data['status'] = 200;
            }
        }
        header("Content-type: application/json");
        echo json_encode($data);
        exit();
    }
}
