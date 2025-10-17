<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Prescriptions - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/prescriptions.css">
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">My Prescriptions</h1>
  <p class="section-subtitle">Upload and manage your medical prescriptions</p>

  <div class="card">
    <h2 class="card-title">Upload New Prescription</h2>
    <p class="card-subtitle">Upload a clear image or PDF of your prescription for pharmacist validation</p>

    <form action="${pageContext.request.contextPath}/patient/upload-prescription" method="post" enctype="multipart/form-data">\
    <label for="prescriptionFile" class="upload-area">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 16V12M12 12V8M12 12H8M12 12H16" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M16 16L12 20L8 16" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 4V12" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>Click to upload or drag and drop</p>
        <p class="small">PDF, PNG, JPG up to 10MB</p>
        <input type="file" name="prescriptionFile" id="prescriptionFile" accept=".pdf,.jpg,.jpeg,.png" required hidden />
      </label>
      <button type="submit" class="btn btn-upload">Upload Prescription</button>
      <c:if test="${not empty error}">
        <p class="error">${error}</p>
      </c:if>
    </form>
  </div>

  <div class="card">
    <div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M14 5H10V4C10 2.89543 10.8954 2 12 2C13.1046 2 14 2.89543 14 4V5Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M12 6V18" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 18H18V6" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <p>No prescriptions uploaded yet</p>
    </div>
  </div>
</main>

<%--<jsp:include page="/WEB-INF/views/components/footer.jsp" />--%>
</body>
</html>