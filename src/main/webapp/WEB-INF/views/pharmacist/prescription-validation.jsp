<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html>
<head>
  <title>Validate Prescriptions - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/index.css">
  <style>
    .validation-container {
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    .back-link {
      color: #007acc;
      text-decoration: none;
      font-weight: bold;
    }
    .prescription-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    .prescription-card {
      border: 1px solid #e0e0e0;
      border-radius: 8px;
      padding: 16px;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s;
    }
    .prescription-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .preview-container {
      width: 100%;
      height: 200px;
      overflow: hidden;
      border-radius: 4px;
      margin-bottom: 10px;
      position: relative;
      background: #f9f9f9;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .preview-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
    }
    .pdf-icon {
      font-size: 48px;
      color: #d32f2f;
    }
    .info {
      margin-bottom: 10px;
    }
    .info p {
      margin: 5px 0;
      font-size: 14px;
    }
    .btn-group {
      display: flex;
      gap: 8px;
      justify-content: center;
    }
    .btn-approve {
      background: #28a745; color: white; padding: 8px 16px;
      border: none; border-radius: 4px; cursor: pointer;
    }
    .btn-reject {
      background: #dc3545; color: white; padding: 8px 16px;
      border: none; border-radius: 4px; cursor: pointer;
    }
    .view-btn {
      background: #007acc; color: white; padding: 6px 12px;
      border: none; border-radius: 4px; cursor: pointer;
      text-decoration: none; display: inline-block;
      margin-right: 10px;
    }
    .file-type {
      font-size: 12px;
      color: #666;
      margin-top: 5px;
    }
  </style>
</head>
<body>
<div class="validation-container">
  <div class="header">
    <h2>Prescription Validation</h2>
    <a href="${pageContext.request.contextPath}/pharmacist/dashboard" class="back-link">← Back to Dashboard</a>
  </div>

  <c:if test="${empty prescriptions}">
    <p>No pending prescriptions.</p>
  </c:if>

  <div class="prescription-grid">
    <c:forEach var="p" items="${prescriptions}">
      <div class="prescription-card">
        <!-- Preview -->
        <div class="preview-container">
          <c:choose>
            <c:when test="${fn:endsWith(p.fileName, '.pdf')}">
              <span class="pdf-icon">📄</span>
            </c:when>
            <c:otherwise>
              <img src="${pageContext.request.contextPath}/view-prescription?filePath=${p.filePath}"
                   alt="Prescription" class="preview-image">
            </c:otherwise>
          </c:choose>
        </div>

        <!-- Info -->
        <div class="info">
          <p><strong>Patient:</strong> ${p.patientNic}</p>
          <p><strong>Uploaded:</strong> ${p.uploadDate}</p>
          <p class="file-type">${p.fileName}</p>
        </div>

        <!-- Actions -->
        <div class="btn-group">
          <a href="${pageContext.request.contextPath}/pharmacist/prescription-review?id=${p.id}"
             class="view-btn">View</a>

          <form action="${pageContext.request.contextPath}/validate-prescription" method="post" style="display:inline;">
            <input type="hidden" name="prescriptionId" value="${p.id}">
            <input type="hidden" name="action" value="APPROVE">
            <button type="submit" class="btn-approve">Approve</button>
          </form>

          <form action="${pageContext.request.contextPath}/validate-prescription" method="post" style="display:inline;">
            <input type="hidden" name="prescriptionId" value="${p.id}">
            <input type="hidden" name="action" value="REJECT">
            <button type="submit" class="btn-reject">Reject</button>
          </form>
        </div>
      </div>
    </c:forEach>
  </div>
</div>
</body>
</html>