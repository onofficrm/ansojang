<?php
if (!defined('ICRM_MEMBER_ACTIVE')) {
    exit;
}

$modules = icrm_member_modules();
$license_ok = function_exists('icrm_admin_shell_license_ok') ? icrm_admin_shell_license_ok() : false;
?>
<?php include __DIR__ . '/_panel_onboarding.php'; ?>

<div class="icrm-member-dash">
    <?php foreach ($modules as $key => $item) {
        if ($key === 'update' && !icrm_member_can_access()) {
            continue;
        }
        $can = icrm_member_can_module($key);
        $lock_reason = $can ? '' : icrm_member_module_lock_reason($key);
        ?>
    <div class="icrm-member-card<?php echo $can ? '' : ' is-locked'; ?>">
        <div class="icrm-member-card__body">
            <h3><?php echo icrm_member_h($item['label']); ?></h3>
            <p><?php echo icrm_member_h($item['desc']); ?></p>
        </div>
        <?php if ($can) { ?>
        <div class="icrm-member-card__footer">
            <a class="icc-btn icc-btn--primary" href="<?php echo icrm_member_h(icrm_member_url($key)); ?>">
                <?php echo $key === 'home' ? '바로가기' : '열기'; ?>
            </a>
        </div>
        <?php } else { ?>
        <p class="icrm-member-card__lock"><?php echo icrm_member_h($lock_reason); ?></p>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<div class="icrm-member-dash-status">
    <strong>iCRM 연동</strong>
    <?php if ($license_ok) { ?>
    <span style="color:#0f766e"> · 연동됨</span>
    <?php } else { ?>
    <span style="color:#b45309"> · 미설정 (관리자에게 문의)</span>
    <?php } ?>
    <?php if (icrm_member_can_update()) { ?>
    <span style="color:#64748b"> · 게시판·콘텐츠 발행은 <a href="<?php echo icrm_member_h(G5_PLUGIN_URL . '/icrm_hub/admin/index.php'); ?>">iCRM AI 관리</a>에서 진행합니다.</span>
    <?php } elseif (icrm_member_can_access()) { ?>
    <span style="color:#64748b"> · 게시판·콘텐츠 발행은 iCRM AI 관리에서 진행합니다. (관리자에게 문의)</span>
    <?php } ?>
</div>
