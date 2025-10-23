<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medicine Inventory</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/medicine-inventory.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<div class="container">
  <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

  <main class="main-content">
    <header class="header">
      <div class="user-info">
        <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar" class="avatar">
        <span class="user-role">Super Pharmacist</span>
      </div>
      <div class="greeting">
        <span class="greeting-icon">📦</span>
        <div>
          <span class="greeting-text">Medicine Inventory</span>
          <span class="date-time">Current stock and details</span>
        </div>
      </div>
    </header>

    <div class="inventory-section">
      <div class="section-header">
        <div>
          <h2>Existing Medicines</h2>
          <p>Review the inventory of all medicines in the system</p>
        </div>
        <a href="${pageContext.request.contextPath}/pharmacist/addMedicine" class="add-btn">
          <i data-lucide="plus"></i> Add Medicine
        </a>
      </div>

      <div class="search-box">
        <i data-lucide="search"></i>
        <input type="text" placeholder="Search by name, category...">
      </div>

      <c:if test="${not empty medicineList}">
        <table class="data-table small-table">
          <thead>
          <tr>
            <th>Brand</th>
            <th>Generic</th>
            <th>Category</th>
            <th>Strength</th>
            <th>Form</th>
            <th>Qty</th>
            <th>Manufacturer</th>
            <th>Expiry</th>
            <th>Actions</th>
          </tr>
          </thead>
          <tbody>
          <c:forEach var="m" items="${medicineList}">
            <tr>
              <td>${m.name}</td>
              <td>${m.genericName}</td>
              <td>${m.category}</td>
              <td>${m.strength}</td>
              <td>${m.dosageForm}</td>
              <td>${m.quantityInStock}</td>
              <td>${m.manufacturer}</td>
              <td>${m.expiryDate}</td>
              <td>
                <button class="action-btn" onclick="openActionsMenu(this)">
                  <i data-lucide="more-vertical"></i>
                </button>
                <div class="action-menu hidden">
                  <ul>
                    <li class="edit" onclick="window.location.href='${pageContext.request.contextPath}/pharmacist/edit-medicine?id=${m.id}'">
                      <i data-lucide='edit-3'></i> Edit
                    </li>
                    <li class="delete" onclick="confirmDelete('${m.id}')">
                      <i data-lucide="trash-2"></i> Delete
                    </li>
                  </ul>
                </div>
              </td>
            </tr>
          </c:forEach>
          </tbody>
        </table>
      </c:if>
    </div>
  </main>
</div>
<div id="deleteModal" class="modal hidden">
  <div class="modal-content">
    <h3>Confirm Deletion</h3>
    <p>Are you sure you want to delete this medicine?</p>
    <div class="modal-actions">
      <button class="cancel-btn" onclick="closeModal()">Cancel</button>
      <form id="deleteForm" method="post">
        <button type="submit" class="delete-btn">Yes, Delete</button>
      </form>
    </div>
  </div>
</div>


<script>
  lucide.createIcons();

  const contextPath = '<%= request.getContextPath() %>';

  function confirmDelete(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');
    form.action = contextPath + '/pharmacist/delete-medicine?id=' + id;
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
