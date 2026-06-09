<?php
define('G5_IS_ADMIN', true);
require_once __DIR__ . '/../../../common.php';

if ($is_admin !== 'super') {
    alert('최고관리자만 접근 가능합니다.', G5_URL);
}

if (is_file(G5_LIB_PATH . '/icrm-member.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-member.lib.php';
}
if (function_exists('icrm_member_enabled') && icrm_member_enabled() && function_exists('icrm_member_url')) {
    goto_url(icrm_member_url('update'));
    exit;
}

if (is_file(G5_LIB_PATH . '/icrm-point.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-point.lib.php';
}
if (is_file(G5_LIB_PATH . '/seo-meta.lib.php')) {
    include_once G5_LIB_PATH . '/seo-meta.lib.php';
}

if (!is_file(G5_LIB_PATH . '/icrm-update.lib.php') && is_file(G5_LIB_PATH . '/icrm-update-bootstrap.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-update-bootstrap.lib.php';
    icrm_update_bootstrap_install();
}

if (is_file(G5_LIB_PATH . '/onoff-update.lib.php')) {
    include_once G5_LIB_PATH . '/onoff-update.lib.php';
}
if (is_file(G5_LIB_PATH . '/icrm-update.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-update.lib.php';
}
if (is_file(G5_LIB_PATH . '/icrm-builder-deploy.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-builder-deploy.lib.php';
}

$admin_url = G5_PLUGIN_URL . '/icrm_update/admin/index.php';
$action_url = G5_PLUGIN_URL . '/icrm_update/admin/action.php';
$status = function_exists('icrm_update_check_status') ? icrm_update_check_status() : array(
    'ready' => false,
    'message' => '업데이트 모듈이 없습니다. iCRM 라이선스 설정 후 새로고침하세요.',
);
$builder_status = function_exists('icrm_builder_deploy_check_status') ? icrm_builder_deploy_check_status() : array(
    'ready' => false,
    'message' => '빌더 배포 모듈이 없습니다.',
);
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>iCRM 업데이트</title>
<link rel="stylesheet" href="<?php echo htmlspecialchars(G5_URL . '/css/icrm-update-panel.css', ENT_QUOTES, 'UTF-8'); ?>">
<style>
body{margin:0;background:#eef2f7;color:#0f172a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,'Malgun Gothic',sans-serif;font-size:14px;line-height:1.5}
.icu-top{background:#1e293b;color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.icu-top h1{margin:0;font-size:18px;font-weight:600}
.icu-top a{color:#cbd5e1;text-decoration:none;font-size:13px}
.icu-wrap{max-width:720px;margin:24px auto;padding:0 16px 40px}
</style>
</head>
<body>
<header class="icu-top">
    <h1>iCRM 업데이트</h1>
    <a href="<?php echo htmlspecialchars(G5_ADMIN_URL, ENT_QUOTES, 'UTF-8'); ?>">← 관리자 홈</a>
</header>
<div class="icu-wrap">
<?php include __DIR__ . '/_panel.php'; ?>
</div>
</body>
</html>
