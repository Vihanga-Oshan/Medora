<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Messages | Medora</title>
                <c:choose>
                    <c:when test="${role == 'pharmacist'}">
                        <link rel="stylesheet"
                            href="${pageContext.request.contextPath}/css/pharmacist/dashboard-style.css">
                    </c:when>
                    <c:otherwise>
                        <link rel="stylesheet" href="${pageContext.request.contextPath}/css/patient/main.css">
                    </c:otherwise>
                </c:choose>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/components/chat-style.css">
                <style>
                    /* --- Chat Layout --- */


                    /* --- Chat Layout --- */
                    .chat-layout {
                        display: grid;
                        grid-template-columns: 320px 1fr;
                        grid-template-rows: 1fr;
                        flex: 1;
                        min-height: 0;
                        /* Critical for flex scrolling */
                        background: white;
                        border-radius: 20px;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                        overflow: hidden;
                        border: 1px solid var(--glass-border);
                        margin: 0 40px 40px;
                    }

                    .contact-list {
                        border-right: 1px solid #f0f2f5;
                        overflow-y: auto;
                        background: #fcfcfc;
                    }

                    .contact-item {
                        padding: 16px 20px;
                        border-bottom: 1px solid #f0f2f5;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        position: relative;
                        transition: all 0.2s;
                    }

                    .contact-item:hover {
                        background: #f8fafc;
                    }

                    .contact-item.active {
                        background: #e6f0ff;
                        border-right: 3px solid var(--chat-primary);
                    }

                    .contact-avatar {
                        width: 44px;
                        height: 44px;
                        background: var(--chat-primary);
                        color: white;
                        border-radius: 12px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-weight: 600;
                    }

                    .chat-window {
                        min-height: 0;
                        height: 100%;
                        display: flex;
                        flex-direction: column;
                        overflow: hidden;
                    }

                    .chat-container {
                        display: flex;
                        flex-direction: column;
                        height: 100%;
                        min-height: 0;
                        overflow: hidden;
                    }

                    .chat-messages {
                        flex: 1;
                        overflow-y: auto !important;
                        padding: 24px;
                        display: flex;
                        flex-direction: column;
                        gap: 15px;
                        background: #f8fafc;
                        min-height: 0;
                    }

                    .chat-input-area {
                        padding: 20px 32px;
                        background: white;
                        border-top: 1px solid var(--glass-border);
                        display: flex;
                        gap: 16px;
                        align-items: center;
                        flex-shrink: 0;
                    }

                    /* --- Patient View Overrides --- */
                    .patient-view .chat-layout {
                        grid-template-columns: 1fr 300px;
                        width: 100%;
                        max-width: 1150px;
                        margin: 0 auto;
                        height: 600px;
                        border-radius: 20px;
                        border: 1px solid var(--glass-border);
                        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
                    }

                    @media (max-width: 1100px) {
                        .patient-view .chat-layout {
                            grid-template-columns: 1fr;
                        }

                        .chat-sidebar {
                            display: none;
                        }
                    }

                    .patient-view .chat-container {
                        height: 100%;
                        min-height: 0;
                    }

                    /* --- Chat Sidebar (Meds Widget) --- */
                    .chat-sidebar {
                        background: #fcfcfc;
                        border-left: 1px solid #f0f2f5;
                        padding: 24px;
                        overflow-y: auto;
                    }

                    .sidebar-section h3 {
                        font-size: 12px;
                        font-weight: 700;
                        text-transform: uppercase;
                        letter-spacing: 0.1em;
                        color: var(--medical-blue);
                        margin-bottom: 24px;
                        border-bottom: 1px solid #f1f5f9;
                        padding-bottom: 10px;
                    }

                    .med-widget-item {
                        padding: 16px;
                        background: white;
                        border-radius: 16px;
                        border: 1px solid #f1f5f9;
                        margin-bottom: 12px;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
                    }

                    .med-widget-item h4 {
                        margin: 0 0 4px 0;
                        font-size: 15px;
                        color: var(--text-main);
                    }

                    .med-widget-item .dosage {
                        font-size: 13px;
                        color: var(--chat-primary);
                        font-weight: 600;
                    }

                    .med-widget-item .frequency {
                        font-size: 12px;
                        color: var(--text-muted);
                        margin-top: 4px;
                    }

                    .patient-view .chat-page-title-box {
                        max-width: 1000px;
                        margin: 0 auto 10px;
                    }

                    .patient-view .contact-list {
                        display: none;
                    }

                    .chat-page-title-box {
                        flex-shrink: 0;
                        padding: 20px 40px 0;
                        margin-bottom: 30px;
                    }
                </style>
            </head>

            <body class="dashboard-body role-${role}">
                <c:if test="${role == 'patient'}">
                    <jsp:include page="/WEB-INF/views/components/header.jsp" />
                    <!-- Ensure receiverId is set for patients even if servlet failed -->
                    <c:if test="${empty receiverId}">
                        <c:set var="receiverId" value="PHARMACIST" scope="request" />
                    </c:if>
                </c:if>
                <div class="container ${role == 'patient' ? 'patient-view' : ''}">
                    <!-- Sidebar -->
                    <c:choose>
                        <c:when test="${role == 'pharmacist'}">
                            <%@ include file="/WEB-INF/views/components/sidebar.jsp" %>
                        </c:when>
                        <c:otherwise>
                            <!-- Patient doesn't have a sidebar in this view -->
                        </c:otherwise>
                    </c:choose>

                    <main class="main-content">


                        <div class="chat-layout">
                            <!-- Contact List - Hidden for Patients -->
                            <c:if test="${role != 'patient'}">
                                <div class="contact-list">
                                    <div class="contact-tabs" style="display: flex; border-bottom: 1px solid #f0f2f5;">
                                        <a href="?type=patients"
                                            class="tab-link ${empty chatType || chatType == 'patients' ? 'active' : ''}"
                                            style="flex: 1; text-align: center; padding: 15px; text-decoration: none; color: #666; font-weight: 500;">Patients</a>
                                        <a href="?type=suppliers"
                                            class="tab-link ${chatType == 'suppliers' ? 'active' : ''}"
                                            style="flex: 1; text-align: center; padding: 15px; text-decoration: none; color: #666; font-weight: 500;">Suppliers</a>
                                    </div>
                                    <style>
                                        .tab-link.active {
                                            color: var(--chat-primary) !important;
                                            border-bottom: 2px solid var(--chat-primary);
                                            background: #f8fafc;
                                        }

                                        .tab-link:hover {
                                            background: #f8fafc;
                                        }
                                    </style>
                                    <c:forEach var="c" items="${contacts}">
                                        <c:set var="contactId" value="${role == 'patient' ? c.id : c.nic}" />
                                        <c:set var="unread" value="${unreadCounts[contactId]}" />
                                        <div class="contact-item ${contactId == receiverId ? 'active' : ''} ${unread > 0 ? 'has-unread' : ''}"
                                            onclick="window.location.href='?type=${chatType}&with=${contactId}'">
                                            <div class="contact-avatar">
                                                ${fn:substring(c.name, 0, 1)}
                                            </div>
                                            <div class="contact-info">
                                                <h4>
                                                    <c:if test="${unread > 0}">
                                                        <span class="unread-dot"></span>
                                                    </c:if>
                                                    ${c.name}
                                                </h4>

                                                <c:choose>
                                                    <c:when test="${role == 'pharmacist'}">
                                                        <p
                                                            style="font-size: 0.85em; color: #666; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">
                                                            ${not empty c.lastMessage ? c.lastMessage : '<i>No
                                                                messages</i>'}
                                                        </p>
                                                    </c:when>
                                                    <c:otherwise>
                                                        <p>Pharmacist</p>
                                                    </c:otherwise>
                                                </c:choose>
                                            </div>
                                        </div>
                                    </c:forEach>
                                </div>
                            </c:if>

                            <!-- Chat Window -->
                            <div class="chat-window">
                                <c:choose>
                                    <c:when test="${not empty receiverId}">
                                        <div class="chat-container">
                                            <div class="chat-header">
                                                <div class="status-dot"></div>
                                                <div style="display: flex; flex-direction: column;">
                                                    <c:choose>
                                                        <c:when test="${role == 'patient'}">
                                                            <h3 style="color: var(--medical-blue); font-weight: 700;">
                                                                Pharmacy Support</h3>
                                                            <div
                                                                style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                                                                Expert Healthcare Advisor</div>
                                                        </c:when>
                                                        <c:otherwise>
                                                            <c:forEach var="ct" items="${contacts}">
                                                                <c:set var="ctId"
                                                                    value="${role == 'patient' ? ct.id : ct.nic}" />
                                                                <c:if test="${ctId == receiverId}">
                                                                    <c:set var="receiverName" value="${ct.name}" />
                                                                </c:if>
                                                            </c:forEach>
                                                            <h3>${not empty receiverName ? receiverName : 'Loading...'}
                                                            </h3>
                                                            <div
                                                                style="font-size: 12px; color: var(--medical-blue); font-weight: 600;">
                                                                Active Session</div>
                                                        </c:otherwise>
                                                    </c:choose>
                                                </div>
                                            </div>
                                            <div class="chat-messages" id="message-container">
                                                <!-- Messages loaded via JS -->
                                            </div>
                                            <form class="chat-input-area" id="chat-form">
                                                <div
                                                    style="position: relative; flex: 1; display: flex; align-items: center;">
                                                    <input type="text" class="chat-input" id="msg-input"
                                                        placeholder="Write your message..." autocomplete="off">
                                                </div>
                                                <button type="submit" class="send-btn">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <line x1="22" y1="2" x2="11" y2="13"></line>
                                                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </c:when>
                                    <c:otherwise>
                                        <div class="no-selection">
                                            <div class="no-selection-icon">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8Z" />
                                                </svg>
                                            </div>
                                            <h3>Your Conversations</h3>
                                            <p>Select a contact from the list to start a secure messaging session.</p>
                                        </div>
                                    </c:otherwise>
                                </c:choose>
                            </div>

                            <!-- Right Sidebar for Patients: Active Medications -->
                            <c:if test="${role == 'patient'}">
                                <div class="chat-sidebar">
                                    <div class="sidebar-section">
                                        <h3>Active Medications</h3>
                                        <c:choose>
                                            <c:when test="${empty activeMeds}">
                                                <p style="font-size: 14px; color: var(--text-muted);">No active
                                                    prescriptions found.</p>
                                            </c:when>
                                            <c:otherwise>
                                                <div class="meds-list">
                                                    <c:forEach var="med" items="${activeMeds}">
                                                        <div class="med-widget-item">
                                                            <h4>${med.medicineName}</h4>
                                                            <div class="dosage">${med.dosage}</div>
                                                            <div class="frequency">${med.frequency} • ${med.mealTiming}
                                                            </div>
                                                        </div>
                                                    </c:forEach>
                                                </div>
                                            </c:otherwise>
                                        </c:choose>
                                    </div>
                                </div>
                            </c:if>
                        </div>
                    </main>
                </div>

                <script>
                    const userId = "${userId}";
                    const receiverId = "${receiverId}";
                    const contextPath = "${pageContext.request.contextPath}";
                    const isSupplier = "${chatType}" === "suppliers";
                    let lastId = 0;

                    if (receiverId) {
                        const form = document.getElementById('chat-form');
                        const input = document.getElementById('msg-input');
                        const container = document.getElementById('message-container');

                        // Highlight active messages link in navbar for patients
                        document.querySelectorAll('.nav-links a').forEach(link => {
                            if (link.getAttribute('href').includes('/messages')) {
                                link.classList.add('active');
                            }
                        });

                        form.onsubmit = function (e) {
                            e.preventDefault();
                            const msg = input.value.trim();
                            if (!msg) return;

                            fetch(contextPath + '/chat', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'receiverId=' + receiverId + '&message=' + encodeURIComponent(msg)
                            }).then(res => {
                                if (res.ok) {
                                    input.value = '';
                                    poll();
                                }
                            });
                        };

                        function poll() {
                            fetch(contextPath + '/chat?receiverId=' + receiverId + '&lastId=' + lastId + '&isSupplier=' + isSupplier)
                                .then(res => res.json())
                                .then(data => {
                                    data.forEach(m => {
                                        const div = document.createElement('div');
                                        div.className = 'message ' + (m.senderId === userId ? 'sent' : 'received');

                                        const textSpan = document.createElement('span');
                                        textSpan.textContent = m.message;
                                        div.appendChild(textSpan);

                                        if (!m.isRead && m.senderId !== userId) {
                                            const badge = document.createElement('span');
                                            badge.className = 'new-badge';
                                            badge.textContent = 'New';
                                            div.appendChild(badge);
                                        }

                                        const timeSpan = document.createElement('span');
                                        timeSpan.className = 'message-time';
                                        timeSpan.textContent = m.sentAt;
                                        div.appendChild(timeSpan);

                                        container.appendChild(div);
                                        lastId = m.id;
                                    });
                                    if (data.length > 0) {
                                        container.scrollTop = container.scrollHeight;
                                    }
                                });
                        }

                        // Initial poll and set interval
                        poll();
                        setInterval(poll, 3000);
                    }
                </script>
                <c:if test="${role == 'patient'}">
                    <jsp:include page="/WEB-INF/views/components/footer.jsp" />
                </c:if>
            </body>

            </html>