(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  function init() {
    var cfg = window.AdminSettingsConfig || {};
    var emailInput = byId('admin-email');
    var emailChangeBtn = byId('email-change-btn');
    var emailCancelBtn = byId('email-cancel-btn');
    var emailSaveBtn = byId('email-save-btn');

    var passwordCard = byId('password-change-card');
    var passwordChangeBtn = byId('password-change-btn');
    var verifiedCurrentPassword = byId('verified-current-password');

    var modal = byId('password-verify-modal');
    var modalInput = byId('modal-current-password');
    var modalVerifyBtn = byId('modal-verify-btn');
    var modalCancelBtn = byId('modal-cancel-btn');
    var modalError = byId('password-verify-error');

    if (emailInput && emailChangeBtn && emailSaveBtn) {
      var originalEmail = emailInput.getAttribute('data-original-value') || emailInput.value || '';

      emailChangeBtn.addEventListener('click', function () {
        emailInput.removeAttribute('readonly');
        emailInput.value = '';
        emailInput.placeholder = 'Enter new EMAIL';
        emailInput.focus();
        emailChangeBtn.classList.add('is-hidden');
        if (emailCancelBtn) {
          emailCancelBtn.classList.remove('is-hidden');
        }
        emailSaveBtn.classList.remove('is-hidden');
      });

      if (emailCancelBtn) {
        emailCancelBtn.addEventListener('click', function () {
          emailInput.value = originalEmail;
          emailInput.setAttribute('readonly', 'readonly');
          emailInput.placeholder = '';
          emailChangeBtn.classList.remove('is-hidden');
          emailCancelBtn.classList.add('is-hidden');
          emailSaveBtn.classList.add('is-hidden');
        });
      }
    }

    function openModal() {
      if (!modal) return;
      modal.classList.remove('hidden');
      modal.style.display = 'flex';
      modalInput.value = '';
      modalError.classList.add('is-hidden');
      modalError.textContent = '';
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

    if (modalCancelBtn) {
      modalCancelBtn.addEventListener('click', closeModal);
    }

    if (modal) {
      modal.addEventListener('click', function (e) {
        if (e.target === modal) {
          closeModal();
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeModal();
      }
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
        formData.append('csrf_token', cfg.verifyCsrfToken || '');
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
              if (passwordCard) {
                passwordCard.classList.remove('is-hidden');
              }
              closeModal();
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
            }
          })
          .catch(function () {
            modalError.textContent = 'Could not verify password right now. Please try again.';
            modalError.classList.remove('is-hidden');
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
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
