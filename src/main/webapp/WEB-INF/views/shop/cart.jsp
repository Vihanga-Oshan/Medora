<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>
            <html>

            <head>
                <title>Shopping Cart | Medora</title>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/shop-redesign.css">
            </head>

            <body>
                <div class="container">
                    <div class="order-header">
                        <div>
                            <a href="${pageContext.request.contextPath}/router/shop" class="text-muted"
                                style="text-decoration: none;">← Back to Shop</a>
                            <h1>Your Shopping Cart</h1>
                        </div>
                    </div>

                    <c:choose>
                        <c:when test="${empty cartDetails}">
                            <div class="card" style="text-align: center; padding: 60px;">
                                <h2 class="text-muted">Your cart is empty</h2>
                                <p>Looks like you haven't added any medicines yet.</p>
                                <br>
                                <a href="${pageContext.request.contextPath}/router/shop" class="btn btn-primary">Start
                                    Shopping</a>
                            </div>
                        </c:when>
                        <c:otherwise>
                            <div class="cart-grid">
                                <div class="cart-items-list">
                                    <div class="card">
                                        <c:forEach var="item" items="${cartDetails}">
                                            <div class="cart-item">
                                                <img src="${pageContext.request.contextPath}/${not empty item.medicine.imagePath ? item.medicine.imagePath : 'images/placeholder-medicine.png'}"
                                                    alt="${item.medicine.name}" class="cart-item-img"
                                                    onerror="this.onerror=null; this.src='${pageContext.request.contextPath}/images/placeholder-medicine.png'">
                                                <div class="cart-item-info">
                                                    <h4>${item.medicine.name}</h4>
                                                    <p class="text-muted">${item.medicine.genericName}</p>
                                                    <div class="cart-item-price">
                                                        LKR
                                                        <fmt:formatNumber value="${item.medicine.price}" type="number"
                                                            minFractionDigits="2" maxFractionDigits="2" />
                                                    </div>
                                                </div>
                                                <div class="cart-item-qty">
                                                    <span class="text-muted">Qty:</span>
                                                    <strong>${item.quantity} ${item.medicine.sellingUnit}${item.quantity
                                                        > 1 ? 's' : ''}</strong>
                                                </div>
                                                <a href="${pageContext.request.contextPath}/router/shop/cart/remove?id=${item.medicine.id}"
                                                    class="cart-item-remove">
                                                    Remove
                                                </a>
                                            </div>
                                        </c:forEach>
                                    </div>
                                </div>

                                <div class="cart-summary">
                                    <div class="card">
                                        <h3 style="margin-top: 0;">Order Summary</h3>
                                        <div class="cart-summary-row">
                                            <span class="text-muted">Subtotal</span>
                                            <span>LKR
                                                <fmt:formatNumber value="${cartTotal}" type="number"
                                                    minFractionDigits="2" maxFractionDigits="2" />
                                            </span>
                                        </div>
                                        <div class="cart-summary-row">
                                            <span class="text-muted">Delivery</span>
                                            <span>LKR 0.00</span>
                                        </div>
                                        <div class="cart-summary-total">
                                            <span>Total</span>
                                            <span>LKR
                                                <fmt:formatNumber value="${cartTotal}" type="number"
                                                    minFractionDigits="2" maxFractionDigits="2" />
                                            </span>
                                        </div>
                                        <br>
                                        <a href="${pageContext.request.contextPath}/router/shop/checkout"
                                            class="btn btn-primary" style="width: 100%; box-sizing: border-box;">
                                            Proceed to Checkout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </c:otherwise>
                    </c:choose>
                </div>
            </body>

            </html>