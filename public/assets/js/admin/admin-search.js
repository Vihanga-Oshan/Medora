(function () {
  function normalize(text) {
    return (text || '').toLowerCase().trim();
  }

  function ensureNoResultsRow(tbody, colspan) {
    var existing = tbody.querySelector('.admin-search-empty-row');
    if (existing) return existing;
    var tr = document.createElement('tr');
    tr.className = 'admin-search-empty-row';
    tr.style.display = 'none';
    var td = document.createElement('td');
    td.colSpan = colspan;
    td.textContent = 'No matching results found.';
    tr.appendChild(td);
    tbody.appendChild(tr);
    return tr;
  }

  function filterTable(table, query) {
    var tbody = table.querySelector('tbody');
    if (!tbody) return;

    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    var dataRows = rows.filter(function (row) {
      return !row.classList.contains('admin-search-empty-row') && row.children.length > 0;
    });

    var matches = 0;
    dataRows.forEach(function (row) {
      var hit = query === '' || normalize(row.textContent).indexOf(query) !== -1;
      if (hit) matches += 1;
      row.style.display = hit ? '' : 'none';
    });

    var cols = table.querySelectorAll('thead th').length || 1;
    var emptyRow = ensureNoResultsRow(tbody, cols);
    emptyRow.style.display = matches === 0 ? '' : 'none';
  }

  function filterCollection(elements, query) {
    if (!elements.length) return;
    elements.forEach(function (el) {
      var hit = query === '' || normalize(el.textContent).indexOf(query) !== -1;
      el.style.display = hit ? '' : 'none';
    });
  }

  function initAdminSearch() {
    var input = document.getElementById('admin-global-search');
    if (!input) return;

    var main = document.querySelector('.main-content') || document.body;
    var tables = Array.prototype.slice.call(main.querySelectorAll('table.data-table'));

    var cards = Array.prototype.slice.call(
      main.querySelectorAll('.stats-grid .card, .stats-row .stat-mini-card, .activity-list li, .status-list li')
    );

    var searchBar = input.closest('.search-bar');
    var info = document.createElement('small');
    info.className = 'admin-search-meta';
    info.style.marginLeft = '8px';
    info.style.color = '#6b7280';
    info.style.fontSize = '12px';
    info.textContent = '';
    if (searchBar && searchBar.parentNode) {
      searchBar.parentNode.appendChild(info);
    }

    function countVisible(rows) {
      return rows.reduce(function (acc, row) {
        return acc + (row.style.display === 'none' ? 0 : 1);
      }, 0);
    }

    input.addEventListener('input', function () {
      var q = normalize(input.value);
      tables.forEach(function (table) {
        filterTable(table, q);
      });
      filterCollection(cards, q);

      var visibleCards = countVisible(cards);
      var hasQuery = q.length > 0;
      if (info) {
        if (!hasQuery) {
          info.textContent = '';
        } else {
          info.textContent = visibleCards > 0 ? (visibleCards + ' matches') : 'No matches';
        }
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        input.value = '';
        input.dispatchEvent(new Event('input'));
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminSearch);
  } else {
    initAdminSearch();
  }
})();
