<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Add Medicine - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css" />
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/formstyles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="container">
  <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

  <main class="main-content">
    <!-- Header -->
    <header class="header">
      <div class="user-info">
        <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar" class="avatar">
        <span class="user-role">Pharmacist</span>
      </div>
      <div class="greeting">
        <span class="greeting-icon">💊</span>
        <div>
          <span class="greeting-text">Add New Medicine</span>
          <span class="date-time">${pageContext.request.contextPath}</span>
        </div>
      </div>
    </header>

    <!-- Form Section -->
    <section class="form-section">
      <div class="section-header">
        <div>
          <h2>Add Medicine to Inventory</h2>
          <p>Fill in all necessary fields to register a new medicine</p>
        </div>
        <button class="add-btn" onclick="window.location.href='${pageContext.request.contextPath}/pharmacist/medicine-inventory'">
          <i data-lucide='arrow-left'></i> Back to Inventory
        </button>
      </div>

      <form action="${pageContext.request.contextPath}/pharmacist/addMedicine" method="post" class="styled-form">
        <label>Brand Name</label>
        <input type="text" name="name" required>

        <label>Generic Name</label>
        <input type="text" name="generic_name" required>

        <label>Category</label>
        <input type="text" name="category" placeholder="e.g. Antibiotic, Painkiller" required>

        <label>Description</label>
        <textarea name="description" rows="2" placeholder="E.g. Used to treat fever and pain."></textarea>

        <label>Dosage Form</label>
        <input type="text" name="dosage_form" placeholder="Tablet, Capsule, Syrup..." required>

        <label>Strength</label>
        <input type="text" name="strength" placeholder="e.g. 500mg" required>

        <label>Quantity In Stock</label>
        <input type="number" name="quantity_in_stock" min="0" value="0" required>

        <label>Manufacturer</label>
        <input type="text" name="manufacturer">

        <label>Expiry Date</label>
        <input type="date" name="expiry_date" required>

        <div class="btn-group">
          <button type="submit" class="btn-submit">Add Medicine</button>
        </div>
      </form>
    </section>
  </main>
</div>

<script>
  lucide.createIcons();
</script>
</body>
</html>
