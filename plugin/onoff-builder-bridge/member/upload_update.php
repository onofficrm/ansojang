<?php
include_once(dirname(__DIR__) . '/_common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_deploy_user();
onoff_builder_require_post();

$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
$project_name = isset($_POST['project_name']) ? trim(strip_tags($_POST['project_name'])) : '';

if ($project_name === '') {
    onoff_builder_alert('프로젝트 이름을 입력하세요.', onoff_builder_member_url());
}

if (!isset($_FILES['zip_file'])) {
    onoff_builder_alert('ZIP 파일을 선택하세요.', onoff_builder_member_url());
}

$result = onoff_builder_handle_zip_upload($project_id, $project_name, $_FILES['zip_file']);

if (empty($result['ok'])) {
    onoff_builder_alert(
        isset($result['message']) ? $result['message'] : '가져오기에 실패했습니다.',
        onoff_builder_member_url()
    );
}

$id = $result['project_id'];
$entry = isset($result['entry']) ? $result['entry'] : 'index.html';

if (!onoff_builder_add_import(array(
    'id'    => $id,
    'name'  => $result['project_name'],
    'path'  => $id,
    'entry' => $entry,
))) {
    onoff_builder_remove_dir(onoff_builder_project_dir($id));
    onoff_builder_alert('프로젝트 정보 저장에 실패했습니다.', onoff_builder_member_url());
}

$msg = isset($result['message']) ? $result['message'] : '업로드가 완료되었습니다. 아래 [배포하고 바로 적용]을 눌러 주세요.';

$redirect = onoff_builder_member_url('?msg=' . urlencode($msg));
if (function_exists('icrm_member_enabled') && icrm_member_enabled() && is_file(G5_PLUGIN_PATH . '/icrm_member/index.php')) {
    if (is_file(G5_LIB_PATH . '/icrm-member.lib.php')) {
        include_once G5_LIB_PATH . '/icrm-member.lib.php';
    }
    if (function_exists('icrm_member_url')) {
        $redirect = icrm_member_url('design');
        $redirect .= (strpos($redirect, '?') !== false ? '&' : '?') . 'msg=' . urlencode($msg);
    }
}

if (function_exists('goto_url')) {
    goto_url($redirect);
}

header('Location: ' . $redirect);
exit;
