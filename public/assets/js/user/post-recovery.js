(() => {
  document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initJobCardInteractions();
    initFilter();
  });

  function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchButton = document.querySelector('.search-button');
    const state = window.postRecoveryState || {};

    if (!searchInput) return;

    searchInput.value = state.searchQuery || '';

    const submitSearch = () => {
      const q = (searchInput.value || '').trim();
      const params = new URLSearchParams(window.location.search);
      if (q) {
        params.set('q', q);
      } else {
        params.delete('q');
      }
      params.set('page', '1');
      window.location.href = '/user/post-recovery?' + params.toString();
    };

    if (searchButton) {
      searchButton.addEventListener('click', submitSearch);
    }

    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        submitSearch();
      }
    });
  }

  function initJobCardInteractions() {
    const saveButtons = document.querySelectorAll('.save-btn');
    saveButtons.forEach((button) => {
      button.addEventListener('click', async (e) => {
        e.preventDefault();
        await toggleSave(button);
      });
    });

    const applyButtons = document.querySelectorAll('.apply-btn');
    applyButtons.forEach((button) => {
      if (button.tagName.toLowerCase() === 'button') {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          handleApply(button);
        });
      }
    });

    const jobCards = document.querySelectorAll('.job-card');
    jobCards.forEach((card) => {
      card.addEventListener('click', (e) => {
        if (!e.target.closest('button') && !e.target.closest('a')) {
          handleJobCardClick(card);
        }
      });
    });
  }

  async function toggleSave(button) {
    const jobId = Number(button.getAttribute('data-job-id'));
    const state = window.postRecoveryState || {};

    if (!jobId || !state.saveToggleUrl) return;

    try {
      const response = await fetch(state.saveToggleUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ jobId })
      });
      const data = await response.json();
      if (!data || !data.success) {
        showNotification('Could not update saved job', 'error');
        return;
      }

      if (data.saved) {
        button.classList.add('saved', 'btn-secondary');
        button.classList.remove('btn-outline');
        button.textContent = 'Saved';
        showNotification('Job saved successfully!', 'success');
      } else {
        button.classList.remove('saved', 'btn-secondary');
        button.classList.add('btn-outline');
        button.textContent = 'Save';
        showNotification('Job removed from saved jobs', 'info');

        const params = new URLSearchParams(window.location.search);
        if (params.get('my') === '1') {
          button.closest('.job-card')?.remove();
        }
      }
    } catch (err) {
      showNotification('Could not update saved job', 'error');
    }
  }

  function handleApply(button) {
    const jobCard = button.closest('.job-card');
    const jobTitle = jobCard?.querySelector('.job-title')?.textContent || 'job';
    button.textContent = 'Applied';
    button.classList.remove('btn-primary');
    button.classList.add('btn-secondary');
    button.disabled = true;
    showNotification(`Application intent recorded for ${jobTitle}`, 'success');
  }

  function handleJobCardClick(card) {
    card.style.transform = 'scale(0.98)';
    setTimeout(() => {
      card.style.transform = 'translateY(-2px)';
    }, 100);
  }

  function initFilter() {
    const filterButton = document.querySelector('.filter-button');
    if (filterButton) {
      filterButton.addEventListener('click', () => {
        showNotification('Filter options coming soon!', 'info');
      });
    }
  }

  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: ${
        type === 'success'
          ? 'var(--color-primary)'
          : type === 'error'
          ? '#e74c3c'
          : 'var(--color-secondary)'
      };
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      z-index: 1000;
      animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(notification);
    setTimeout(() => {
      notification.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  if (!document.querySelector('#post-recovery-animations')) {
    const style = document.createElement('style');
    style.id = 'post-recovery-animations';
    style.textContent = `
      @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
      @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
      }
    `;
    document.head.appendChild(style);
  }
})();
