document.addEventListener('DOMContentLoaded', function () {
  const loginForm =
    document.getElementById('loginForm') ||
    document.getElementById('guardianLoginForm') ||
    document.getElementById('counselorLoginForm') ||
    document.getElementById('adminLoginForm');

  if (!loginForm) return;

  const emailInput = loginForm.querySelector('#email');
  const nicInput = loginForm.querySelector('#nic');
  const passwordInput = loginForm.querySelector('#password');
  const passwordToggle = loginForm.querySelector('#passwordToggle');
  const loginBtn =
    loginForm.querySelector('.form-submit-btn') ||
    loginForm.querySelector('.btn-submit');

  function showError(input, message) {
    clearError(input);
    input.style.borderColor = 'var(--color-error, #f44336)';
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
  }

  function clearError(input) {
    const existingError = input.parentNode.querySelector('.field-error');
    if (existingError) existingError.remove();
    input.style.borderColor = '';
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  if (passwordToggle && passwordInput) {
    passwordToggle.addEventListener('click', function () {
      const type =
        passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      const svg = passwordToggle.querySelector('svg');
      if (svg) {
        if (type === 'text') {
          svg.innerHTML =
            '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
          svg.innerHTML =
            '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
      }
    });
  }

  if (emailInput) {
    emailInput.addEventListener('blur', function () {
      if (this.value && !validateEmail(this.value)) {
        showError(this, 'Please enter a valid email address');
      }
    });
    emailInput.addEventListener('input', function () {
      clearError(this);
    });
  }

  if (nicInput) {
    nicInput.addEventListener('input', function () {
      clearError(this);
    });
  }

  if (passwordInput) {
    passwordInput.addEventListener('input', function () {
      clearError(this);
    });
  }

  loginForm.addEventListener('submit', function (e) {
    let isValid = true;

    if (emailInput) {
      const email = emailInput.value.trim();
      if (!validateEmail(email)) {
        showError(emailInput, 'Valid email is required');
        isValid = false;
      }
    }

    if (nicInput) {
      const nic = nicInput.value.trim();
      if (!nic) {
        showError(nicInput, 'NIC is required');
        isValid = false;
      }
    }

    if (passwordInput) {
      const password = passwordInput.value;
      if (!password) {
        showError(passwordInput, 'Password is required');
        isValid = false;
      }
    }

    if (!isValid) {
      e.preventDefault();
      return;
    }

    if (loginBtn) {
      const originalText = loginBtn.textContent;
      loginBtn.textContent = originalText.toLowerCase().includes('access')
        ? 'Accessing Portal...'
        : 'Logging in...';
      loginBtn.disabled = true;
    }
  });
});
