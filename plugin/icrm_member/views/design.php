<?php
if (!defined('ICRM_MEMBER_ACTIVE')) {
    exit;
}

$msg = isset($_GET['msg']) ? trim(strip_tags($_GET['msg'])) : '';
define('ICRM_MEMBER_DESIGN_EMBED', true);

if (is_file(G5_PLUGIN_PATH . '/onoff-builder-bridge/bootstrap.php')) {
    include_once G5_PLUGIN_PATH . '/onoff-builder-bridge/bootstrap.php';
}
?>
<div class="icrm-member-embed">
<?php
include G5_PLUGIN_PATH . '/onoff-builder-bridge/member/_panel_design.php';
include __DIR__ . '/_panel_platform_skin.php';
?>
</div>
<link rel="stylesheet" href="<?php echo icrm_member_h(G5_PLUGIN_URL . '/onoff-builder-bridge/assets/css/admin.css'); ?>">
<link rel="stylesheet" href="<?php echo icrm_member_h(G5_PLUGIN_URL . '/onoff-builder-bridge/assets/css/member.css'); ?>">
<script src="<?php echo icrm_member_h(G5_PLUGIN_URL . '/onoff-builder-bridge/assets/js/member.js'); ?>"></script>
<script>
document.body.setAttribute('data-action-url', <?php echo json_encode(function_exists('icrm_member_action_url') ? icrm_member_action_url() : G5_PLUGIN_URL . '/icrm_member/action.php'); ?>);
</script>
