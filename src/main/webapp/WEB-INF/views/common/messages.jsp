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
                        background: white;
                        border-radius: 20px;
                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                        overflow: hidden;
                        border: 1px solid #e2e8f0;
                        margin: 0 40px 40px;
                    }

                    /* Pharmacist Role Fixes */
                    body.role-pharmacist .container {
                        display: flex;
                        height: 100vh;
                        overflow: hidden;
                    }

                    body.role-pharmacist .main-content {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        height: 100vh;
                        overflow: hidden;
                        background: var(--bg-light);
                    }

                    body.role-pharmacist .chat-layout {
                        height: calc(100vh - 80px);
                        margin: 20px;
                        flex: 1;
                        min-height: 0;
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
                        user-select: none;
                        z-index: 1;
                    }

                    .contact-item:hover {
                        background: #f8fafc;
                        transform: translateX(2px);
                    }

                    .contact-item.active {
                        background: #e6f0ff;
                        border-right: 3px solid var(--chat-primary);
                    }

                    .contact-item.has-unread {
                        background: #fef3f2;
                    }

                    .contact-item.has-unread:hover {
                        background: #fee2e2;
                    }

                    .contact-item.has-unread .contact-info h4 {
                        font-weight: 700;
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

                    /* Message Bubbles */
                    .message {
                        display: flex;
                        flex-direction: column;
                        max-width: 70%;
                        padding: 12px 16px;
                        border-radius: 16px;
                        position: relative;
                        word-wrap: break-word;
                        animation: slideIn 0.3s ease;
                    }

                    @keyframes slideIn {
                        from {
                            opacity: 0;
                            transform: translateY(10px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }

                    .message.sent {
                        align-self: flex-end;
                        background: var(--chat-primary);
                        color: white;
                        border-bottom-right-radius: 4px;
                    }

                    .message.received {
                        align-self: flex-start;
                        background: white;
                        color: var(--text-main);
                        border: 1px solid #e2e8f0;
                        border-bottom-left-radius: 4px;
                    }

                    .message-time {
                        font-size: 11px;
                        opacity: 0.7;
                        margin-top: 6px;
                        align-self: flex-end;
                    }

                    @keyframes pulse {

                        0%,
                        100% {
                            transform: scale(1);
                            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
                        }

                        50% {
                            transform: scale(1.05);
                            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.6);
                        }
                    }

                    .unread-dot {
                        display: inline-block;
                        width: 8px;
                        height: 8px;
                        background: #ef4444;
                        border-radius: 50%;
                        margin-right: 8px;
                        animation: pulse 2s infinite;
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

                    .no-selection {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        height: 100%;
                        text-align: center;
                        padding: 60px 40px;
                        color: var(--text-muted);
                    }

                    .chat-window.has-selection .no-selection {
                        display: none;
                    }

                    .chat-window:not(.has-selection) .chat-container {
                        display: none;
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
                    <!-- Ensure selectedContactId is set for patients even if servlet failed -->
                    <c:if test="${empty requestScope.selectedContactId}">
                        <c:set var="selectedContactId" value="PHARMACIST" scope="request" />
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
                                        <c:set var="currContactId" value="${role == 'patient' ? c.id : c.nic}" />
                                        <c:set var="isThisActive"
                                            value="${not empty requestScope.selectedContactId and currContactId eq requestScope.selectedContactId}" />
                                        <div class="contact-item ${isThisActive ? 'active' : ''} ${unreadCounts[currContactId] > 0 ? 'has-unread' : ''}"
                                            data-contact-id="${currContactId}" data-contact-name="${c.name}"
                                            data-chat-type="${chatType}">
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
                            <div class="chat-window ${not empty requestScope.selectedContactId ? 'has-selection' : ''}">
                                <!-- Welcome Screen (shown when no selection) -->
                                <div class="no-selection">
                                    <div class="no-selection-icon"
                                        style="margin-bottom: 24px; color: var(--chat-primary); opacity: 0.4;">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="m3 21 1.9-5.7a8.5 8.5 0 1 1 3.8 3.8Z" />
                                        </svg>
                                    </div>
                                    <h3
                                        style="font-size: 22px; font-weight: 600; margin-bottom: 12px; color: var(--text-main);">
                                        Your Conversations</h3>
                                    <p style="font-size: 15px; color: var(--text-muted); max-width: 400px;">Select a
                                        contact from the list to start a secure messaging session.</p>
                                </div>

                                <!-- Chat Container (shown when has-selection) -->
                                <div class="chat-container">
                                    <div class="chat-header">
                                        <div class="status-dot"></div>
                                        <div style="display: flex; flex-direction: column;">
                                            <c:choose>
                                                <c:when test="${role == 'patient'}">
                                                    <h3 style="color: var(--medical-blue); font-weight: 700;">Pharmacy
                                                        Support</h3>
                                                    <div
                                                        style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                                                        Expert Healthcare Advisor</div>
                                                </c:when>
                                                <c:otherwise>
                                                    <c:set var="selectedName" value="Loading..." />
                                                    <c:set var="matchFound" value="false" />
                                                    <c:if test="${not empty requestScope.selectedContactId}">
                                                        <c:forEach var="ct" items="${contacts}">
                                                            <c:if test="${not matchFound}">
                                                                <c:set var="ctId"
                                                                    value="${role == 'patient' ? ct.id : ct.nic}" />
                                                                <c:if test="${ctId eq requestScope.selectedContactId}">
                                                                    <c:set var="selectedName" value="${ct.name}" />
                                                                    <c:set var="matchFound" value="true" />
                                                                </c:if>
                                                            </c:if>
                                                        </c:forEach>
                                                    </c:if>
                                                    <h3 id="current-chat-name">${selectedName}</h3>
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
                                        <div style="position: relative; flex: 1; display: flex; align-items: center;">
                                            <input type="text" class="chat-input" id="msg-input"
                                                placeholder="Write your message..." autocomplete="off">
                                        </div>
                                        <button type="submit" class="send-btn">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
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
                    let receiverId = "${requestScope.selectedContactId}";
                    const contextPath = "${pageContext.request.contextPath}";
                    let isSupplier = "${chatType}" === "suppliers";
                    let lastId = 0;
                    let pollInterval;

                    // Handle contact item clicks for smooth switching
                    document.querySelectorAll('.contact-item').forEach(item => {
                        item.addEventListener('click', function () {
                            const contactId = this.dataset.contactId;
                            const contactName = this.dataset.contactName;
                            const chatType = this.dataset.chatType;

                            // Update URL without reload
                            const newUrl = `?type=${chatType}&with=${contactId}`;
                            window.history.pushState({}, '', newUrl);

                            // Update active state
                            document.querySelectorAll('.contact-item').forEach(c => c.classList.remove('active'));
                            this.classList.add('active');

                            // Remove unread indicators from this contact
                            this.classList.remove('has-unread');
                            const unreadDot = this.querySelector('.unread-dot');
                            if (unreadDot) {
                                unreadDot.remove();
                            }

                            // Switch chat
                            switchChat(contactId, contactName);
                        });
                    });

                    function switchChat(newReceiverId, contactName) {
                        receiverId = newReceiverId;
                        lastId = 0;

                        // Show chat UI and hide welcome screen
                        const chatWindow = document.querySelector('.chat-window');
                        if (chatWindow) {
                            chatWindow.classList.add('has-selection');
                        }

                        // Clear existing messages
                        const msgContainer = document.getElementById('message-container');
                        if (msgContainer) {
                            msgContainer.innerHTML = '';
                        }

                        // Update chat header
                        const chatHeader = document.querySelector('.chat-header h3');
                        if (chatHeader && contactName) {
                            chatHeader.textContent = contactName;
                        }

                        // Stop old polling and start new
                        if (pollInterval) {
                            clearInterval(pollInterval);
                        }

                        // Start polling for new chat
                        if (receiverId) {
                            fetchMessages();
                            pollInterval = setInterval(fetchMessages, 3000);
                        }
                    }

                    // Global event listener for form (delegation)
                    document.addEventListener('submit', function (e) {
                        if (e.target && e.target.id === 'chat-form') {
                            e.preventDefault();
                            const input = document.getElementById('msg-input');
                            const msg = input.value.trim();
                            if (!msg || !receiverId) return;

                            fetch(contextPath + '/chat', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'receiverId=' + receiverId + '&message=' + encodeURIComponent(msg)
                            }).then(res => {
                                if (res.ok) {
                                    input.value = '';
                                    fetchMessages();
                                }
                            });
                        }
                    });

                    if (receiverId) {
                        function fetchMessages() {
                            const container = document.getElementById('message-container');
                            if (!container) return;

                            fetch(contextPath + '/chat?receiverId=' + receiverId + '&lastId=' + lastId + '&isSupplier=' + isSupplier)
                                .then(res => res.json())
                                .then(data => {
                                    data.forEach(m => {
                                        const div = document.createElement('div');
                                        div.className = 'message ' + (m.senderId === userId ? 'sent' : 'received');

                                        const textSpan = document.createElement('span');
                                        textSpan.textContent = m.message;
                                        div.appendChild(textSpan);

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

                        // Initial fetch and set interval
                        fetchMessages();
                        pollInterval = setInterval(fetchMessages, 3000);
                    }
                </script>
                <c:if test="${role == 'patient'}">
                    <jsp:include page="/WEB-INF/views/components/footer.jsp" />
                </c:if>
            </body>

            </html>