(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  function initFieldEditor(inputId, changeBtnId, cancelBtnId, saveBtnId, placeholder) {
    var input = byId(inputId);
    var changeBtn = byId(changeBtnId);
    var cancelBtn = byId(cancelBtnId);
    var saveBtn = byId(saveBtnId);
    if (!input || !changeBtn || !cancelBtn || !saveBtn) return;

    var original = input.getAttribute('data-original-value') || input.value || '';

    changeBtn.addEventListener('click', function () {
      input.removeAttribute('readonly');
      input.value = '';
      input.placeholder = placeholder || '';
      input.focus();
      changeBtn.classList.add('is-hidden');
      cancelBtn.classList.remove('is-hidden');
      saveBtn.classList.remove('is-hidden');
    });

    cancelBtn.addEventListener('click', function () {
      input.value = original;
      input.setAttribute('readonly', 'readonly');
      input.placeholder = '';
      changeBtn.classList.remove('is-hidden');
      cancelBtn.classList.add('is-hidden');
      saveBtn.classList.add('is-hidden');
    });
  }

  function init() {
    var cfg = window.GuardianSettingsConfig || {};
    var toast = byId('guardian-ux-toast');
    var updatedBadge = byId('guardian-last-updated');

    function relativeLabel(ts) {
      var now = Math.floor(Date.now() / 1000);
      var diff = Math.max(0, now - ts);
      if (diff < 60) return 'Last updated just now';
      var mins = Math.floor(diff / 60);
      if (mins < 60) return 'Last updated ' + mins + ' min ago';
      var hrs = Math.floor(mins / 60);
      if (hrs < 24) return 'Last updated ' + hrs + ' hour' + (hrs === 1 ? '' : 's') + ' ago';
      var days = Math.floor(hrs / 24);
      return 'Last updated ' + days + ' day' + (days === 1 ? '' : 's') + ' ago';
    }

    function showToast(message, type) {
      if (!toast) return;
      toast.textContent = message;
      toast.className = '';
      toast.classList.add(type || 'info');
      toast.classList.add('show');
      window.clearTimeout(showToast._t);
      showToast._t = window.setTimeout(function () {
        toast.classList.remove('show');
      }, 2200);
    }

    initFieldEditor('guardian-name', 'name-change-btn', 'name-cancel-btn', 'name-save-btn', 'Enter full name');
    initFieldEditor('guardian-email', 'email-change-btn', 'email-cancel-btn', 'email-save-btn', 'Enter new email');
    initFieldEditor('guardian-phone', 'phone-change-btn', 'phone-cancel-btn', 'phone-save-btn', 'Enter phone number');

    var passwordCard = byId('password-change-card');
    var passwordCardCancelBtn = byId('password-card-cancel-btn');
    var passwordChangeBtn = byId('password-change-btn');
    var verifiedCurrentPassword = byId('verified-current-password');
    var newPasswordInput = byId('new_password');
    var confirmPasswordInput = byId('confirm_password');

    var modal = byId('password-verify-modal');
    var modalInput = byId('modal-current-password');
    var modalVerifyBtn = byId('modal-verify-btn');
    var modalCancelBtn = byId('modal-cancel-btn');
    var modalError = byId('password-verify-error');
    var modalSuccess = byId('password-verify-success');

    function openModal() {
      if (!modal) return;
      modal.classList.remove('hidden');
      modal.style.display = 'flex';
      modalInput.value = '';
      modalError.classList.add('is-hidden');
      modalError.textContent = '';
      if (modalSuccess) {
        modalSuccess.classList.add('is-hidden');
        modalSuccess.textContent = '';
      }
      modalInput.focus();
    }

    function closeModal() {
      if (!modal) return;
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }

    if (passwordChangeBtn) {
      passwordChangeBtn.addEventListener('click', openModal);
    }

    if (passwordCardCancelBtn) {
      passwordCardCancelBtn.addEventListener('click', function () {
        if (passwordCard) {
          passwordCard.classList.add('is-hidden');
        }
        if (newPasswordInput) newPasswordInput.value = '';
        if (confirmPasswordInput) confirmPasswordInput.value = '';
        if (verifiedCurrentPassword) verifiedCurrentPassword.value = '';
        showToast('Password change cancelled.', 'info');
      });
    }
    if (modalCancelBtn) {
      modalCancelBtn.addEventListener('click', closeModal);
    }
    if (modal) {
      modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });

    if (modalVerifyBtn) {
      modalVerifyBtn.addEventListener('click', function () {
        var currentPassword = (modalInput.value || '').trim();
        if (!currentPassword) {
          modalError.textContent = 'Please enter your current password.';
          modalError.classList.remove('is-hidden');
          return;
        }

        var formData = new FormData();
        formData.append('current_password', modalInput.value);

        fetch(cfg.verifyUrl || '', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
          .then(function (res) { return res.json(); })
          .then(function (json) {
            if (json && json.ok) {
              if (verifiedCurrentPassword) {
                verifiedCurrentPassword.value = modalInput.value;
              }
              if (modalSuccess) {
                modalSuccess.textContent = 'Password verified successfully.';
                modalSuccess.classList.remove('is-hidden');
              }
              window.setTimeout(function () {
                if (passwordCard) {
                  passwordCard.classList.remove('is-hidden');
                }
                closeModal();
                showToast('Password verified. You can now set a new password.', 'success');
              }, 450);
            } else {
              var code = (json && json.code) ? json.code : '';
              var msg = (json && json.message) ? json.message : 'Current password is incorrect.';
              var remaining = (json && typeof json.remaining_attempts === 'number') ? json.remaining_attempts : null;
              if (code === 'incorrect_password') {
                msg = 'Incorrect password. Try again.';
                if (remaining !== null) {
                  msg += ' (' + remaining + ' attempt' + (remaining === 1 ? '' : 's') + ' left)';
                }
              }
              modalError.textContent = msg;
              modalError.classList.remove('is-hidden');
              if (modalSuccess) {
                modalSuccess.classList.add('is-hidden');
                modalSuccess.textContent = '';
              }
            }
          })
          .catch(function () {
            modalError.textContent = 'Could not verify password right now. Please try again.';
            modalError.classList.remove('is-hidden');
            if (modalSuccess) {
              modalSuccess.classList.add('is-hidden');
              modalSuccess.textContent = '';
            }
          });
      });
    }

    if (cfg.startWithPasswordCard && passwordCard) {
      passwordCard.classList.remove('is-hidden');
    }
    if (modal) {
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }

    var successMsg = document.querySelector('.alert-success');
    if (successMsg && successMsg.textContent.trim()) {
      showToast(successMsg.textContent.trim(), 'success');
    }

    if (updatedBadge) {
      var ts = parseInt(updatedBadge.getAttribute('data-updated-at') || '0', 10);
      if (ts > 0) {
        updatedBadge.textContent = relativeLabel(ts);
        window.setInterval(function () {
          updatedBadge.textContent = relativeLabel(ts);
        }, 30000);
      }
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
