<%@ page contentType="text/html;charset=UTF-8" language="java" %>
  <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Prescription Review - Medora</title>
      <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/prescription-review.css">
    </head>

    <body>
      <!-- Include Sidebar Component -->
      <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

        <main class="main">
          <div class="top-bar">
            <h1>Prescription Review</h1>
            <div class="user-info">
              <span class="pharmacist-name">${sessionScope.pharmacist.name}</span>
              <img src="${pageContext.request.contextPath}/assets/avatar.png" alt="Pharmacist" class="avatar">
            </div>
          </div>

          <div class="prescription-grid">
            <c:forEach items="${pendingPrescriptions}" var="prescription">
              <div class="prescription-card" data-id="${prescription.id}">
                <div class="prescription-header">
                  <span class="patient-name">${prescription.patientName}</span>
                  <span class="upload-date">${prescription.formattedUploadDate}</span>
                </div>
                <div class="prescription-image">
                  <img src="${pageContext.request.contextPath}/prescriptionFile/${prescription.imagePath}"
                    alt="Prescription" onclick="openImageModal(this.src)">
                </div>
                <div class="patient-details">
                  <p><strong>Patient ID:</strong> ${prescription.patientNic}</p>
                  <p><strong>Allergies:</strong> ${prescription.patientAllergies}</p>
                  <p><strong>Chronic Conditions:</strong> ${prescription.patientChronicIssues}</p>
                </div>
                <div class="prescription-actions">
                  <textarea placeholder="Add notes for patient..." class="pharmacist-note"></textarea>
                  <div class="action-buttons">
                    <button class="approve-btn" onclick="reviewPrescription('${prescription.id}', 'APPROVED')">
                      &#10003; Approve
                    </button>
                    <button class="reject-btn" onclick="reviewPrescription('${prescription.id}', 'REJECTED')">
                      &#10007; Reject
                    </button>
                  </div>
                </div>
              </div>
            </c:forEach>
          </div>
        </main>

        <!-- Image Modal -->
        <div id="imageModal" class="modal">
          <span class="close-modal">&times;</span>
          <img id="modalImage" src="" alt="Prescription Large View">
        </div>

        <script>
          function openImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            modal.style.display = "flex";
            modalImg.src = imageSrc;
          }

          document.querySelector('.close-modal').onclick = function () {
            document.getElementById('imageModal').style.display = "none";
          }

          function reviewPrescription(prescriptionId, status) {
            const card = document.querySelector(`[data-id="${prescriptionId}"]`);
            const note = card.querySelector('.pharmacist-note').value;

            fetch('${pageContext.request.contextPath}/pharmacist/prescription/review', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
              },
              body: `prescriptionId=${prescriptionId}&status=${status}&note=${encodeURIComponent(note)}`
            })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  card.classList.add('fade-out');
                  setTimeout(() => card.remove(), 300);
                  showNotification(status === 'APPROVED' ? 'Prescription approved' : 'Prescription rejected');
                } else {
                  showNotification('Error: ' + data.error, true);
                }
              })
              .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred while processing the prescription', true);
              });
          }

          function showNotification(message, isError = false) {
            const notification = document.createElement('div');
            notification.className = `notification ${isError ? 'error' : 'success'}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
          }
        </script>
    </body>

    </html>