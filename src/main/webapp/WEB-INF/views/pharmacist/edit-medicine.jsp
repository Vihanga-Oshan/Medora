<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Medicine</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/medicine-inventory.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/formstyles.css">
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
        <span class="greeting-icon">✏️</span>
        <div>
          <span class="greeting-text">Edit Medicine</span>
          <span class="date‑time">Modify medicine details</span>
        </div>
      </div>
    </header>

    <div class="form-section">
      <div class="section-header">
        <div>
          <h2>Edit Medicine</h2>
          <p>Modify the details of the medicine below.</p>
        </div>
        <a href="${pageContext.request.contextPath}/pharmacist/medicine-inventory" class="add-btn">
          <i data-lucide="arrow-left"></i> Back to Inventory
        </a>
      </div>

      <c:if test="${not empty medicine}">
      <form action="${pageContext.request.contextPath}/pharmacist/update-medicine" method="post" class="styled-form">
        <!-- Hidden id -->
        <input type="hidden" name="id" value="${medicine.id}" />

        <label>Brand Name</label>
        <input type="text" name="name" value="${medicine.name}" required>

        <label>Generic Name</label>
        <input type="text" name="generic_name" value="${medicine.genericName}" required>

        <label>Category</label>
        <input type="text" name="category" placeholder="e.g. Antibiotic, Painkiller" value="${medicine.category}" required>

        <label>Description</label>
        <textarea name="description" rows="2" placeholder="E.g. Used to treat fever and pain.">${medicine.description}</textarea>

        <label>Dosage Form</label>
        <input type="text" name="dosage_form" placeholder="Tablet, Capsule, Syrup..." value="${medicine.dosageForm}" required>

        <label>Strength</label>
        <input type="text" name="strength" placeholder="e.g. 500mg" value="${medicine.strength}" required>

        <label>Quantity In Stock</label>
        <input type="number" name="quantity_in_stock" min="0" value="${medicine.quantityInStock}" required>

        <label>Manufacturer</label>
        <input type="text" name="manufacturer" value="${medicine.manufacturer}">

        <label>Expiry Date</label>
        <input type="date" name="expiry_date" value="${medicine.expiryDate}“ required>

          <div class="btn-group">
        <button type="submit" class="btn-submit">Update Medicine</button>
    </div>
    </form>
    </c:if>

</div>
</main>
</div>
<script>
  lucide.createIcons();
</script>
</body>
</html>
