<?php
if ($wo['loggedin'] == false) {
    header("Location: " . Wo_SeoLink('index.php?link1=welcome'));
    exit();
}
$wo['notifications'] = Wo_GetNotifications(array(
    'limit' => 50
));
$wo['description'] = $wo['config']['siteDesc'];
$wo['keywords']    = $wo['config']['siteKeywords'];
$wo['page']        = 'notifications';
$wo['title']       = !empty($wo['lang']['notifications']) ? $wo['lang']['notifications'] : 'Notifications';
$wo['content']     = Wo_LoadPage('notifications/content');
