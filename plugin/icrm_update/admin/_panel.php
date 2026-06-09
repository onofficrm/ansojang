<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

if (!function_exists('icu_h')) {
    function icu_h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($action_url) || $action_url === '') {
    $action_url = G5_PLUGIN_URL . '/icrm_update/admin/action.php';
}
if (!isset($status) || !is_array($status)) {
    $status = function_exists('icrm_update_check_status') ? icrm_update_check_status() : array(
        'ready' => false,
        'message' => '업데이트 모듈이 없습니다.',
    );
}
if (!isset($builder_status) || !is_array($builder_status)) {
    $builder_status = function_exists('icrm_builder_deploy_check_status') ? icrm_builder_deploy_check_status() : array(
        'ready' => false,
        'message' => '빌더 배포 모듈이 없습니다.',
    );
}

$license_settings_url = function_exists('icrm_admin_page_url')
    ? icrm_admin_page_url('seo', array('tab' => 'settings'))
    : (G5_PLUGIN_URL . '/icrm_hub/admin/index.php?m=seo&tab=settings');
?>
<div class="icrm-update-panel">
    <div class="icu-card">
        <h2>기능 업데이트</h2>
        <p class="icu-hint">iCRM 중앙 서버에서 최신 기능을 받아옵니다. 버튼 한 번이면 됩니다.</p>

        <div class="icu-row">
            <span class="icu-label">라이선스</span>
            <span class="icu-val">
                <?php if (!empty($status['license_ok'])) { ?>
                    <span class="icu-badge icu-badge--ok">연결됨</span>
                <?php } else { ?>
                    <span class="icu-badge icu-badge--warn">미설정</span>
                <?php } ?>
            </span>
        </div>
        <div class="icu-row">
            <span class="icu-label">이 사이트 버전</span>
            <span class="icu-val"><code><?php echo icu_h($status['local_release'] ?: '(없음)'); ?></code></span>
        </div>
        <div class="icu-row">
            <span class="icu-label">iCRM 최신 버전</span>
            <span class="icu-val"><code><?php echo icu_h($status['remote_release'] ?: '-'); ?></code></span>
        </div>
        <div class="icu-row">
            <span class="icu-label">업데이트</span>
            <span class="icu-val">
                <?php if (!empty($status['update_available'])) { ?>
                    <span class="icu-badge icu-badge--warn">새 버전 있음</span>
                <?php } elseif (!empty($status['ready'])) { ?>
                    <span class="icu-badge icu-badge--ok">최신</span>
                <?php } else { ?>
                    <span class="icu-badge icu-badge--muted">확인 불가</span>
                <?php } ?>
            </span>
        </div>

        <?php if (empty($status['license_ok'])) { ?>
        <p class="icu-hint" style="margin-top:12px;color:#b45309">먼저 <a href="<?php echo icu_h($license_settings_url); ?>">iCRM AI 관리 → SEO 메타 → iCRM 연동</a>에서 라이선스 키를 저장하세요.</p>
        <?php } elseif (empty($status['ready']) && !empty($status['message'])) { ?>
        <p class="icu-hint" style="margin-top:12px;color:#b45309"><?php echo icu_h($status['message']); ?></p>
        <?php if (strpos((string) $status['message'], '파싱') !== false || strpos((string) $status['message'], '연결') !== false) { ?>
        <p class="icu-hint" style="margin-top:8px;line-height:1.6">`_site.config.php`의 <code>icrm_update_api_base_url</code>이 <code>https://icrm.co.kr/api/g5-update</code>인지, 호스팅에서 icrm.co.kr HTTPS 아웃바운드가 허용되는지 확인하세요.</p>
        <?php } ?>
        <?php } ?>

        <div class="icu-actions">
            <button type="button" class="icu-btn icu-btn--primary" id="icu-pull" <?php echo empty($status['ready']) ? 'disabled' : ''; ?>>
                <?php echo !empty($status['update_available']) ? '지금 업데이트' : '다시 확인 · 업데이트'; ?>
            </button>
            <button type="button" class="icu-btn icu-btn--ghost" id="icu-refresh">상태 새로고침</button>
        </div>

        <div class="icu-msg" id="icu-msg"></div>
        <pre class="icu-log" id="icu-log"></pre>
    </div>

    <div class="icu-card">
        <h2>자동 업데이트</h2>
        <p class="icu-hint">기본값 켜짐 — 최고관리자가 <?php echo icu_h(function_exists('icrm_update_check_interval_hours') ? icrm_update_check_interval_hours() : 24); ?>시간마다 로그인하면 자동으로 최신 버전을 적용합니다.</p>
    </div>

    <div class="icu-card">
        <h2>빌더 디자인 동기화</h2>
        <p class="icu-hint">iCRM에 등록된 빌더 디자인을 이 사이트에 받아 적용합니다. (회원이 올린 ZIP과 별도로 중앙 서버 기준 동기화)</p>

        <div class="icu-row">
            <span class="icu-label">적용된 디자인</span>
            <span class="icu-val"><code><?php echo icu_h($builder_status['local_release'] ?: '(없음)'); ?></code></span>
        </div>
        <div class="icu-row">
            <span class="icu-label">iCRM 최신 디자인</span>
            <span class="icu-val"><code><?php echo icu_h($builder_status['remote_release'] ?: '-'); ?></code></span>
        </div>
        <?php if (!empty($builder_status['project_name'])) { ?>
        <div class="icu-row">
            <span class="icu-label">프로젝트</span>
            <span class="icu-val"><?php echo icu_h($builder_status['project_name']); ?> <code><?php echo icu_h($builder_status['project_id']); ?></code></span>
        </div>
        <?php } ?>
        <div class="icu-row">
            <span class="icu-label">상태</span>
            <span class="icu-val">
                <?php if (!empty($builder_status['update_available'])) { ?>
                    <span class="icu-badge icu-badge--warn">새 디자인 있음</span>
                <?php } elseif (!empty($builder_status['ready'])) { ?>
                    <span class="icu-badge icu-badge--ok">최신</span>
                <?php } else { ?>
                    <span class="icu-badge icu-badge--muted">확인 불가</span>
                <?php } ?>
            </span>
        </div>
        <?php if (!empty($builder_status['page_url'])) { ?>
        <div class="icu-row">
            <span class="icu-label">미리보기 URL</span>
            <span class="icu-val"><a href="<?php echo icu_h($builder_status['page_url']); ?>" target="_blank" rel="noopener"><?php echo icu_h($builder_status['page_url']); ?></a></span>
        </div>
        <?php } ?>
        <?php if (!empty($builder_status['home_url'])) { ?>
        <div class="icu-row">
            <span class="icu-label">홈 URL</span>
            <span class="icu-val"><a href="<?php echo icu_h($builder_status['home_url']); ?>" target="_blank" rel="noopener"><?php echo icu_h($builder_status['home_url']); ?></a></span>
        </div>
        <?php } ?>
        <?php if (!empty($builder_status['remote_release']) && !empty($builder_status['preview_url'])) { ?>
        <div class="icu-row">
            <span class="icu-label">새 디자인 미리보기</span>
            <span class="icu-val"><a href="<?php echo icu_h($builder_status['preview_url']); ?>" target="_blank" rel="noopener">적용 전 미리보기 ↗</a></span>
        </div>
        <?php } ?>
        <?php if (!empty($builder_status['history']) && is_array($builder_status['history'])) { ?>
        <div class="icu-row" style="flex-direction:column;align-items:stretch">
            <span class="icu-label" style="margin-bottom:6px">이전 릴리스</span>
            <ul style="margin:0;padding-left:18px;font-size:13px">
                <?php foreach (array_slice($builder_status['history'], 0, 5) as $hist) {
                    if (empty($hist['release_id'])) {
                        continue;
                    }
                    ?>
                <li><code><?php echo icu_h($hist['release_id']); ?></code>
                    <?php if (!empty($hist['project_name'])) { ?> — <?php echo icu_h($hist['project_name']); ?><?php } ?>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>

        <?php if (empty($builder_status['license_ok'])) { ?>
        <p class="icu-hint" style="margin-top:12px;color:#b45309">먼저 iCRM 라이선스를 설정하세요.</p>
        <?php } elseif (empty($builder_status['ready']) && !empty($builder_status['message'])) { ?>
        <p class="icu-hint" style="margin-top:12px;color:#b45309"><?php echo icu_h($builder_status['message']); ?></p>
        <?php } ?>

        <div class="icu-actions">
            <button type="button" class="icu-btn icu-btn--primary" id="icb-pull" <?php echo empty($builder_status['ready']) || empty($builder_status['update_available']) ? 'disabled' : ''; ?>>
                빌더 디자인 적용
            </button>
            <button type="button" class="icu-btn icu-btn--ghost" id="icb-rollback" <?php echo empty($builder_status['history']) ? 'disabled' : ''; ?>>이전 버전 복구</button>
            <button type="button" class="icu-btn icu-btn--ghost" id="icb-refresh">상태 새로고침</button>
        </div>

        <div class="icu-msg" id="icb-msg"></div>
        <pre class="icu-log" id="icb-log"></pre>
    </div>
</div>

<script>
(function () {
    var actionUrl = <?php echo json_encode($action_url, JSON_UNESCAPED_UNICODE); ?>;

    function showMsg(text, ok) {
        var el = document.getElementById('icu-msg');
        if (!el) return;
        el.textContent = text;
        el.className = 'icu-msg on ' + (ok ? 'icu-msg--ok' : 'icu-msg--err');
    }

    function setBusy(busy) {
        var pull = document.getElementById('icu-pull');
        var refresh = document.getElementById('icu-refresh');
        if (pull) pull.disabled = busy;
        if (refresh) refresh.disabled = busy;
    }

    function refreshStatus() {
        setBusy(true);
        fetch(actionUrl + '?action=status', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showMsg(data.error || '상태 확인 실패', false);
                    return;
                }
                var st = data.status || {};
                if (st.ready) {
                    location.reload();
                    return;
                }
                showMsg(st.message || 'iCRM 중앙 서버에 연결할 수 없습니다.', false);
            })
            .catch(function () { showMsg('네트워크 오류', false); })
            .finally(function () { setBusy(false); });
    }

    function runPull() {
        if (!confirm('iCRM에서 최신 파일을 받아 적용합니다. 계속할까요?')) {
            return;
        }
        setBusy(true);
        showMsg('업데이트 진행 중…', true);
        var fd = new FormData();
        fd.append('action', 'pull');
        fetch(actionUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var res = data.result || {};
                var log = document.getElementById('icu-log');
                if (log && res.changed && res.changed.length) {
                    log.textContent = res.changed.join('\n');
                    log.className = 'icu-log on';
                }
                if (data.ok) {
                    showMsg(res.message || '완료', true);
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    showMsg(res.message || data.error || '실패', false);
                }
            })
            .catch(function () { showMsg('네트워크 오류', false); })
            .finally(function () { setBusy(false); });
    }

    var icuPull = document.getElementById('icu-pull');
    var icuRefresh = document.getElementById('icu-refresh');
    if (icuPull) icuPull.addEventListener('click', runPull);
    if (icuRefresh) icuRefresh.addEventListener('click', refreshStatus);

    function showBuilderMsg(text, ok) {
        var el = document.getElementById('icb-msg');
        if (!el) return;
        el.textContent = text;
        el.className = 'icu-msg on ' + (ok ? 'icu-msg--ok' : 'icu-msg--err');
    }

    function setBuilderBusy(busy) {
        var pull = document.getElementById('icb-pull');
        var refresh = document.getElementById('icb-refresh');
        var rollback = document.getElementById('icb-rollback');
        if (pull) pull.disabled = busy;
        if (refresh) refresh.disabled = busy;
        if (rollback) rollback.disabled = busy;
    }

    function refreshBuilderStatus() {
        setBuilderBusy(true);
        fetch(actionUrl + '?action=builder_status', { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) {
                    showBuilderMsg(data.error || '상태 확인 실패', false);
                    return;
                }
                location.reload();
            })
            .catch(function () { showBuilderMsg('네트워크 오류', false); })
            .finally(function () { setBuilderBusy(false); });
    }

    function runBuilderPull() {
        if (!confirm('iCRM에서 빌더 디자인을 받아 적용합니다. 계속할까요?')) {
            return;
        }
        setBuilderBusy(true);
        showBuilderMsg('빌더 디자인 적용 중…', true);
        var fd = new FormData();
        fd.append('action', 'builder_pull');
        fetch(actionUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var res = data.result || {};
                var log = document.getElementById('icb-log');
                if (log && res.changed && res.changed.length) {
                    log.textContent = res.changed.join('\n');
                    log.className = 'icu-log on';
                }
                if (data.ok) {
                    var msg = res.message || '완료';
                    if (res.page_url) {
                        msg += ' — ' + res.page_url;
                    }
                    showBuilderMsg(msg, true);
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    showBuilderMsg(res.message || data.error || '실패', false);
                }
            })
            .catch(function () { showBuilderMsg('네트워크 오류', false); })
            .finally(function () { setBuilderBusy(false); });
    }

    var icbPull = document.getElementById('icb-pull');
    var icbRefresh = document.getElementById('icb-refresh');
    if (icbPull) icbPull.addEventListener('click', runBuilderPull);

    function runBuilderRollback() {
        if (!confirm('직전에 적용했던 빌더 디자인으로 복구합니다. 계속할까요?')) {
            return;
        }
        setBuilderBusy(true);
        showBuilderMsg('복구 진행 중…', true);
        var fd = new FormData();
        fd.append('action', 'builder_rollback');
        fetch(actionUrl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var res = data.result || {};
                if (data.ok) {
                    showBuilderMsg(res.message || '복구 완료', true);
                    setTimeout(function () { location.reload(); }, 1500);
                } else {
                    showBuilderMsg(res.message || data.error || '복구 실패', false);
                }
            })
            .catch(function () { showBuilderMsg('네트워크 오류', false); })
            .finally(function () { setBuilderBusy(false); });
    }

    var icbRollback = document.getElementById('icb-rollback');
    if (icbRollback) icbRollback.addEventListener('click', runBuilderRollback);
    if (icbRefresh) icbRefresh.addEventListener('click', refreshBuilderStatus);
})();
</script>
