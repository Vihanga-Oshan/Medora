<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>

    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Pharmacist Management - Medora</title>
      <link rel="stylesheet" href="${pageContext.request.contextPath}/css/admin/admin-style.css" />
    </head>

    <body>

      <!-- Sidebar -->
      <aside class="sidebar">
        <div class="logo">
          <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medora Logo">
          <span class="logo-text">Medora Admin</span>
        </div>

        <ul class="nav-links">
          <li>
            <a href="${pageContext.request.contextPath}/admin/dashboard">
              <span>&#128202;</span>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="active">
            <a href="${pageContext.request.contextPath}/admin/pharmacists">
              <span>&#128104;&#8205;&#9877;&#65039;</span>
              <span>Pharmacists</span>
            </a>
          </li>
          <li>
            <a href="${pageContext.request.contextPath}/admin/settings">
              <span>&#9881;&#65039;</span>
              <span>Settings</span>
            </a>
          </li>
        </ul>

        <div class="admin-profile">
          <div class="profile-icon">AD</div>
          <div class="profile-info">
            <p class="name">Admin User</p>
            <p class="email">admin@medora.com</p>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="main-content">
        <header class="topbar">
          <div class="search-bar">
            <span>&#128269;</span>
            <input type="text" placeholder="Search users, pharmacists..." />
          </div>
          <div class="top-icons">
            <span>&#128276;</span>
          </div>
        </header>

        <section class="pharmacist-section">
          <div class="section-header">
            <div>
              <h1>Pharmacist Management</h1>
              <p>Manage pharmacist accounts and permissions</p>
            </div>
            <button class="add-btn"
              onclick="window.location.href='${pageContext.request.contextPath}/admin/add-pharmacist'">
              <span>+</span> Add Pharmacist
            </button>
          </div>

          <div class="stats-row">
            <div class="stat-card">
              <h3>Total Pharmacists</h3>
              <h2>127</h2>
            </div>
            <div class="stat-card">
              <h3>Active</h3>
              <h2>98</h2>
            </div>
            <div class="stat-card">
              <h3>Deleted</h3>
              <h2>5</h2>
            </div>
          </div>

          <div class="search-filter">
            <div class="search-box">
              <span>&#128269;</span>
              <input type="text" placeholder="Search by name, email, or license..." />
            </div>
          </div>

          <table class="pharmacist-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>License</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <c:forEach var="pharmacist" items="${pharmacists}">
                <tr>
                  <td>${pharmacist.name}</td>
                  <td>${pharmacist.email}</td>
                  <td>MEDORA-${pharmacist.id}</td>
                  <td>
                    <button class="action-btn" onclick="openActionsMenu(this)">
                      &#8942;
                    </button>
                    <div class="action-menu hidden">
                      <ul>
                        <li class="edit"
                          onclick="window.location.href='${pageContext.request.contextPath}/admin/edit-pharmacist?id=${pharmacist.id}'">
                          &#9998;
                          Edit Details
                        </li>
                        <li class="delete" onclick="confirmDelete('${pharmacist.id}')">
                          &#128465;
                          Delete
                        </li>
                      </ul>
                    </div>
                  </td>
                </tr>
              </c:forEach>
            </tbody>

          </table>
        </section>
      </main>
      <div id="deleteModal" class="modal hidden">
        <div class="modal-content">
          <h3>Confirm Deletion</h3>
          <p>Are you sure you want to delete this pharmacist?</p>
          <div class="modal-actions">
            <button class="cancel-btn" onclick="closeModal()">Cancel</button>
            <form id="deleteForm" method="post">
              <button type="submit" class="delete-btn">Yes, Delete</button>
            </form>
          </div>
        </div>
      </div>

      <script>

        const contextPath = '<%= request.getContextPath() %>';

        function confirmDelete(id) {
          const modal = document.getElementById('deleteModal');
          const form = document.getElementById('deleteForm');
          form.action = contextPath + '/admin/pharmacists/delete?id=' + id;
          modal.classList.remove('hidden');
        }

        function closeModal() {
          document.getElementById('deleteModal').classList.add('hidden');
        }

        function openActionsMenu(button) {
          const menu = button.nextElementSibling;
          document.querySelectorAll('.action-menu').forEach(m => {
            if (m !== menu) m.classList.add('hidden');
          });
          menu.classList.toggle('hidden');
        }

        document.addEventListener('click', e => {
          if (!e.target.closest('.action-btn') && !e.target.closest('.action-menu')) {
            document.querySelectorAll('.action-menu').forEach(m => m.classList.add('hidden'));
          }
        });
      </script>

    </body>

    </html>