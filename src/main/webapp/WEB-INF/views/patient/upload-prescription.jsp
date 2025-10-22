<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Prescriptions - Medora</title>
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/prescriptions.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
  <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">

</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
  <h1 class="section-title">My Prescriptions</h1>
  <p class="section-subtitle">Upload and manage your medical prescriptions</p>

  <div class="card">
    <h2 class="card-title">Upload New Prescription</h2>
    <p class="card-subtitle">Upload a clear image or PDF of your prescription for pharmacist validation</p>

    <form action="${pageContext.request.contextPath}/patient/upload-prescription" method="post" enctype="multipart/form-data">
    <label for="prescriptionFile" class="upload-area">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 16V12M12 12V8M12 12H8M12 12H16" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M16 16L12 20L8 16" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 4V12" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>Click to upload or drag and drop</span>
        <span class="small">PDF, PNG, JPG up to 10MB</span>
        <input type="file" name="prescriptionFile" id="prescriptionFile" accept=".pdf,.jpg,.jpeg,.png" required hidden />
      </label>

      <!-- Preview is placed outside the label to avoid nested interactive controls inside the label (prevents double file dialog) -->
      <div id="prescriptionPreview" class="preview-wrapper" aria-live="polite"></div>
      <p id="uploadError" class="error" style="display:none"></p>

      <button type="submit" class="btn btn-upload">Upload Prescription</button>
      <c:if test="${not empty error}">
        <p class="error">${error}</p>
      </c:if>
    </form>
  </div>

  <div class="card">
    <h3 class="card-title">My Uploaded Prescriptions</h3>
    <c:if test="${not empty prescriptions}">
      <p class="muted">You have ${fn:length(prescriptions)} uploaded prescriptions.</p>
    </c:if>
    <c:if test="${not empty prescriptions}">
      <div class="prescription-list">
        <c:forEach items="${prescriptions}" var="p">
          <div class="prescription-tile">
            <a href="${pageContext.request.contextPath}/prescriptionFile/${p.filePath}" target="_blank" class="prescription-thumb"
               aria-label="Open ${p.fileName}">
              <c:choose>
                <c:when test="${fn:endsWith(p.fileName, 'pdf') or fn:endsWith(p.fileName, 'PDF')}">
                  <div class="pdf-icon">PDF</div>
                </c:when>
                <c:otherwise>
                  <img src="${pageContext.request.contextPath}/prescriptionFile/${p.filePath}" alt="${p.fileName}">
                </c:otherwise>
              </c:choose>
            </a>
            <div class="prescription-meta">
              <div class="prescription-name">${p.fileName}</div>
              <div class="prescription-date">${p.formattedUploadDate}</div>
              <a href="${pageContext.request.contextPath}/patient/edit-prescription?id=${p.id}" class="btn btn-sm">Edit</a>

            </div>
          </div>
        </c:forEach>
      </div>
    </c:if>

    <c:if test="${empty prescriptions}">
      <div class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M14 5H10V4C10 2.89543 10.8954 2 12 2C13.1046 2 14 2.89543 14 4V5Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12 6V18" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M6 18H18V6" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p>No prescriptions uploaded yet</p>
      </div>
    </c:if>
  </div>

</main>

<script>
  document.addEventListener("DOMContentLoaded", () => {
  const dropZone = document.querySelector(".upload-area");
  const fileInput = document.getElementById("prescriptionFile");
  const previewWrapper = document.getElementById("prescriptionPreview");
  const uploadError = document.getElementById("uploadError");
  const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

  if (!dropZone || !fileInput || !previewWrapper) return;

  function showDefault() {
    // remove previews and errors and show default label content
    previewWrapper.innerHTML = '';
    uploadError.style.display = 'none';
    uploadError.textContent = '';
    const defaultContentItems = dropZone.querySelectorAll('svg, span');
    defaultContentItems.forEach(el => el.style.display = '');
    // restore upload area visibility
    dropZone.classList.remove('hidden');
  }

  function showError(message) {
    uploadError.textContent = message;
    uploadError.style.display = 'block';
  }

  function showPreview(file) {
    // size check
    if (file.size && file.size > MAX_FILE_SIZE) {
      showDefault();
      try { fileInput.value = ''; } catch (err) { /* ignore */ }
      showError('File is too large. Maximum allowed size is 10MB.');
      return;
    }

    // remove any previous preview first
    const existingPreview = previewWrapper.querySelector(".preview-container");
    if (existingPreview) existingPreview.remove();

    // Hide default content inside the label (if present)
    const defaultContentItems = dropZone.querySelectorAll("svg, span");
    defaultContentItems.forEach(el => el.style.display = "none");

    // Create a preview container
    const previewContainer = document.createElement("div");
    previewContainer.className = "preview-container";

    // Build preview HTML and actions (Change / Remove)
    const actionsHtml =
      '<div class="preview-actions">' +
        '<button type="button" class="btn btn-change">Change</button>' +
        '<button type="button" class="btn btn-remove">Remove</button>' +
      '</div>';

    if (file.type && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.addEventListener('load', function (evt) {
        previewContainer.innerHTML = '<img src="' + evt.target.result + '" alt="Preview">' +
          '<p class="file-name">' + file.name + '</p>' + actionsHtml;
        previewWrapper.appendChild(previewContainer);

        // hide the upload area while preview is visible
        dropZone.classList.add('hidden');

        // wire actions
        const btnChange = previewContainer.querySelector('.btn-change');
        const btnRemove = previewContainer.querySelector('.btn-remove');
        btnChange.addEventListener('click', () => fileInput.click());
        btnRemove.addEventListener('click', () => {
          try { fileInput.value = ''; } catch (err) { /* ignore */ }
          showDefault();
        });
      });
      reader.readAsDataURL(file);
    } else {
      // For non-image (PDF) show an icon + filename
      previewContainer.innerHTML =
        '<div class="pdf-preview">' +
          '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">' +
            '<path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
          '</svg>' +
          '<p class="file-name">' + file.name + '</p>' +
        '</div>' + actionsHtml;
      previewWrapper.appendChild(previewContainer);

      // hide the upload area while preview is visible
      dropZone.classList.add('hidden');

      const btnChange = previewContainer.querySelector('.btn-change');
      const btnRemove = previewContainer.querySelector('.btn-remove');
      btnChange.addEventListener('click', () => fileInput.click());
      btnRemove.addEventListener('click', () => {
        try { fileInput.value = ''; } catch (err) { /* ignore */ }
        showDefault();
      });
    }
  }

  // Handle file selection
  fileInput.addEventListener("change", (e) => {
    const file = e.target.files && e.target.files[0];
    if (file) {
      showPreview(file);
    } else {
      showDefault();
    }
  });

  // Handle drag and drop
  dropZone.addEventListener("dragenter", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.add("drag-over");
  });

  dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.add("drag-over");
  });

  dropZone.addEventListener("dragleave", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove("drag-over");
  });

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();
    e.stopPropagation();
    dropZone.classList.remove("drag-over");
    const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
    if (file) {
      // try to set the file input's FileList so form submission works
      try {
        // Preferred: assign the DataTransfer files directly
        fileInput.files = e.dataTransfer.files;
      } catch (err) {
        try {
          // Fallback: construct a new DataTransfer and add the single file
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          fileInput.files = dataTransfer.files;
        } catch (err2) {
          // last resort: leave fileInput alone (server won't receive file when submitting)
        }
      }

      showPreview(file);
    }
  });

  // initialize default state
  showDefault();

});

</script>
<jsp:include page="/WEB-INF/views/components/footer.jsp" />
</body>
</html>
