document.addEventListener('DOMContentLoaded', function () {
  const moodItems = document.querySelectorAll('.mood-item');
  moodItems.forEach(function (item) {
    item.addEventListener('click', function () {
      moodItems.forEach(function (m) { m.classList.remove('active'); });
      item.classList.add('active');
    });
  });

  const saveReflectionBtn = document.querySelector('.save-reflection-btn');
  const reflectionTextarea = document.querySelector('.reflection-textarea');
  const recentReflections = document.querySelector('.recent-reflections');

  if (saveReflectionBtn && reflectionTextarea && recentReflections) {
    saveReflectionBtn.addEventListener('click', function () {
      const text = reflectionTextarea.value.trim();
      const activeMood = document.querySelector('.mood-item.active .mood-emoji');
      const mood = activeMood ? activeMood.textContent : '';

      if (!text) {
        showNotification('Please write something before saving', 'error');
        return;
      }

      const newReflection = document.createElement('div');
      newReflection.className = 'reflection-item';
      newReflection.innerHTML = '<span class="reflection-date">Today ' + mood + ': ' + escapeHtml(text) + '</span>';

      const recentTitle = recentReflections.querySelector('.recent-title');
      if (recentTitle) {
        recentTitle.insertAdjacentElement('afterend', newReflection);
      } else {
        recentReflections.prepend(newReflection);
      }

      reflectionTextarea.value = '';
      showNotification('Reflection saved successfully!', 'success');
    });
  }

  bindClick('.log-progress-btn', function (e) {
    e.preventDefault();
    showProgressModal();
  });

  bindClick('.view-analytics-btn', function () {
    showNotification('Opening detailed analytics dashboard...', 'info');
  });

  bindClick('.view-all-progress-btn', function () {
    showNotification('Opening comprehensive progress tracker...', 'info');
  });

  document.querySelectorAll('.tool-item').forEach(function (tool) {
    tool.addEventListener('click', function () {
      const toolName = (tool.querySelector('.tool-name') || {}).textContent || 'tool';
      showNotification('Opening ' + toolName + '...', 'info');
    });
  });

  bindClickAll('.review-btn', function () {
    showNotification('Recovery plan review opened', 'info');
  });

  bindClickAll('.create-plan-btn', function () {
    showNotification('Recovery plan opened', 'info');
  });

  bindClick('.join-session-btn', function () {
    showNotification('Joining session...', 'success');
  });

  bindClick('.request-feedback-btn', function () {
    showNotification('Feedback request sent to counselor', 'success');
  });

  // Fallback: if buttons/forms are wired to the current page as placeholders,
  // provide visible feedback instead of silent "no-op" reloads.
  wireRecoveryPlaceholders();

  animateProgressBars();
});

function bindClick(selector, handler) {
  const el = document.querySelector(selector);
  if (el) el.addEventListener('click', handler);
}

function bindClickAll(selector, handler) {
  const els = document.querySelectorAll(selector);
  els.forEach(function (el) {
    el.addEventListener('click', handler);
  });
}

function wireRecoveryPlaceholders() {
  const currentPath = window.location.pathname;

  document.querySelectorAll('.recovery-container a.btn').forEach(function (link) {
    link.addEventListener('click', function (e) {
      const href = link.getAttribute('href') || '';
      if (!href || href === '#' || href === currentPath || href === '/user/recovery') {
        e.preventDefault();
        showNotification('This action is not wired yet in PHP. I can implement this route next.', 'info');
      }
    });
  });

  document.querySelectorAll('.recovery-container form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      const action = form.getAttribute('action') || '';
      if (!action || action === currentPath || action === '/user/recovery') {
        e.preventDefault();
        showNotification('This form action is not wired yet in PHP. I can implement it next.', 'info');
      }
    });
  });
}

function animateProgressBars() {
  const progressBars = document.querySelectorAll('.progress-fill');
  progressBars.forEach(function (bar) {
    const width = bar.style.width;
    bar.style.width = '0%';
    setTimeout(function () {
      bar.style.width = width;
    }, 300);
  });
}

function showNotification(message, type) {
  const existing = document.querySelector('.notification');
  if (existing) existing.remove();

  const notification = document.createElement('div');
  notification.className = 'notification notification-' + (type || 'info');
  notification.innerHTML = '<span class="notification-message">' + escapeHtml(message) + '</span><button class="notification-close">&times;</button>';

  notification.style.cssText = [
    'position: fixed',
    'top: 20px',
    'right: 20px',
    'padding: 16px 20px',
    'border-radius: 8px',
    'color: white',
    'font-weight: 500',
    'z-index: 1000',
    'display: flex',
    'align-items: center',
    'gap: 12px',
    'min-width: 300px',
    'animation: slideIn 0.3s ease'
  ].join(';');

  if (type === 'success') notification.style.backgroundColor = '#10b981';
  else if (type === 'error') notification.style.backgroundColor = '#ef4444';
  else notification.style.backgroundColor = '#3b82f6';

  const closeBtn = notification.querySelector('.notification-close');
  if (closeBtn) {
    closeBtn.style.cssText = 'background:none;border:none;color:white;font-size:18px;cursor:pointer;padding:0;margin-left:auto;';
    closeBtn.addEventListener('click', function () { notification.remove(); });
  }

  document.body.appendChild(notification);
  setTimeout(function () {
    if (notification.parentNode) notification.remove();
  }, 5000);
}

function showProgressModal(defaultType) {
  let modal = document.querySelector('.progress-modal.dynamic-progress-modal');
  if (!modal) {
    modal = createProgressModal();
    document.body.appendChild(modal);
  }

  const typeSelect = modal.querySelector('#progress-type');
  if (defaultType && typeSelect) typeSelect.value = defaultType;

  modal.classList.add('active');
}

function createProgressModal() {
  const modal = document.createElement('div');
  modal.className = 'progress-modal dynamic-progress-modal';
  modal.innerHTML = [
    '<div class="modal-content">',
    '  <div class="modal-header">',
    '    <h3 class="modal-title">Log Your Progress</h3>',
    '    <button class="modal-close">&times;</button>',
    '  </div>',
    '  <form class="log-form">',
    '    <div class="form-group">',
    '      <label class="form-label">Progress Type</label>',
    '      <select class="form-select" id="progress-type">',
    '        <option value="mood">Mood Check-in</option>',
    '        <option value="urge">Urge/Craving</option>',
    '        <option value="milestone">Milestone</option>',
    '        <option value="setback">Setback</option>',
    '      </select>',
    '    </div>',
    '    <div class="form-group">',
    '      <label class="form-label">Notes</label>',
    '      <textarea class="form-textarea" id="progress-notes" placeholder="How are you feeling today?"></textarea>',
    '    </div>',
    '    <div class="progress-actions">',
    '      <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>',
    '      <button type="submit" class="btn btn-primary">Save Progress</button>',
    '    </div>',
    '  </form>',
    '</div>'
  ].join('');

  const closeBtn = modal.querySelector('.modal-close');
  const cancelBtn = modal.querySelector('.modal-cancel');
  const form = modal.querySelector('.log-form');

  if (closeBtn) closeBtn.addEventListener('click', function () { modal.classList.remove('active'); });
  if (cancelBtn) cancelBtn.addEventListener('click', function () { modal.classList.remove('active'); });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      modal.classList.remove('active');
      form.reset();
      showNotification('Progress logged successfully!', 'success');
    });
  }

  modal.addEventListener('click', function (e) {
    if (e.target === modal) modal.classList.remove('active');
  });

  return modal;
}

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

(function ensureAnimationStyle() {
  if (document.getElementById('recovery-notification-anim')) return;
  const style = document.createElement('style');
  style.id = 'recovery-notification-anim';
  style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }';
  document.head.appendChild(style);
})();
