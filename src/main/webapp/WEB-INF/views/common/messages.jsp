<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <%@ taglib prefix="fn" uri="http://java.sun.com/jsp/jstl/functions" %>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Messages | Medora</title>
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap"
                    rel="stylesheet">
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
                    /* --- Global Layout Reset for Messaging --- */
                    <c:choose><c:when test="${role == 'pharmacist'}">html,
                    body {
                        height: 100vh !important;
                        margin: 0;
                        padding: 0;
                        overflow: hidden !important;
                    }

                    .container {
                        height: 100vh;
                        overflow: hidden;
                        display: flex;
                    }

                    .main-content {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        height: 100%;
                        overflow: hidden;
                        background: var(--bg-light);
                    }

                    </c:when><c:otherwise>.container {
                        width: 95%;
                        max-width: 1200px;
                        margin: 0 auto;
                        display: block;
                    }

                    .main-content {
                        padding-top: 20px;
                    }

                    </c:otherwise></c:choose>

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
                        grid-template-columns: 1fr;
                        width: 100%;
                        max-width: 1100px;
                        margin: 20px auto 40px;
                        height: 600px;
                    }

                    .patient-view .chat-container {
                        height: 600px;
                        min-height: 600px;
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

                    /* --- Components --- */
                    .unread-dot {
                        display: inline-block;
                        width: 10px;
                        height: 10px;
                        background-color: #ff4d4f;
                        border-radius: 50%;
                        margin-right: 10px;
                        vertical-align: middle;
                        box-shadow: 0 0 8px rgba(255, 77, 79, 0.4);
                        animation: pulse 2s infinite;
                    }

                    @keyframes pulse {
                        0% {
                            transform: scale(1);
                            box-shadow: 0 0 0 0 rgba(255, 77, 79, 0.4);
                        }

                        70% {
                            transform: scale(1.1);
                            box-shadow: 0 0 0 10px rgba(255, 77, 79, 0);
                        }

                        100% {
                            transform: scale(1);
                            box-shadow: 0 0 0 0 rgba(255, 77, 79, 0);
                        }
                    }

                    .contact-item.has-unread h4 {
                        font-weight: 700;
                        color: var(--chat-primary);
                    }

                    .new-badge {
                        display: inline-block;
                        background: #ff4d4f;
                        color: white;
                        font-size: 10px;
                        font-weight: 800;
                        padding: 2px 6px;
                        border-radius: 4px;
                        margin-left: 8px;
                        text-transform: uppercase;
                    }
                </style>
            </head>

            <body class="dashboard-body">
                <c:if test="${role == 'patient'}">
                    <%@ include file="/WEB-INF/views/components/header.jsp" %>
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
                        <c:if test="${role != 'patient'}">
                            <div class="chat-page-title-box" style="margin-bottom: 30px;">
                                <div class="greeting">
                                    <span class="greeting-icon">&#128172;</span>
                                    <div class="greeting-content">
                                        <h2>Messaging</h2>
                                        <p class="date-time">Communicate with your ${roleText}</p>
                                    </div>
                                </div>
                            </div>
                        </c:if>

                        <div class="chat-layout">
                            <!-- Contact List - Hidden for Patients -->
                            <c:if test="${role != 'patient'}">
                                <div class="contact-list">
                                    <c:forEach var="c" items="${contacts}">
                                        <c:set var="contactId" value="${role == 'patient' ? c.id : c.nic}" />
                                        <c:set var="unread" value="${unreadCounts[contactId]}" />
                                        <div class="contact-item ${contactId == receiverId ? 'active' : ''} ${unread > 0 ? 'has-unread' : ''}"
                                            onclick="window.location.href='?with=${contactId}'">
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
                                                <p>${role == 'patient' ? 'Pharmacist' : 'Patient'}</p>
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
                                                <c:choose>
                                                    <c:when test="${role == 'patient'}">
                                                        <strong id="chatting-with">Pharmacy Support</strong>
                                                    </c:when>
                                                    <c:otherwise>
                                                        <c:forEach var="ct" items="${contacts}">
                                                            <c:set var="ctId"
                                                                value="${role == 'patient' ? ct.id : ct.nic}" />
                                                            <c:if test="${ctId == receiverId}">
                                                                <c:set var="receiverName" value="${ct.name}" />
                                                            </c:if>
                                                        </c:forEach>
                                                        <strong id="chatting-with">${not empty receiverName ?
                                                            receiverName :
                                                            'Loading...'}</strong>
                                                    </c:otherwise>
                                                </c:choose>
                                            </div>
                                            <div class="chat-messages" id="message-container">
                                                <!-- Messages loaded via JS -->
                                            </div>
                                            <form class="chat-input-area" id="chat-form">
                                                <input type="text" class="chat-input" id="msg-input"
                                                    placeholder="Type a message..." autocomplete="off">
                                                <button type="submit" class="send-btn">
                                                    🚀
                                                </button>
                                            </form>
                                        </div>
                                    </c:when>
                                    <c:otherwise>
                                        <div class="no-selection">
                                            <h3>Select a conversation to start chatting</h3>
                                            <p>Communicate directly with your healthcare providers</p>
                                        </div>
                                    </c:otherwise>
                                </c:choose>
                            </div>
                        </div>
                    </main>
                </div>

                <script>
                    const userId = "${userId}";
                    const receiverId = "${receiverId}";
                    const contextPath = "${pageContext.request.contextPath}";
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
                            fetch(contextPath + '/chat?receiverId=' + receiverId + '&lastId=' + lastId)
                                .then(res => res.json())
                                .then(data => {
                                    data.forEach(m => {
                                        const div = document.createElement('div');
                                        div.className = 'message ' + (m.senderId === userId ? 'sent' : 'received');
                                        let msgHtml = m.message;
                                        if (!m.isRead && m.senderId !== userId) {
                                            msgHtml += '<span class="new-badge">New</span>';
                                        }
                                        div.innerHTML = msgHtml + '<span class="message-time">' + m.sentAt + '</span>';
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
            </body>

            </html>