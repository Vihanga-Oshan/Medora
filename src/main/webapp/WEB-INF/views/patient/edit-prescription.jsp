<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Prescription</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/header.css">
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/prescriptions.css">
    <style>
        .edit-container {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            gap: 40px;
        }

        .edit-form {
            background: #ffffff;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            flex: 1;
            max-width: 400px;
        }

        .edit-form h2 {
            margin-bottom: 16px;
            font-size: 22px;
        }

        .edit-form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .edit-form input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .edit-form button,
        .edit-form a {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            text-decoration: none;
            margin-top: 10px;
        }

        .edit-form .btn-save {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .edit-form .btn-cancel {
            background-color: transparent;
            color: #007bff;
            border: 1px solid #007bff;
            margin-left: 10px;
        }

        .image-preview {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .image-preview h3 {
            margin-bottom: 12px;
            font-size: 20px;
        }

        .image-preview img {
            width: 100%;
            max-width: 600px;
            max-height: 550px;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .edit-container {
                flex-direction: column-reverse;
                align-items: center;
            }

            .image-preview img {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<jsp:include page="/WEB-INF/views/components/header.jsp" />

<main class="container">
    <p class="section-subtitle">Update the display name of your uploaded prescription</p>

    <div class="edit-layout">
        <!-- LEFT: File preview -->
        <div class="edit-preview">
            <div class="preview-box">
                <a href="${pageContext.request.contextPath}/prescriptionFile/${prescription.filePath}" target="_blank">
                    <c:choose>
                        <c:when test="${fn:endsWith(prescription.fileName, 'pdf') or fn:endsWith(prescription.fileName, 'PDF')}">
                            <div class="pdf-icon">PDF</div>
                        </c:when>
                        <c:otherwise>
                            <img src="${pageContext.request.contextPath}/prescriptionFile/${prescription.filePath}" alt="${prescription.fileName}" />
                        </c:otherwise>
                    </c:choose>
                </a>
            </div>
        </div>

        <!-- RIGHT: Edit Form -->
        <div class="edit-form card">
            <h3 class="card-title">Edit File Name</h3>
            <p class="card-subtitle">This name appears under the prescription in your list.</p>

            <form method="post" action="${pageContext.request.contextPath}/patient/edit-prescription">
                <input type="hidden" name="id" value="${prescription.id}" />

                <div class="form-group">
                    <label for="fileName">Prescription Name:</label>
                    <input type="text" id="fileName" name="fileName" value="${prescription.fileName}" required />
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="${pageContext.request.contextPath}/patient/dashboard" class="btn btn-outline">Cancel</a>
            </form>
        </div>
    </div>
</main>

<jsp:include page="/WEB-INF/views/components/footer.jsp" />
</body>
</html>