<?php
require_once __DIR__ . '/../../../common.php';

header('Content-Type: application/json; charset=utf-8');

if ($is_admin !== 'super') {
    echo json_encode(array('ok' => false, 'error' => '최고관리자만 사용할 수 있습니다.'), JSON_UNESCAPED_UNICODE);
    exit;
}

if (is_file(G5_LIB_PATH . '/icrm-point.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-point.lib.php';
}
if (is_file(G5_LIB_PATH . '/seo-meta.lib.php')) {
    include_once G5_LIB_PATH . '/seo-meta.lib.php';
}

if (!is_file(G5_LIB_PATH . '/icrm-update.lib.php')) {
    if (is_file(G5_LIB_PATH . '/icrm-update-bootstrap.lib.php')) {
        include_once G5_LIB_PATH . '/icrm-update-bootstrap.lib.php';
        icrm_update_bootstrap_install();
    }
}

if (is_file(G5_LIB_PATH . '/onoff-update.lib.php')) {
    include_once G5_LIB_PATH . '/onoff-update.lib.php';
}
if (!is_file(G5_LIB_PATH . '/icrm-update.lib.php')) {
    echo json_encode(array('ok' => false, 'error' => '업데이트 모듈을 설치할 수 없습니다. iCRM 라이선스를 확인하세요.'), JSON_UNESCAPED_UNICODE);
    exit;
}

include_once G5_LIB_PATH . '/icrm-update.lib.php';
if (is_file(G5_LIB_PATH . '/icrm-builder-deploy.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-builder-deploy.lib.php';
}

$action = isset($_REQUEST['action']) ? preg_replace('/[^a-z_]/', '', $_REQUEST['action']) : '';

if ($action === 'status') {
    echo json_encode(array('ok' => true, 'status' => icrm_update_check_status()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'builder_status') {
    if (!function_exists('icrm_builder_deploy_check_status')) {
        echo json_encode(array('ok' => false, 'error' => '빌더 배포 모듈이 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo json_encode(array('ok' => true, 'status' => icrm_builder_deploy_check_status()), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'pull') {
    $dry = !empty($_POST['dry_run']) || !empty($_GET['dry_run']);
    $result = icrm_update_pull($dry);
    echo json_encode(array(
        'ok'     => !empty($result['success']),
        'result' => $result,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'builder_pull') {
    if (!function_exists('icrm_builder_deploy_pull')) {
        echo json_encode(array('ok' => false, 'error' => '빌더 배포 모듈이 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $dry = !empty($_POST['dry_run']) || !empty($_GET['dry_run']);
    $result = icrm_builder_deploy_pull($dry);
    echo json_encode(array(
        'ok'     => !empty($result['success']),
        'result' => $result,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'builder_rollback') {
    if (!function_exists('icrm_builder_deploy_rollback')) {
        echo json_encode(array('ok' => false, 'error' => '빌더 배포 모듈이 없습니다.'), JSON_UNESCAPED_UNICODE);
        exit;
    }
    $release_id = isset($_POST['release_id']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', (string) $_POST['release_id']) : '';
    $result = icrm_builder_deploy_rollback($release_id);
    echo json_encode(array(
        'ok'     => !empty($result['success']),
        'result' => $result,
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array('ok' => false, 'error' => 'unknown action'), JSON_UNESCAPED_UNICODE);
