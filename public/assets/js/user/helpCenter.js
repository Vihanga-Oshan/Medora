const supportServices = Array.isArray(window.supportServices) ? window.supportServices : [];

let currentPage = 1;
const itemsPerPage = 6;
let filteredServices = [...supportServices];

let servicesGrid;
let helpSearch;
let categoryFilter;
let typeFilter;
let pagination;
let prevBtn;
let nextBtn;
let paginationNumbers;

document.addEventListener('DOMContentLoaded', function () {
  initializeElements();
  setupEventListeners();
  renderServices();
  updatePagination();
});

function initializeElements() {
  servicesGrid = document.getElementById('servicesGrid');
  helpSearch = document.getElementById('helpSearch');
  categoryFilter = document.getElementById('categoryFilter');
  typeFilter = document.getElementById('typeFilter');
  pagination = document.getElementById('helpPagination');
  prevBtn = document.getElementById('prevBtn');
  nextBtn = document.getElementById('nextBtn');
  paginationNumbers = document.getElementById('paginationNumbers');
}

function setupEventListeners() {
  if (helpSearch) {
    helpSearch.addEventListener('input', debounce(handleSearch, 300));
  }

  if (categoryFilter) {
    categoryFilter.addEventListener('change', handleFilter);
  }

  if (typeFilter) {
    typeFilter.addEventListener('change', handleFilter);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => changePage(currentPage - 1));
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => changePage(currentPage + 1));
  }

  setupEmergencyActions();
  setupQuickActions();

  window.showCenterDetails = function (centerId) {
    showCenterDetails(centerId);
  };

  window.closeCenterModal = function () {
    const modal = document.getElementById('centerDetailsModal');
    if (modal) modal.style.display = 'none';
  };

  window.addEventListener('click', function (event) {
    const modal = document.getElementById('centerDetailsModal');
    if (modal && event.target === modal) {
      modal.style.display = 'none';
    }
  });
}

function handleSearch() {
  const searchTerm = (helpSearch?.value || '').toLowerCase().trim();

  if (searchTerm === '') {
    filteredServices = [...supportServices];
  } else {
    filteredServices = supportServices.filter(
      (service) =>
        String(service.title || '').toLowerCase().includes(searchTerm) ||
        String(service.description || '').toLowerCase().includes(searchTerm) ||
        String(service.category || '').toLowerCase().includes(searchTerm)
    );
  }

  currentPage = 1;
  renderServices();
  updatePagination();
}

function handleFilter() {
  const categoryValue = categoryFilter ? categoryFilter.value : 'all';
  const typeValue = typeFilter ? typeFilter.value : 'all';

  filteredServices = supportServices.filter((service) => {
    const serviceCategory = String(service.category || '').toLowerCase();
    const serviceType = String(service.type || '').toLowerCase();
    const categoryMatch = categoryValue === 'all' || serviceCategory === categoryValue;
    const typeMatch = typeValue === 'all' || serviceType === typeValue;
    return categoryMatch && typeMatch;
  });

  if (helpSearch && helpSearch.value.trim() !== '') {
    const searchTerm = helpSearch.value.toLowerCase().trim();
    filteredServices = filteredServices.filter(
      (service) =>
        String(service.title || '').toLowerCase().includes(searchTerm) ||
        String(service.description || '').toLowerCase().includes(searchTerm) ||
        String(service.category || '').toLowerCase().includes(searchTerm)
    );
  }

  currentPage = 1;
  renderServices();
  updatePagination();
}

function renderServices() {
  if (!servicesGrid) return;

  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const servicesToShow = filteredServices.slice(startIndex, endIndex);

  if (servicesToShow.length === 0) {
    servicesGrid.innerHTML = `
      <div class="empty-state" style="grid-column: 1 / -1;">
        <i data-lucide="search-x" style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;"></i>
        <h3>No services found</h3>
        <p>Try adjusting your search or filters</p>
      </div>
    `;
    if (typeof lucide !== 'undefined') lucide.createIcons();
    return;
  }

  servicesGrid.innerHTML = servicesToShow
    .map(
      (service) => `
      <div class="service-card" data-service-id="${service.id}">
        <div class="service-header">
          <div class="service-info">
            <h4>${escapeHtml(service.title || '')}</h4>
            ${service.organization ? `<p class="organization-name">${escapeHtml(service.organization)}</p>` : ''}
          </div>
          <span class="service-category">${escapeHtml(service.category || '')}</span>
        </div>
        <p class="service-description">${escapeHtml(service.description || '')}</p>
        <div class="service-meta">
          <span class="service-availability status-${escapeHtml(service.status || 'available')}">${escapeHtml(service.availability || 'Available')}</span>
          <span class="service-type">${formatServiceType(service.type)}</span>
        </div>
        <div class="service-actions">
          <button class="btn btn-primary service-contact-btn" data-service-id="${service.id}">${escapeHtml(service.contact || 'Contact')}</button>
          <button class="btn btn-link service-details-btn" data-service-id="${service.id}">View Details</button>
        </div>
      </div>
    `
    )
    .join('');

  if (typeof lucide !== 'undefined') lucide.createIcons();
  setupServiceCardListeners();
}

function formatServiceType(type) {
  const t = String(type || '').toLowerCase();
  const typeMap = {
    hotline: 'Phone Support',
    chat: 'Live Chat',
    appointment: 'Appointment',
    resources: 'Self-Help Resources',
  };
  return typeMap[t] || type || '';
}

function setupServiceCardListeners() {
  document.querySelectorAll('.service-contact-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const serviceId = parseInt(this.dataset.serviceId, 10);
      handleServiceContact(serviceId);
    });
  });

  document.querySelectorAll('.service-details-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const serviceId = parseInt(this.dataset.serviceId, 10);
      showCenterDetails(serviceId);
    });
  });
}

function handleServiceContact(serviceId) {
  const service = supportServices.find((s) => Number(s.id) === Number(serviceId));
  if (!service) return;

  if (service.phoneNumber) {
    window.location.href = `tel:${service.phoneNumber}`;
    return;
  }
  if (service.website) {
    window.open(service.website, '_blank');
    return;
  }
  if (service.email) {
    window.location.href = `mailto:${service.email}`;
    return;
  }

  alert(`Contacting ${service.title}...`);
}

function showCenterDetails(serviceId) {
  const center = supportServices.find((s) => Number(s.id) === Number(serviceId));
  if (!center) return;

  const modal = document.getElementById('centerDetailsModal');
  const modalName = document.getElementById('modalCenterName');
  const modalContent = document.getElementById('modalCenterContent');
  if (!modal || !modalName || !modalContent) return;

  modalName.textContent = center.title || 'Center Details';

  const location = [center.address, center.city, center.state, center.zipCode]
    .filter((part) => part && String(part).trim() !== '')
    .join(', ');

  modalContent.innerHTML = `
    <div class="center-details">
      <div class="detail-section">
        <h4>Contact Information</h4>
        ${center.phoneNumber ? `<p><strong>Phone:</strong> <a href="tel:${escapeHtml(center.phoneNumber)}">${escapeHtml(center.phoneNumber)}</a></p>` : ''}
        ${center.email ? `<p><strong>Email:</strong> <a href="mailto:${escapeHtml(center.email)}">${escapeHtml(center.email)}</a></p>` : ''}
        ${center.website ? `<p><strong>Website:</strong> <a href="${escapeHtml(center.website)}" target="_blank" rel="noopener">${escapeHtml(center.website)}</a></p>` : ''}
      </div>

      ${location ? `<div class="detail-section"><h4>Location</h4><p>${escapeHtml(location)}</p></div>` : ''}

      <div class="detail-section">
        <h4>Service Details</h4>
        <p><strong>Type:</strong> ${escapeHtml(formatServiceType(center.type))}</p>
        <p><strong>Category:</strong> ${escapeHtml(center.category || 'Not specified')}</p>
        ${center.availability ? `<p><strong>Availability:</strong> ${escapeHtml(center.availability)}</p>` : ''}
        ${center.organization ? `<p><strong>Organization:</strong> ${escapeHtml(center.organization)}</p>` : ''}
      </div>

      ${center.description ? `<div class="detail-section"><h4>Description</h4><p>${escapeHtml(center.description)}</p></div>` : ''}
      ${center.specialties ? `<div class="detail-section"><h4>Specialties</h4><p>${escapeHtml(center.specialties)}</p></div>` : ''}
    </div>
  `;

  modal.style.display = 'block';
}

function updatePagination() {
  const totalPages = Math.ceil(filteredServices.length / itemsPerPage);

  if (totalPages <= 1) {
    if (pagination) pagination.style.display = 'none';
    return;
  }

  if (pagination) pagination.style.display = 'flex';
  if (prevBtn) prevBtn.disabled = currentPage === 1;
  if (nextBtn) nextBtn.disabled = currentPage === totalPages;

  if (paginationNumbers) {
    paginationNumbers.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', () => changePage(i));
      paginationNumbers.appendChild(pageBtn);
    }
  }
}

function changePage(page) {
  const totalPages = Math.ceil(filteredServices.length / itemsPerPage);
  if (page < 1 || page > totalPages) return;

  currentPage = page;
  renderServices();
  updatePagination();

  if (servicesGrid) {
    servicesGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}

function setupEmergencyActions() {
  document.querySelectorAll('.emergency-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const card = this.closest('.emergency-card');
      const title = card?.querySelector('h4')?.textContent || '';

      if (title.includes('Crisis Hotline')) {
        window.location.href = 'tel:1-800-273-8255';
      } else if (title.includes('Text Crisis')) {
        alert('Text "HOME" to 741741');
      } else {
        alert('Connecting to emergency chat...');
      }
    });
  });
}

function setupQuickActions() {
  document.querySelectorAll('.action-card .btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const actionCard = this.closest('.action-card');
      const actionTitle = actionCard?.querySelector('h4')?.textContent || '';

      switch (actionTitle) {
        case 'Schedule Session':
          window.location.href = '/user/sessions';
          break;
        case 'Join Support Group':
          window.location.href = '/user/community';
          break;
        case 'Recovery Resources':
          window.location.href = '/user/recovery';
          break;
        case 'Technical Support':
          alert('Opening technical support chat...');
          break;
        default:
          alert(`Accessing ${actionTitle}...`);
      }
    });
  });
}

function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\"/g, '&quot;')
    .replace(/'/g, '&#39;');
}
