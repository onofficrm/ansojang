(function () {
  'use strict';

  var msgEl = document.getElementById('obb-member-msg');
  var actionUrl = document.body.getAttribute('data-action-url') || '';

  function showMsg(text, type) {
    if (!msgEl) {
      return;
    }
    msgEl.textContent = text;
    msgEl.className = 'onoff-builder-member__msg is-' + (type || 'busy');
    msgEl.hidden = !text;
  }

  function postAction(action, extra) {
    if (!actionUrl) {
      return Promise.reject(new Error('action URL missing'));
    }
    var fd = new FormData();
    fd.append('action', action);
    if (extra) {
      Object.keys(extra).forEach(function (k) {
        fd.append(k, extra[k]);
      });
    }
    return fetch(actionUrl, {
      method: 'POST',
      credentials: 'same-origin',
      body: fd,
    }).then(function (r) {
      return r.json();
    });
  }

  var publishBtn = document.getElementById('obb-publish-apply');
  if (publishBtn) {
    publishBtn.addEventListener('click', function () {
      var projectId = publishBtn.getAttribute('data-project-id') || '';
      if (!projectId) {
        return;
      }
      if (!window.confirm('디자인을 배포하고 사이트에 바로 적용할까요?')) {
        return;
      }
      publishBtn.disabled = true;
      showMsg('배포 및 적용 중입니다. 잠시만 기다려 주세요…', 'busy');
      var connectHome = document.getElementById('obb-connect-home');
      postAction('publish_apply', {
        project_id: projectId,
        connect_home: connectHome && connectHome.checked ? '1' : '',
      })
        .then(function (data) {
          if (!data.ok) {
            throw new Error((data.error || (data.result && data.result.message)) || '실패');
          }
          var result = data.result || {};
          var lines = [result.message || '완료'];
          if (result.page_url) {
            lines.push('미리보기: ' + result.page_url);
          }
          if (result.home_url) {
            lines.push('홈: ' + result.home_url);
          }
          showMsg(lines.join(' · '), 'ok');
          setTimeout(function () {
            window.location.reload();
          }, 1200);
        })
        .catch(function (err) {
          showMsg(err.message || '요청 실패', 'err');
          publishBtn.disabled = false;
        });
    });
  }

  var rollbackBtn = document.getElementById('obb-rollback');
  if (rollbackBtn) {
    rollbackBtn.addEventListener('click', function () {
      if (!window.confirm('직전 디자인으로 복구할까요?')) {
        return;
      }
      rollbackBtn.disabled = true;
      showMsg('복구 중…', 'busy');
      postAction('builder_rollback', {})
        .then(function (data) {
          if (!data.ok) {
            throw new Error((data.error || (data.result && data.result.message)) || '실패');
          }
          showMsg((data.result && data.result.message) || '복구 완료', 'ok');
          setTimeout(function () {
            window.location.reload();
          }, 1000);
        })
        .catch(function (err) {
          showMsg(err.message || '요청 실패', 'err');
          rollbackBtn.disabled = false;
        });
    });
  }
})();
