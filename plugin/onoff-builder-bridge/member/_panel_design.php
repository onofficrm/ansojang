<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

global $member;

$zip_ok = class_exists('ZipArchive');
$msg = isset($_GET['msg']) ? trim(strip_tags($_GET['msg'])) : '';
$projects = onoff_builder_get_imports();
$default_project_id = '';
$default_project_name = '';

if (function_exists('g5site_cfg')) {
    $default_project_id = trim(g5site_cfg('home_builder_bridge_id', ''));
}
if ($default_project_id === '' && $projects !== array()) {
    $default_project_id = isset($projects[0]['id']) ? (string) $projects[0]['id'] : '';
}
if ($default_project_id !== '' && onoff_builder_project_exists($default_project_id)) {
    $row = onoff_builder_get_import($default_project_id);
    if (is_array($row) && !empty($row['name'])) {
        $default_project_name = (string) $row['name'];
    }
}

$default_project_needs_build = false;
if ($default_project_id !== '') {
    $default_import = onoff_builder_get_import($default_project_id);
    $default_project_needs_build = is_array($default_import) && !empty($default_import['needs_build']);
}

$needs_build_projects = array();
foreach ($projects as $proj) {
    if (!empty($proj['needs_build'])) {
        $needs_build_projects[] = $proj;
    }
}

$license_ok = false;
$deploy_ready = false;
$deploy_message = '';
$builder_status = array(
    'local_release'    => '',
    'remote_release'   => '',
    'update_available' => false,
    'page_url'         => '',
    'home_url'         => '',
    'preview_url'      => '',
    'history'          => array(),
);

if (is_file(G5_LIB_PATH . '/icrm-builder-deploy.lib.php')) {
    include_once G5_LIB_PATH . '/icrm-builder-deploy.lib.php';
    if (function_exists('icrm_builder_deploy_get_license_key')) {
        $license_ok = icrm_builder_deploy_get_license_key() !== '';
    }
    if ($license_ok && function_exists('icrm_builder_deploy_check_status')) {
        $builder_status = icrm_builder_deploy_check_status();
        $deploy_ready = !empty($builder_status['ready']) || !empty($builder_status['license_ok']);
        $deploy_message = isset($builder_status['message']) ? (string) $builder_status['message'] : '';
    } elseif (!$license_ok) {
        $deploy_message = '사이트 iCRM 라이선스가 아직 설정되지 않았습니다. 관리자에게 문의하세요.';
    }
}

$auto_home_default = function_exists('g5site_cfg_bool') ? g5site_cfg_bool('builder_deploy_auto_home', true) : true;

$local_preview_url = '';
if ($default_project_id !== '' && onoff_builder_project_exists($default_project_id)) {
    $local_preview_url = G5_PLUGIN_URL . '/onoff-builder-bridge/page.php?id=' . rawurlencode($default_project_id);
}
$member_preview_url = (defined('ICRM_MEMBER_DESIGN_EMBED') && $local_preview_url !== '')
    ? $local_preview_url
    : (isset($builder_status['preview_url']) ? (string) $builder_status['preview_url'] : '');

$upload_action = defined('ICRM_MEMBER_DESIGN_EMBED')
    ? G5_PLUGIN_URL . '/onoff-builder-bridge/member/upload_update.php'
    : onoff_builder_member_url('upload_update.php');

$design_action_url = defined('ICRM_MEMBER_DESIGN_EMBED') && function_exists('icrm_member_action_url')
    ? icrm_member_action_url()
    : onoff_builder_member_url('action.php');
?>

<?php if (!defined('ICRM_MEMBER_DESIGN_EMBED')) { ?>
<div class="onoff-builder-admin__page-head">
  <h1>홈페이지 디자인 배포</h1>
  <p class="onoff-builder-admin__lead">빌더에서 만든 dist ZIP을 올리고, 버튼 한 번으로 사이트에 반영합니다.</p>
  <?php if (!empty($member['mb_nick'])) { ?>
  <p class="onoff-builder-admin__hint">로그인: <?php echo onoff_builder_escape($member['mb_nick']); ?> (레벨 <?php echo (int) $member['mb_level']; ?>)</p>
  <?php } ?>
</div>
<?php } ?>

<?php if ($msg !== '') { ?>
<p class="onoff-builder-admin__notice"><?php echo onoff_builder_escape($msg); ?></p>
<?php } ?>

<div id="obb-member-msg" class="onoff-builder-member__msg" hidden></div>

<div class="onoff-builder-member__steps">
  <section class="onoff-builder-member__step">
    <h2>1. 빌더 ZIP 업로드</h2>
    <p>빌더에서 다운받은 ZIP을 그대로 올리세요. dist가 포함되어 있으면 바로 배포할 수 있고, 원본만 있으면 아래 빌드 단계로 진행합니다.</p>
    <?php if (!$zip_ok) { ?>
    <p class="onoff-builder-admin__alert">서버에 ZipArchive가 없어 업로드를 사용할 수 없습니다.</p>
    <?php } else { ?>
    <form class="onoff-builder-admin__form" method="post" action="<?php echo onoff_builder_escape($upload_action); ?>" enctype="multipart/form-data">
      <div class="onoff-builder-admin__field">
        <label for="project_id">프로젝트 ID</label>
        <input type="text" name="project_id" id="project_id" required pattern="[a-z0-9_-]{2,50}" maxlength="50" value="<?php echo onoff_builder_escape($default_project_id); ?>" placeholder="headnerve-main">
      </div>
      <div class="onoff-builder-admin__field">
        <label for="project_name">프로젝트 이름</label>
        <input type="text" name="project_name" id="project_name" required maxlength="100" value="<?php echo onoff_builder_escape($default_project_name); ?>" placeholder="메인 홈페이지">
      </div>
      <div class="onoff-builder-admin__field">
        <label for="zip_file">빌더 ZIP</label>
        <input type="file" name="zip_file" id="zip_file" accept=".zip,application/zip" required>
      </div>
      <div class="onoff-builder-admin__form-actions">
        <button type="submit" class="onoff-builder-admin__btn onoff-builder-admin__btn--primary">ZIP 업로드</button>
      </div>
    </form>
    <?php } ?>
  </section>

  <?php if ($needs_build_projects !== array() && $zip_ok) { ?>
  <section class="onoff-builder-member__step" id="obb-build-step">
    <h2>1-2. 빌드 (원본 ZIP 업로드 후)</h2>
    <p>원본 프로젝트가 저장된 경우, iCRM에서 빌드하거나 로컬에서 <code>npm run build</code> 후 dist ZIP을 별도로 업로드할 수 있습니다.</p>

    <div class="onoff-builder-admin__form-actions" style="margin-bottom:16px">
      <button type="button" class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" id="obb-build-source"
        data-project-id="<?php echo onoff_builder_escape($default_project_id); ?>"
        <?php echo ($license_ok && $default_project_needs_build && function_exists('icrm_builder_deploy_build_source_project')) ? '' : 'disabled'; ?>>
        iCRM에서 빌드
      </button>
    </div>

    <form class="onoff-builder-admin__form" method="post" action="<?php echo onoff_builder_escape($upload_action); ?>" enctype="multipart/form-data">
      <input type="hidden" name="dist_only" value="1">
      <div class="onoff-builder-admin__field">
        <label for="dist_project_id">프로젝트 ID</label>
        <select name="project_id" id="dist_project_id" required>
          <?php foreach ($needs_build_projects as $proj) {
              $pid = isset($proj['id']) ? (string) $proj['id'] : '';
              $pname = isset($proj['name']) ? (string) $proj['name'] : $pid;
              $selected = ($pid === $default_project_id) ? ' selected' : '';
              ?>
          <option value="<?php echo onoff_builder_escape($pid); ?>"<?php echo $selected; ?>><?php echo onoff_builder_escape($pname); ?> (<?php echo onoff_builder_escape($pid); ?>)</option>
          <?php } ?>
        </select>
      </div>
      <div class="onoff-builder-admin__field">
        <label for="dist_project_name">프로젝트 이름</label>
        <input type="text" name="project_name" id="dist_project_name" required maxlength="100" value="<?php echo onoff_builder_escape($default_project_name); ?>">
      </div>
      <div class="onoff-builder-admin__field">
        <label for="dist_zip_file">dist ZIP (별도 업로드)</label>
        <input type="file" name="zip_file" id="dist_zip_file" accept=".zip,application/zip" required>
      </div>
      <div class="onoff-builder-admin__form-actions">
        <button type="submit" class="onoff-builder-admin__btn">dist ZIP 업로드</button>
      </div>
    </form>
  </section>
  <?php } ?>

  <section class="onoff-builder-member__step">
    <h2>2. 배포하고 바로 적용</h2>
    <p>업로드한 디자인을 iCRM에 등록한 뒤, 이 사이트에 즉시 반영합니다.</p>

    <?php if ($projects === array()) { ?>
    <p class="onoff-builder-admin__hint">먼저 위에서 ZIP을 업로드하세요.</p>
    <?php } else { ?>
    <div class="onoff-builder-admin__field">
      <label for="obb-project-select">적용할 프로젝트</label>
      <select id="obb-project-select" disabled style="max-width:100%">
        <?php foreach ($projects as $p) {
            $pid = isset($p['id']) ? $p['id'] : '';
            $pname = isset($p['name']) ? $p['name'] : $pid;
            $selected = ($pid === $default_project_id) ? ' selected' : '';
            $needs_build = !empty($p['needs_build']) ? ' data-needs-build="1"' : '';
            ?>
        <option value="<?php echo onoff_builder_escape($pid); ?>"<?php echo $selected . $needs_build; ?>><?php echo onoff_builder_escape($pname); ?><?php echo !empty($p['needs_build']) ? ' (빌드 필요)' : ''; ?> (<?php echo onoff_builder_escape($pid); ?>)</option>
        <?php } ?>
      </select>
    </div>

    <dl class="onoff-builder-member__status">
      <dt>사이트 적용 버전</dt>
      <dd><code><?php echo onoff_builder_escape($builder_status['local_release'] ?: '(없음)'); ?></code></dd>
      <dt>iCRM 최신 디자인</dt>
      <dd><code><?php echo onoff_builder_escape($builder_status['remote_release'] ?: '-'); ?></code></dd>
      <?php if (!empty($builder_status['page_url'])) { ?>
      <dt>페이지 URL</dt>
      <dd><a href="<?php echo onoff_builder_escape($builder_status['page_url']); ?>" target="_blank" rel="noopener"><?php echo onoff_builder_escape($builder_status['page_url']); ?></a></dd>
      <?php } ?>
    </dl>

    <?php if ($deploy_message !== '' && (!$license_ok || !$deploy_ready)) { ?>
    <p class="onoff-builder-admin__alert"><?php echo onoff_builder_escape($deploy_message); ?></p>
    <?php } ?>

    <?php if ($default_project_needs_build) { ?>
    <p class="onoff-builder-admin__alert">선택한 프로젝트는 빌드가 필요합니다. [iCRM에서 빌드] 또는 dist ZIP 업로드 후 배포하세요.</p>
    <?php } ?>

    <label class="onoff-builder-member__check">
      <input type="checkbox" id="obb-connect-home" value="1" <?php echo $auto_home_default ? 'checked' : ''; ?>>
      적용 후 홈(<code>/</code>)에 이 디자인 연결
    </label>

    <div class="onoff-builder-admin__form-actions">
      <button type="button" class="onoff-builder-admin__btn onoff-builder-admin__btn--primary" id="obb-publish-apply"
        data-project-id="<?php echo onoff_builder_escape($default_project_id); ?>"
        <?php echo ($license_ok && $default_project_id !== '' && !$default_project_needs_build && function_exists('icrm_builder_deploy_publish_and_apply')) ? '' : 'disabled'; ?>>
        배포하고 바로 적용
      </button>
      <?php if ($member_preview_url !== '') { ?>
      <a class="onoff-builder-admin__btn" href="<?php echo onoff_builder_escape($member_preview_url); ?>" target="_blank" rel="noopener"><?php echo (defined('ICRM_MEMBER_DESIGN_EMBED') && $local_preview_url !== '') ? '업로드본 미리보기' : '적용 전 미리보기'; ?></a>
      <?php } ?>
      <button type="button" class="onoff-builder-admin__btn" id="obb-rollback" <?php echo empty($builder_status['history']) ? 'disabled' : ''; ?>>이전 디자인 복구</button>
    </div>
    <?php } ?>
  </section>
</div>

<script>
document.body.setAttribute('data-action-url', <?php echo json_encode($design_action_url); ?>);
(function(){
  var sel=document.getElementById('obb-project-select');
  var btn=document.getElementById('obb-publish-apply');
  var buildBtn=document.getElementById('obb-build-source');
  if(!sel)return;
  sel.disabled=false;
  function syncProject(){
    var opt=sel.options[sel.selectedIndex];
    var pid=sel.value||'';
    var needsBuild=opt&&opt.getAttribute('data-needs-build')==='1';
    if(btn){
      btn.setAttribute('data-project-id',pid);
      btn.disabled=!pid||needsBuild;
    }
    if(buildBtn){
      buildBtn.setAttribute('data-project-id',pid);
      buildBtn.disabled=!pid||!needsBuild;
    }
  }
  sel.addEventListener('change',syncProject);
  syncProject();
})();
</script>
