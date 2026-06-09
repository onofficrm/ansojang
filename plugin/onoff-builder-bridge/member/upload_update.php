<?php
include_once(dirname(__DIR__) . '/../../common.php');
include_once(dirname(__DIR__) . '/bootstrap.php');

onoff_builder_require_deploy_user();
onoff_builder_require_post();

$project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
$project_name = isset($_POST['project_name']) ? trim(strip_tags($_POST['project_name'])) : '';

if ($project_name === '') {
    onoff_builder_member_portal_redirect('프로젝트 이름을 입력하세요.');
}

if (!isset($_FILES['zip_file'])) {
    onoff_builder_member_portal_redirect('ZIP 파일을 선택하세요.');
}

$result = onoff_builder_handle_zip_upload($project_id, $project_name, $_FILES['zip_file']);

if (empty($result['ok'])) {
    onoff_builder_member_portal_redirect(
        isset($result['message']) ? $result['message'] : '가져오기에 실패했습니다.'
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
    onoff_builder_member_portal_redirect('프로젝트 정보 저장에 실패했습니다.');
}

$msg = isset($result['message']) ? $result['message'] : '업로드가 완료되었습니다. 아래 [배포하고 바로 적용]을 눌러 주세요.';
onoff_builder_member_portal_redirect($msg);
