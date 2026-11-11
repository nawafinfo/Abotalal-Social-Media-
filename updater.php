<?php
require_once 'assets/init.php';
header('Content-Type: application/json');

if (empty($wo['loggedin']) || Wo_IsAdmin() === false) {
    echo json_encode(array('status' => 400, 'ERROR_NAME' => 'Admin login required'));
    exit();
}

$action = !empty($_GET['f']) ? $_GET['f'] : '';
$target = __DIR__;

if ($action === 'run_updater' && !empty($_FILES['update_zip']['tmp_name'])) {
    $tmp = $_FILES['update_zip']['tmp_name'];
    $name = !empty($_FILES['update_zip']['name']) ? $_FILES['update_zip']['name'] : '';
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'zip') {
        echo json_encode(array('status' => 400, 'ERROR_NAME' => 'Please upload a .zip update file'));
        exit();
    }
    $zip = new ZipArchive();
    if ($zip->open($tmp) !== true) {
        echo json_encode(array('status' => 400, 'ERROR_NAME' => 'Could not open the zip file'));
        exit();
    }
    $zip->extractTo($target);
    $zip->close();
    echo json_encode(array('status' => 200));
    exit();
}

echo json_encode(array('status' => 300));
exit();
