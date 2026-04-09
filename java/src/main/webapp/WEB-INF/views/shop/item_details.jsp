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
                    <!-- Breadcrumbs - Moved inside for better flow -->
                    <div class="breadcrumb">
                        <a href="${pageContext.request.contextPath}/router/shop">Shop</a>
                        <span class="sep">&rsaquo;</span>
                        <a
                            href="${pageContext.request.contextPath}/router/shop?category=${medicine.category}">${medicine.category}</a>
                        <span class="sep">&rsaquo;</span>
                        <span class="current">${medicine.name}</span>
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
                            <div class="med-category" style="font-size: 0.75rem; margin-bottom: 5px;">
                                ${medicine.category}</div>
                            <h1
                                style="font-size: 2.4rem; font-weight: 800; color: #1e293b; margin: 0 0 8px 0; line-height: 1.1;">
                                ${medicine.name}
                            </h1>
                            <p style="font-size: 1rem; color: #64748b; margin-bottom: 20px;">
                                Generic: <span style="font-weight: 700; color: #334155;">${medicine.genericName}</span>
                            </p>

                            <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
                                <div class="med-badge" style="padding: 4px 12px; font-size: 0.8rem;">
                                    <span>&#128138;</span> ${medicine.dosageForm}
                                </div>
                                <div class="med-badge" style="padding: 4px 12px; font-size: 0.8rem;">
                                    <span>&#9878;</span> ${medicine.strength}
                                </div>
                                <div class="med-badge" style="padding: 4px 12px; font-size: 0.8rem;">
                                    <span>&#127981;</span> ${medicine.manufacturer}
                                </div>
                            </div>

                            <div
                                style="color: #475569; line-height: 1.6; font-size: 1rem; margin-bottom: 25px; max-height: 80px; overflow-y: auto;">
                                ${medicine.description}
                            </div>

                            <!-- Package Banner -->
                            <div class="info-banner" style="margin: 20px 0; padding: 15px;">
                                <h4 style="font-size: 1rem; margin-bottom: 5px;"><span>&#128230;</span> Packaging
                                    Details</h4>
                                <p style="margin:0; font-size: 0.9rem; color: #475569;">
                                    Sold per <strong>${medicine.sellingUnit}</strong>.
                                    Each unit contains <strong>${medicine.unitQuantity}
                                        ${medicine.dosageForm}(s)</strong>.
                                </p>
                            </div>

                            <hr style="border:0; border-top: 1px solid #f1f5f9; margin: 10px 0;">

                            <div class="purchase-actions">
                                <div class="price-side">
                                    <div class="price-label">Price per ${medicine.sellingUnit}</div>
                                    <div class="price-value">
                                        <span class="currency">LKR</span>
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
                                    class="purchase-form">
                                    <input type="hidden" name="id" value="${medicine.id}">

                                    <div class="quantity-picker">
                                        <label>Quantity</label>
                                        <div class="qty-input-wrapper">
                                            <input type="number" name="quantity" value="1" min="1"
                                                max="${medicine.quantityInStock}">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-add-cart">
                                        <span>&#128722;</span> Add to Cart
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                    <!-- Footer Nav Link -->
                    <div class="details-footer">
                        <a href="${pageContext.request.contextPath}/router/shop">
                            <span>&larr;</span> Return to Shop Catalog
                        </a>
                    </div>
                </div>

            </body>

            </html>