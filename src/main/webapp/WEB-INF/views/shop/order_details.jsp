<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>
            <html>

            <head>
                <title>Order #${order.id} | Medora</title>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/shop-redesign.css">
            </head>

            <body>
                <div class="container">
                    <div class="order-header">
                        <div>
                            <a href="${pageContext.request.contextPath}/router/shop/orders" class="text-muted"
                                style="text-decoration: none;">← Orders</a>
                            <h1>Order #${order.id}
                                <span class="badge badge-paid">Paid</span>
                                <span class="badge <c:choose>
                        <c:when test=" ${order.status=='PENDING' }">badge-pending</c:when>
                                    <c:when test="${order.status == 'APPROVED'}">badge-unfulfilled</c:when>
                                    <c:when test="${order.status == 'COMPLETED'}">badge-paid</c:when>
                                    <c:otherwise>badge-rejected</c:otherwise>
                                    </c:choose>">${order.status}
                                </span>
                            </h1>
                            <div class="text-muted">
                                <fmt:formatDate value="${order.createdAt}" pattern="MM.dd.yyyy 'at' hh:mm a" />
                            </div>
                        </div>
                        <div class="header-actions">
                            <c:if test="${order.status == 'APPROVED'}">
                                <a href="${pageContext.request.contextPath}/router/shop/orders/complete?id=${order.id}"
                                    class="btn btn-primary">Pay & Pickup</a>
                            </c:if>
                            <div class="btn btn-outline" style="margin-left: 10px;">•••</div>
                        </div>
                    </div>

                    <div class="order-grid">
                        <div class="order-main">
                            <div class="card">
                                <div class="order-section-title">${order.status} ${order.items.size()}</div>
                                <div class="order-item-list">
                                    <c:forEach var="item" items="${order.items}">
                                        <div class="order-item-row">
                                            <img src="${pageContext.request.contextPath}/${not empty item.medicineImage ? item.medicineImage : 'images/placeholder-medicine.png'}"
                                                alt="${item.medicineName}" class="order-item-thumb"
                                                onerror="this.src='${pageContext.request.contextPath}/images/placeholder-medicine.png'">
                                            <div class="order-item-name">
                                                ${item.medicineName}
                                                <div class="text-muted">Qty: ${item.quantity} units</div>
                                            </div>
                                            <div class="order-item-meta">
                                                <div class="order-item-price">LKR
                                                    <fmt:formatNumber value="${item.price}" type="number"
                                                        minFractionDigits="2" maxFractionDigits="2" />
                                                </div>
                                                <div class="text-muted">Total: LKR
                                                    <fmt:formatNumber value="${item.price * item.quantity}"
                                                        type="number" minFractionDigits="2" maxFractionDigits="2" />
                                                </div>
                                            </div>
                                        </div>
                                    </c:forEach>
                                </div>
                            </div>

                            <div class="card">
                                <div class="order-section-title">Delivery</div>
                                <div class="cart-summary-row">
                                    <div style="display: flex; align-items: center;">
                                        <div
                                            style="width: 40px; height: 40px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                                            📦</div>
                                        <div>
                                            <strong>Self Pickup</strong>
                                            <div class="text-muted">Medora Pharmacy Main Branch</div>
                                        </div>
                                    </div>
                                    <div>LKR 0.00</div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="order-section-title">Payment Summary</div>
                                <div class="cart-summary-row">
                                    <span class="text-muted">Subtotal (${order.items.size()} items)</span>
                                    <span>LKR
                                        <fmt:formatNumber value="${order.totalAmount}" type="number"
                                            minFractionDigits="2" maxFractionDigits="2" />
                                    </span>
                                </div>
                                <div class="cart-summary-row">
                                    <span class="text-muted">Delivery</span>
                                    <span>LKR 0.00</span>
                                </div>
                                <div class="cart-summary-row">
                                    <span class="text-muted">Tax (Included)</span>
                                    <span>LKR 0.00</span>
                                </div>
                                <div class="cart-summary-total">
                                    <span>Total paid by customer</span>
                                    <span>LKR
                                        <fmt:formatNumber value="${order.totalAmount}" type="number"
                                            minFractionDigits="2" maxFractionDigits="2" />
                                    </span>
                                </div>
                            </div>

                            <c:if test="${not empty order.notes}">
                                <div class="card" style="border-left: 4px solid var(--warning);">
                                    <div class="order-section-title">Pharmacist Notes</div>
                                    <p>${order.notes}</p>
                                </div>
                            </c:if>
                        </div>

                        <div class="order-sidebar">
                            <div class="card customer-card">
                                <div class="order-section-title">Customer</div>
                                <div class="customer-info-item" style="display: flex; align-items: center;">
                                    <div
                                        style="width: 40px; height: 40px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 12px;">
                                        ${sessionScope.patient.name.charAt(0)}
                                    </div>
                                    <div>
                                        <div class="customer-info-value">${sessionScope.patient.name}</div>
                                        <div class="text-muted">NIC: ${sessionScope.patient.nic}</div>
                                    </div>
                                </div>

                                <div class="customer-info-item">
                                    <div class="customer-info-label">Contact info</div>
                                    <div class="customer-info-value">${sessionScope.patient.email}</div>
                                    <div class="customer-info-value">${sessionScope.patient.phone}</div>
                                </div>

                                <div class="customer-info-item">
                                    <div class="customer-info-label">Shipping Address</div>
                                    <div class="customer-info-value">${sessionScope.patient.address}</div>
                                </div>

                                <div class="customer-info-item">
                                    <div class="customer-info-label">Billing Address</div>
                                    <div class="customer-info-value">${sessionScope.patient.address}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>

            </html>