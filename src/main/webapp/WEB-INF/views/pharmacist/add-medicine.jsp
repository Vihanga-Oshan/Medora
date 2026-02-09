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
    </head>

    <body>

      <div class="container">
        <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

          <main class="main-content">
            <!-- Header -->
            <header class="header">
              <div class="user-info">
                <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar"
                  class="avatar">
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
                <button class="add-btn"
                  onclick="window.location.href='${pageContext.request.contextPath}/pharmacist/medicine-inventory'">
                  <span>&larr;</span> Back to Inventory
                </button>
              </div>

              <form action="${pageContext.request.contextPath}/pharmacist/addMedicine" method="post"
                enctype="multipart/form-data" class="styled-form">

                <c:if test="${not empty error}">
                  <div class="alert alert-error"
                    style="grid-column: span 2; background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9em; border: 1px solid #fecaca;">
                    <span>&#9888;&#65039;</span>
                    ${error}
                  </div>
                </c:if>

                <!-- Section: Basic Identification -->
                <span>&#8505;&#65039;</span> Basic Identification
      </div>

      <div class="form-group">
        <label>Brand Name</label>
        <input type="text" name="name" minlength="3" maxlength="100" placeholder="e.g. Panadol" required>
      </div>

      <div class="form-group">
        <label>Generic Name</label>
        <input type="text" name="generic_name" minlength="3" maxlength="100" placeholder="e.g. Paracetamol" required>
      </div>

      <div class="form-group">
        <label>Category</label>
        <div class="category-input-group">
          <select name="category_id" id="categorySelect" required>
            <option value="">Select Category</option>
            <c:forEach var="cat" items="${categories}">
              <option value="${cat.id}">${cat.name}</option>
            </c:forEach>
          </select>
          <button type="button" onclick="openCategoryModal()" class="category-add-btn" title="Add New Category">
            <span>+</span>
          </button>
        </div>
      </div>

      <div class="form-group">
        <label>Manufacturer</label>
        <input type="text" name="manufacturer" maxlength="100" placeholder="e.g. GSK">
      </div>

      <div class="form-group full-width">
        <label>Description</label>
        <textarea name="description" maxlength="500"
          placeholder="Describe the medicine and its primary uses..."></textarea>
      </div>

      <!-- Section: Form & Strength -->
      <span>&#128138;</span> Dosage & Presentation
      </div>

      <div class="form-group">
        <label>Dosage Form</label>
        <select name="dosage_form" required>
          <option value="">Select Form</option>
          <c:forEach var="df" items="${dosageForms}">
            <option value="${df.name}" ${medicine.dosageForm==df.name ? 'selected' : '' }>${df.name}</option>
          </c:forEach>
        </select>
      </div>

      <div class="form-group">
        <label>Strength</label>
        <input type="text" name="strength" placeholder="e.g. 500mg or 5ml" required>
      </div>

      <!-- Section: Inventory & Units -->
      <span>&#128230;</span> Inventory & Measurement
      </div>

      <div class="form-group">
        <label>Selling Unit</label>
        <select name="selling_unit" required>
          <option value="">Select Selling Unit</option>
          <c:forEach var="su" items="${sellingUnits}">
            <option value="${su.name}" ${medicine.sellingUnit==su.name ? 'selected' : '' }>${su.name}</option>
          </c:forEach>
        </select>
      </div>

      <div class="form-group">
        <label>Doses per Unit</label>
        <input type="number" name="unit_quantity" min="1" value="1" required>
      </div>

      <div class="form-group">
        <label>Current Stock (in Units)</label>
        <input type="number" name="quantity_in_stock" min="0" value="0" required>
      </div>

      <div class="form-group">
        <label>Price per Unit (LKR)</label>
        <input type="number" name="price" step="0.01" min="0.01" value="0.00" required>
      </div>

      <!-- Section: Logistics -->
      <span>&#128197;</span> Logistics & Media
      </div>

      <div class="form-group">
        <label>Expiry Date</label>
        <input type="date" name="expiry_date" id="expiryDateInput" required>
      </div>

      <div class="form-group">
        <label>Medicine Image</label>
        <input type="file" name="imageFile" accept="image/*">
      </div>

      <div class="btn-group">
        <button type="submit" class="btn-submit">Add Medicine to Inventory</button>
      </div>
      </form>
      </section>
      </main>
      </div>

      <!-- Add Category Modal -->
      <div id="categoryModal" class="modal"
        style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content"
          style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 300px; border-radius: 8px;">
          <h3>Add New Category</h3>
          <input type="text" id="newCategoryName" placeholder="Category Name"
            style="width: 100%; padding: 8px; margin: 10px 0;">
          <div style="text-align: right;">
            <button type="button" onclick="closeCategoryModal()"
              style="padding: 8px 12px; margin-right: 5px;">Cancel</button>
            <button type="button" onclick="submitCategory()"
              style="padding: 8px 12px; background: #007dca; color: white; border: none; border-radius: 4px;">Add</button>
          </div>
        </div>
      </div>

      <script>

        function openCategoryModal() {
          document.getElementById('categoryModal').style.display = 'block';
        }

        function closeCategoryModal() {
          document.getElementById('categoryModal').style.display = 'none';
        }

        function submitCategory() {
          const name = document.getElementById('newCategoryName').value;
          if (!name) return alert("Category name is required");

          fetch('${pageContext.request.contextPath}/pharmacist/add-category', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'name=' + encodeURIComponent(name)
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                const select = document.getElementById('categorySelect');
                const option = new Option(data.name, data.id);
                select.add(option);
                select.value = data.id; // Auto-select new category
                closeCategoryModal();
              } else {
                alert(data.error || "Failed to add category");
              }
            })
            .catch(err => alert("Error: " + err));
        }
      </script>
    </body>

    </html>