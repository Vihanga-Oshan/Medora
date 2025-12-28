<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
  <%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
      <!DOCTYPE html>
      <html lang="en">

      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Medora - Prescription Review</title>
        <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
        <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/prescription-review.css">
        <style>
          /* Specificity override for the main layout container */
          body div.container {
            display: flex !important;
            width: 100% !important;
            max-width: none !important;
            height: 100vh !important;
            margin: 0 !important;
          }
        </style>
      </head>

      <body>
        <div class="container">

          <!-- Include Sidebar Component -->
          <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

            <!-- Main Content -->
            <main class="main-content">
              <!-- Header -->
              <header class="header">
                <div class="user-info">
                  <img src="${pageContext.request.contextPath}/assets/register-patient1.png" alt="User Avatar"
                    class="avatar">
                  <span class="user-role">Super Pharmacist</span>
                </div>
                <div class="greeting">
                  <span class="greeting-icon">☀️</span>
                  <div>
                    <span class="greeting-text">Good Morning</span>
                    <span class="date-time">14 January 2022 • 22:45:04</span>
                  </div>
                </div>
              </header>

              <div class="review-page-body">
                <!-- Page Title -->
                <h2 class="page-title">Prescription Review</h2>

                <!-- Content Area -->
                <div class="content-wrapper">
                  <div class="prescription-image-container">
                    <div class="image-header">
                      <span>Prescription Document</span>
                    </div>
                    <div class="image-box" id="imageContainer">
                      <c:choose>
                        <c:when test="${not empty prescription and fn:endsWith(prescription.fileName, '.pdf')}">
                          <div class="pdf-placeholder">
                            <span class="pdf-icon">📄</span>
                            <p>PDF Document</p>
                            <a href="${pageContext.request.contextPath}/prescriptionFile/${prescription.filePath}"
                              target="_blank" class="btn secondary">Open PDF</a>
                          </div>
                        </c:when>
                        <c:when test="${not empty prescription}">
                          <c:set var="cleanPath" value="${fn:replace(prescription.filePath, '\"', '')}" />
                          <img src="${pageContext.request.contextPath}/prescriptionFile/${cleanPath}"
                               alt="Prescription" id="zoomable-image">
                          <div class="zoom-controls">
                            <div class="zoom-info" id="zoomPercent">100%</div>
                            <button type="button" class="zoom-btn" onclick="adjustZoom(-0.2)" title="Zoom Out">−</button>
                            <button type="button" class="zoom-btn" onclick="adjustZoom(0.2)" title="Zoom In">+</button>
                            <button type="button" class="zoom-btn" onclick="resetZoom()" title="Reset">↺</button>
                          </div>
                        </c:when>
                        <c:otherwise>
                          <p class="error-msg">No prescription document available.</p>
                        </c:otherwise>
                      </c:choose>
                    </div>

                    <script>
                      (function () {
                        var ks = 1;
                        var kx = 0;
                        var ky = 0;
                        var sx = 0;
                        var sy = 0;
                        var drag = false;

                        // Dynamically build IDs to avoid tool mangling strings
                        var id1 = "zoomable" + "-" + "image";
                        var id2 = "image" + "Container";
                        var id3 = "zoom" + "Percent";

                        var target = document.getElementById(id1);
                        var box = document.getElementById(id2);
                        var lbl = document.getElementById(id3);

                        function redraw() {
                          if (target) {
                            target.style.transform = "translate(" + kx + "px," + ky + "px) scale(" + ks + ")";
                            if (lbl) lbl.innerText = Math.round(ks * 100) + "%";
                          }
                        }

                        window.adjustZoom = function (val) {
                          var n = ks + val;
                          if (n >= 0.5 && n <= 5) {
                            ks = n;
                            redraw();
                          }
                        };

                        window.resetZoom = function () {
                          ks = 1; kx = 0; ky = 0;
                          redraw();
                        };

                        if (box && target) {
                          box.addEventListener("mousedown", function (e) {
                            if (ks > 1) {
                              drag = true;
                              sx = e.clientX - kx;
                              sy = e.clientY - ky;
                              target.classList.add("dragging");
                              e.preventDefault();
                            }
                          });

                          window.addEventListener("mousemove", function (e) {
                            if (drag) {
                              kx = e.clientX - sx;
                              ky = e.clientY - sy;
                              redraw();
                            }
                          });

                          window.addEventListener("mouseup", function () {
                            drag = false;
                            if (target) target.classList.remove("dragging");
                          });

                          box.addEventListener("wheel", function (e) {
                            e.preventDefault();
                            var d = e.deltaY > 0 ? -0.1 : 0.1;
                            window.adjustZoom(d);
                          }, { passive: false });
                        }
                      })();
                    </script>
                    </div>

                    <div class="patient-details-card">
                      <div class="details-header">
                        <h3>Patient Details</h3>
                      </div>
                      <div class="details-body">
                        <div class="detail-row">
                          <span class="label">Full Name</span>
                          <span class="value">${patient.name}</span>
                        </div>
                        <div class="detail-row">
                          <span class="label">NIC</span>
                          <span class="value">${patient.nic}</span>
                        </div>
                        <div class="detail-row">
                          <span class="label">Emergency Contact</span>
                          <span class="value">${empty patient.emergencyContact ? ' Not provided' :
                            patient.emergencyContact}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Email</span>
                      <span class="value">${empty patient.email ? 'Not provided' : patient.email}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Allergies</span>
                      <span class="value highlight-danger">${empty patient.allergies ? 'None' :
                        patient.allergies}</span>
                    </div>
                    <div class="detail-row">
                      <span class="label">Chronic Conditions</span>
                      <span class="value">${empty patient.chronicIssues ? 'None' : patient.chronicIssues}</span>
                    </div>

                    <c:if test="${not empty patient.guardianNic}">
                      <div class="detail-row">
                        <span class="label">Guardian NIC</span>
                        <span class="value">${patient.guardianNic}</span>
                      </div>
                    </c:if>
                  </div>

                  <!-- Action Buttons -->
                  <div class="review-actions">
                    <form action="${pageContext.request.contextPath}/pharmacist/prescription-review" method="post"
                      class="action-form">
                      <input type="hidden" name="prescriptionId" value="${prescription.id}">
                      <input type="hidden" name="action" value="REJECTED">
                      <button type="submit" class="btn-reject">Reject Prescription</button>
                    </form>
                    <form action="${pageContext.request.contextPath}/pharmacist/prescription-review" method="post"
                      class="action-form">
                      <input type="hidden" name="prescriptionId" value="${prescription.id}">
                      <input type="hidden" name="action" value="APPROVED">
                      <button type="submit" class="btn-approve">Approve Prescription</button>
                    </form>
                  </div>
                </div>
              </div>
        </div>
        </main>
        </div>
      </body>

      </html>