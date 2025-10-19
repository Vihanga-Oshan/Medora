<%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
<%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<!DOCTYPE html>
<html>
<head>
    <title>Medication Scheduling</title>
    <link rel="stylesheet" href="${pageContext.request.contextPath}/css/pharmacist/medication-scheduling.css">
</head>
<body>
<div class="container">
    <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>

    <div class="main-content">
        <h1>Medication Scheduling</h1>

        <div class="scheduling-grid">

            <!-- ======= Schedule Form ======= -->
            <form action="${pageContext.request.contextPath}/pharmacist/submitSchedule"
                  method="post" class="schedule-form">

                <!-- Hidden values -->
                <input type="hidden" name="prescriptionId" value="${prescription.id}">
                <input type="hidden" name="patientNic" value="${prescription.patientNic}">

                <h3>Medication Schedules</h3>

                <!-- Dynamic rows -->
                <div id="med-rows">
                    <div class="med-row" data-row>
                        <label>Medicine</label>
                        <select name="medicineId" required>
                            <c:forEach var="m" items="${medicines}">
                                <option value="${m[0]}">${m[1]}</option>
                            </c:forEach>
                        </select>

                        <label>Dosage</label>
                        <select name="dosageId" required>
                            <c:forEach var="d" items="${dosages}">
                                <option value="${d[0]}">${d[1]}</option>
                            </c:forEach>
                        </select>

                        <label>Frequency</label>
                        <<select name="frequencyId" required>
                        <c:forEach var="f" items="${frequencies}">
                            <option value="${f[0]}">${f[1]}</option>
                        </c:forEach>
                         </select>


                        <label>Meal Timing</label>
                        <select name="mealTimingId">
                            <option value="">— Select —</option>
                            <c:forEach var="mt" items="${mealTimings}">
                                <option value="${mt[0]}">${mt[1]}</option>
                            </c:forEach>
                        </select>

                        <label>Start Date</label>
                        <input type="date" name="startDate" required>

                        <label>Duration (Days)</label>
                        <input type="number" name="durationDays" min="1" value="7" required>

                        <label>Instructions (optional)</label>
                        <textarea name="instructions" rows="2" placeholder="E.g. Take with water..."></textarea>

                        <button type="button" class="btn-reject" onclick="removeRow(this)">Remove</button>
                        <hr>
                    </div>
                </div>

                <!-- Add another medicine -->
                <div class="btn-group">
                    <button type="button" class="btn-reject" onclick="addRow()">+ Add Another Medicine</button>
                </div>

                <!-- Submit -->
                <div class="btn-group">
                    <button type="submit" class="btn-submit">Submit Schedule</button>
                </div>
            </form>

            <!-- ======= Prescription Preview ======= -->
            <div class="patient-details-box">
                <div class="preview-placeholder">
                    <c:choose>
                        <c:when test="${fn:endsWith(prescription.fileName, '.pdf')}">
                            <span class="pdf-icon">📄 PDF</span>
                        </c:when>
                        <c:otherwise>
                            <img src="${pageContext.request.contextPath}/view-prescription?filePath=${prescription.filePath}"
                                 alt="Prescription" style="width: 100%; border-radius: 12px;" />
                        </c:otherwise>
                    </c:choose>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======= JavaScript Logic ======= -->
<script>
    function addRow() {
        const container = document.getElementById('med-rows');
        const template = container.querySelector('[data-row]');
        const clone = template.cloneNode(true);

        clone.querySelectorAll('input, textarea, select').forEach(el => {
            if (el.tagName === 'SELECT') el.selectedIndex = 0;
            else el.value = '';
        });

        container.appendChild(clone);
    }

    function removeRow(btn) {
        const container = document.getElementById('med-rows');
        if (container.querySelectorAll('[data-row]').length > 1) {
            btn.closest('[data-row]').remove();
        }
    }
</script>
</body>
</html>
