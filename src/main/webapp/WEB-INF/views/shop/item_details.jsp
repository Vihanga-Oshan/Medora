<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib prefix="c" uri="http://java.sun.com/jsp/jstl/core" %>
        <%@ taglib prefix="fmt" uri="http://java.sun.com/jsp/jstl/fmt" %>
            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>${medicine.name} | Medora Shop</title>
                <link rel="stylesheet" href="${pageContext.request.contextPath}/css/shop-modern.css">
            </head>

            <body>
                <!-- Medora Global Header -->
                <jsp:include page="/WEB-INF/views/components/header.jsp" />

                <div class="details-container">
                    <!-- Breadcrumbs -->
                    <div class="breadcrumb">
                        <a href="${pageContext.request.contextPath}/router/shop">Shop</a>
                        <span>&rsaquo;</span>
                        <a
                            href="${pageContext.request.contextPath}/router/shop?category=${medicine.category}">${medicine.category}</a>
                        <span>&rsaquo;</span>
                        <span style="color: #94a3b8;">${medicine.name}</span>
                    </div>

                    <!-- Main Showcase Card -->
                    <div class="item-showcase">

                        <!-- Left: Visual Side -->
                        <div class="item-image-box">
                            <img src="${not empty medicine.imagePath ? medicine.imagePath : pageContext.request.contextPath.concat('/assets/logo.png')}"
                                alt="${medicine.name}"
                                onerror="this.src='${pageContext.request.contextPath}/assets/logo.png';">
                        </div>

                        <!-- Right: Information Side -->
                        <div class="info-side">
                            <div class="med-category">${medicine.category}</div>
                            <h1
                                style="font-size: 2.8rem; font-weight: 800; color: #1e293b; margin: 0 0 10px 0; line-height: 1.1;">
                                ${medicine.name}
                            </h1>
                            <p style="font-size: 1.1rem; color: #64748b; margin-bottom: 25px;">
                                Generic: <span style="font-weight: 700; color: #334155;">${medicine.genericName}</span>
                            </p>

                            <div style="display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap;">
                                <div class="med-badge"><span>&#128138;</span> ${medicine.dosageForm}</div>
                                <div class="med-badge"><span>&#9878;</span> ${medicine.strength}</div>
                                <div class="med-badge"><span>&#127981;</span> ${medicine.manufacturer}</div>
                            </div>

                            <div style="color: #475569; line-height: 1.7; font-size: 1.05rem; margin-bottom: 35px;">
                                ${medicine.description}
                            </div>

                            <!-- Package Banner -->
                            <div class="info-banner">
                                <h4><span>&#128230;</span> Packaging Details</h4>
                                <p style="margin:0; font-size: 0.95rem; color: #475569;">
                                    Sold per <strong>${medicine.sellingUnit}</strong>.
                                    Each unit contains <strong>${medicine.unitQuantity}
                                        ${medicine.dosageForm}(s)</strong>.
                                </p>
                            </div>

                            <hr style="border:0; border-top: 1px solid #f1f5f9; margin: 40px 0;">

                            <!-- Purchase Actions -->
                            <div
                                style="display: flex; align-items: flex-end; justify-content: space-between; gap: 40px; flex-wrap: wrap;">
                                <div class="price-side">
                                    <div
                                        style="font-size: 0.9rem; color: #94a3b8; font-weight: 700; text-transform: uppercase;">
                                        Price per ${medicine.sellingUnit}</div>
                                    <div
                                        style="font-size: 2.5rem; font-weight: 800; color: #0f172a; display: flex; align-items: baseline; gap: 8px;">
                                        <span style="font-size: 1.2rem; color: #94a3b8;">LKR</span>
                                        <fmt:formatNumber value="${medicine.price}" type="number" minFractionDigits="2"
                                            maxFractionDigits="2" />
                                    </div>

                                    <div
                                        class="stock-indicator ${medicine.quantityInStock > 0 ? 'in-stock' : 'out-of-stock'}">
                                        <span>&#9679;</span> ${medicine.quantityInStock > 0 ? 'Available in Stock' :
                                        'Out of Stock'}
                                    </div>
                                </div>

                                <form action="${pageContext.request.contextPath}/router/shop/cart/add" method="post"
                                    style="display: flex; gap: 20px; align-items: flex-end;">
                                    <input type="hidden" name="id" value="${medicine.id}">

                                    <div class="quantity-picker">
                                        <label>Quantity</label>
                                        <div class="qty-input-wrapper">
                                            <input type="number" name="quantity" value="1" min="1"
                                                max="${medicine.quantityInStock}">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-add-cart"
                                        style="width: auto; padding: 15px 40px; margin:0; border-radius: 16px;">
                                        <span style="margin-right: 10px; font-size: 1.2rem;">&#128722;</span> Add to
                                        Chart
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                    <!-- Footer Nav Link -->
                    <div style="margin-top: 20px; display: flex; justify-content: center;">
                        <a href="${pageContext.request.contextPath}/router/shop"
                            style="text-decoration: none; color: #94a3b8; font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                            <span>&larr;</span> Return to Shop Catalog
                        </a>
                    </div>
                </div>

            </body>

            </html>