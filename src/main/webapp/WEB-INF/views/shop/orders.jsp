<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>
            <html>

            <head>
                <title>My Orders | Medora</title>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/shop-redesign.css">
                <style>
                    .order-row-card {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 20px;
                        border-bottom: 1px solid var(--border-color);
                    }

                    .order-row-card:last-child {
                        border-bottom: none;
                    }

                    .order-id {
                        font-weight: 600;
                        color: var(--text-main);
                        text-decoration: none;
                    }

                    .order-id:hover {
                        color: var(--primary);
                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <div class="order-header">
                        <div>
                            <a href="${pageContext.request.contextPath}/router/shop" class="text-muted"
                                style="text-decoration: none;">← Back to Shop</a>
                            <h1>My Orders</h1>
                        </div>
                    </div>

                    <c:choose>
                        <c:when test="${empty orders}">
                            <div class="card" style="text-align: center; padding: 60px;">
                                <h2 class="text-muted">No orders found</h2>
                                <p>You haven't placed any orders yet.</p>
                                <br>
                                <a href="${pageContext.request.contextPath}/router/shop" class="btn btn-primary">Start
                                    Shopping</a>
                            </div>
                        </c:when>
                        <c:otherwise>
                            <div class="card" style="padding: 0;">
                                <c:forEach var="order" items="${orders}">
                                    <div class="order-row-card">
                                        <div style="flex: 1;">
                                            <a href="${pageContext.request.contextPath}/router/shop/order?id=${order.id}"
                                                class="order-id">
                                                Order #${order.id}
                                            </a>
                                            <div class="text-muted">
                                                <fmt:formatDate value="${order.createdAt}" pattern="MMM dd, yyyy" />
                                            </div>
                                        </div>

                                        <div style="flex: 1; text-align: center;">
                                            <div class="badge <c:choose>
                                    <c:when test=" ${order.status=='PENDING' }">badge-pending</c:when>
                                                <c:when test="${order.status == 'APPROVED'}">badge-unfulfilled</c:when>
                                                <c:when test="${order.status == 'COMPLETED'}">badge-paid</c:when>
                                                <c:otherwise>badge-rejected</c:otherwise>
                    </c:choose>">${order.status}
                </div>
                </div>

                <div style="flex: 1; text-align: right;">
                    <div style="font-weight: 600;">LKR
                        <fmt:formatNumber value="${order.totalAmount}" type="number" minFractionDigits="2"
                            maxFractionDigits="2" />
                    </div>
                    <a href="${pageContext.request.contextPath}/router/shop/order?id=${order.id}"
                        class="btn btn-outline btn-sm" style="margin-top: 5px;">
                        View Details
                    </a>
                </div>
                </div>
                </c:forEach>
                </div>
                </c:otherwise>
                </c:choose>
                </div>
            </body>

            </html>