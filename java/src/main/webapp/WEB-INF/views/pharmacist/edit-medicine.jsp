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
    </head>

    <body>
      <div class="container">
        <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

          <main class="main-content">
            <header class="header">
              <div class="user-info">
                <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar"
                  class="avatar">
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
                  <span>&larr;</span> Back to Inventory
                </a>
              </div>

              <c:if test="${not empty medicine}">
                <form action="${pageContext.request.contextPath}/pharmacist/update-medicine" method="post"
                  enctype="multipart/form-data" class="styled-form">
                  <c:if test="${not empty error}">
                    <div class="alert alert-error"
                      style="grid-column: span 2; background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9em; border: 1px solid #fecaca;">
                      <span>&#9888;&#65039;</span>
                      ${error}
                    </div>
                  </c:if>

                  <!-- Hidden id -->
                  <input type="hidden" name="id" value="${medicine.id}" />
                  <input type="hidden" name="existing_image_path" value="${medicine.imagePath}">

                  <!-- Section: Basic Identification -->
                  <div class="form-section-title">
                    <span>&#8505;&#65039;</span> Basic Identification
                  </div>

                  <div class="form-group">
                    <label>Brand Name</label>
                    <input type="text" name="name" value="${medicine.name}" minlength="3" maxlength="100" required>
                  </div>

                  <div class="form-group">
                    <label>Generic Name</label>
                    <input type="text" name="generic_name" value="${medicine.genericName}" minlength="3" maxlength="100"
                      required>
                  </div>

                  <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                      <option value="">Select Category</option>
                      <c:forEach var="cat" items="${categories}">
                        <option value="${cat.id}" ${medicine.categoryId==cat.id || medicine.category==cat.name
                          ? 'selected' : '' }>${cat.name}</option>
                      </c:forEach>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Manufacturer</label>
                    <input type="text" name="manufacturer" value="${medicine.manufacturer}" maxlength="100">
                  </div>

                  <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" maxlength="500"
                      placeholder="Describe the medicine...">${medicine.description}</textarea>
                  </div>

                  <!-- Section: Form & Strength -->
                  <div class="form-section-title">
                    <span>&#128138;</span> Dosage & Presentation
                  </div>

                  <div class="form-group">
                    <label>Dosage Form</label>
                    <select name="dosage_form" required>
                      <option value="">Select Form</option>
                      <c:forEach var="df" items="${dosageForms}">
                        <option value="${df.name}" ${medicine.dosageForm==df.name ? 'selected' : '' }>${df.name}
                        </option>
                      </c:forEach>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Strength</label>
                    <input type="text" name="strength" placeholder="e.g. 500mg" value="${medicine.strength}" required>
                  </div>

                  <!-- Section: Inventory & Units -->
                  <div class="form-section-title">
                    <span>&#128230;</span> Inventory & Measurement
                  </div>

                  <div class="form-group">
                    <label>Selling Unit</label>
                    <select name="selling_unit" required>
                      <option value="">Select Selling Unit</option>
                      <c:forEach var="su" items="${sellingUnits}">
                        <option value="${su.name}" ${medicine.sellingUnit==su.name ? 'selected' : '' }>${su.name}
                        </option>
                      </c:forEach>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Doses per Unit</label>
                    <input type="number" name="unit_quantity" min="1" value="${medicine.unitQuantity}" required>
                  </div>

                  <div class="form-group">
                    <label>Current Stock (in Units)</label>
                    <input type="number" name="quantity_in_stock" min="0" value="${medicine.quantityInStock}" required>
                  </div>

                  <div class="form-group">
                    <label>Price per Unit (LKR)</label>
                    <input type="number" name="price" step="0.01" min="0.01" value="${medicine.price}" required>
                  </div>

                  <!-- Section: Logistics -->
                  <div class="form-section-title">
                    <span>&#128197;</span> Logistics & Media
                  </div>

                  <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="date" name="expiry_date" id="expiryDateInputEdit" value="${medicine.expiryDate}"
                      required>
                  </div>

                  <div class="form-group">
                    <label>Medicine Image</label>
                    <div style="display: flex; align-items: center; gap: 15px;">
                      <c:if test="${not empty medicine.imagePath}">
                        <img src="${medicine.imagePath}" alt="Current"
                          style="height: 46px; width: 46px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                      </c:if>
                      <input type="file" name="imageFile" accept="image/*" style="flex: 1;">
                    </div>
                  </div>

                  <div class="btn-group">
                    <button type="submit" class="btn-submit">Update Medicine Details</button>
                  </div>
                </form>
              </c:if>

            </div>
          </main>
      </div>
      <script>

        // Set min date for expiry date input to tomorrow
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        const expiryInput = document.getElementById('expiryDateInputEdit');
        if (expiryInput) {
          expiryInput.setAttribute('min', tomorrowStr);
        }
      </script>
    </body>

    </html>