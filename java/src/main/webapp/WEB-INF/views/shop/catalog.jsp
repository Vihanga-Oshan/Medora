<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Medora Pharmacy | Smart Catalog</title>
            <link rel="stylesheet"
                href="${pageContext.request.contextPath}/css/shop-modern.css?v=<%= System.currentTimeMillis() %>">
        </head>

        <body>
            <!-- Include top navigation -->
            <%@ include file="/WEB-INF/views/components/header.jsp" %>

                <div class="shop-layout">

                    <!-- Sidebar -->
                    <aside class="shop-sidebar">
                        <div class="sidebar-card">
                            <div class="sidebar-title">Categories</div>
                            <ul class="category-list">
                                <li>
                                    <a href="${pageContext.request.contextPath}/router/shop"
                                        class="${empty currentCategory ? 'active' : ''}">
                                        All Medicines
                                    </a>
                                </li>
                                <c:forEach var="cat" items="${categories}">
                                    <li>
                                        <a href="${pageContext.request.contextPath}/router/shop?category=${cat.name}"
                                            class="${currentCategory == cat.name ? 'active' : ''}">
                                            ${cat.name}
                                        </a>
                                    </li>
                                </c:forEach>
                            </ul>
                        </div>

                        <div class="sidebar-card">
                            <div class="sidebar-title">Quick Links</div>
                            <ul class="category-list">
                                <li><a href="${pageContext.request.contextPath}/router/shop/orders">
                                        &#128230; My Orders
                                    </a></li>
                                <li><a href="${pageContext.request.contextPath}/router/patient/dashboard">
                                        &larr; Back to Dashboard
                                    </a></li>
                            </ul>
                        </div>
                    </aside>

                    <!-- Main Content -->
                    <main class="shop-main">

                        <!-- Top Bar Actions (Search & Cart) -->
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                            <form action="${pageContext.request.contextPath}/router/shop/search" method="get"
                                class="search-container">
                                <input type="text" name="q" placeholder="Search product" value="${searchQuery}">
                                <button type="submit">&#128269;</button> <!-- Unicode Search Icon -->
                            </form>

                            <div class="header-actions">
                                <a href="${pageContext.request.contextPath}/router/shop/cart" class="cart-link">
                                    <span class="icon-span">&#128722;</span> <!-- Unicode Basket Icon -->
                                    <span>Chart</span>
                                    <c:if test="${not empty sessionScope.cart && sessionScope.cart.size() > 0}">
                                        <span class="cart-badge">${sessionScope.cart.size()}</span>
                                    </c:if>
                                </a>
                                <a href="${pageContext.request.contextPath}/router/shop/orders" class="cart-link">
                                    <span class="icon-span">&#128221;</span> <!-- Unicode List Icon -->
                                    <span>Orders</span>
                                </a>
                            </div>
                        </div>

                        <!-- Bento-Style Hero Banner -->
                        <section class="shop-hero">
                            <div class="main-promo-card">
                                <div class="promo-tag">${not empty mainPromo.tag ? mainPromo.tag : 'Biggest Offer
                                    Revealed'}</div>
                                <h1>${not empty mainPromo.title ? mainPromo.title : 'MORE DEALS INSIDE<br>UP TO 50%
                                    OFF'}</h1>
                                <p>${not empty mainPromo.desc ? mainPromo.desc : 'Premium medical essentials for every
                                    household.'}</p>
                                <a href="#" class="view-all"
                                    style="color: #1a2b3c; text-decoration: underline;">Wishlist Now &raquo;</a>
                            </div>

                            <c:choose>
                                <c:when test="${not empty subPromos}">
                                    <c:forEach var="promo" items="${subPromos}">
                                        <div class="sub-promo-card" style="background: ${promo.bg};">
                                            <div>
                                                <div class="promo-tag"
                                                    style="background: rgba(0,0,0,0.05); color: #333;">
                                                    ${promo.tag}</div>
                                                <h3>${promo.title}</h3>
                                                <p>${promo.desc}</p>
                                            </div>
                                            <div class="price-box">${promo.offer}</div>
                                        </div>
                                    </c:forEach>
                                </c:when>
                                <c:otherwise>
                                    <!-- Fallback static promos if controller hasn't recompiled yet -->
                                    <div class="sub-promo-card" style="background: #fdf2f2;">
                                        <div>
                                            <div class="promo-tag" style="background: rgba(0,0,0,0.05); color: #333;">
                                                NEW ARRIVAL</div>
                                            <h3>First Aid Essentials</h3>
                                            <p>Complete kits for emergencies.</p>
                                        </div>
                                        <div class="price-box">UP TO 30% OFF</div>
                                    </div>
                                    <div class="sub-promo-card" style="background: #f0f7ff;">
                                        <div>
                                            <div class="promo-tag" style="background: rgba(0,0,0,0.05); color: #333;">
                                                SUGGESTION</div>
                                            <h3>Vitamins & Energy</h3>
                                            <p>Boost your daily vitality.</p>
                                        </div>
                                        <div class="price-box">Starting from $19</div>
                                    </div>
                                </c:otherwise>
                            </c:choose>
                        </section>



                        <!-- Grid -->
                        <div class="medicine-grid">
                            <c:forEach var="med" items="${medicines}">
                                <div class="medicine-card"
                                    onclick="location.href='${pageContext.request.contextPath}/router/shop/item?id=${med.id}';"
                                    style="cursor: pointer;">
                                    <div class="card-img-wrapper">
                                        <img src="${not empty med.imagePath ? med.imagePath : pageContext.request.contextPath.concat('/assets/placeholder-med.png')}"
                                            alt="${med.name}"
                                            onerror="this.src='${pageContext.request.contextPath}/assets/logo.png';">
                                    </div>

                                    <div class="card-body">
                                        <div class="med-category">${med.category}</div>
                                        <h3 class="med-title">${med.name}</h3>

                                        <div class="price-container">
                                            <div class="price-tag">$${med.price}</div>
                                            <div class="old-price">$${med.price + 50}</div>
                                        </div>

                                        <form action="${pageContext.request.contextPath}/router/shop/cart/add"
                                            method="post" onsubmit="event.stopPropagation();"
                                            style="margin-top: 15px; width: 100%;">
                                            <input type="hidden" name="id" value="${med.id}">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" class="btn-add-cart"
                                                style="width: 100%; border-radius: 8px; gap: 8px;"
                                                onclick="event.stopPropagation();">
                                                <span>&#10010;</span> Add to Cart <!-- Unicode Plus/Medical Icon -->
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </c:forEach>
                        </div>

                        <c:if test="${empty medicines}">
                            <div style="text-align: center; padding: 50px; color: #666;">
                                <div style="font-size: 3em; margin-bottom: 20px; color: #ddd;">&#128230;</div>
                                <p>No medicines found.</p>
                                <a href="${pageContext.request.contextPath}/router/shop"
                                    style="color:#007dca; text-decoration:none;">Clear Filters</a>
                            </div>
                        </c:if>

                    </main>

                    <!-- Right Dashboard Panel -->
                    <aside class="right-panel">
                        <div class="widget-card">
                            <div class="widget-title">Recently Viewed</div>
                            <c:choose>
                                <c:when test="${not empty recentlyViewed}">
                                    <c:forEach var="item" items="${recentlyViewed}">
                                        <div class="widget-item" style="cursor: pointer;"
                                            onclick="location.href='${pageContext.request.contextPath}/router/shop/item?id=${item.id}'">
                                            <img src="${not empty item.imagePath ? item.imagePath : pageContext.request.contextPath.concat('/assets/logo.png')}"
                                                alt="${item.name}">
                                            <div class="widget-info">
                                                <h4>${item.name}</h4>
                                                <p>${item.category}</p>
                                            </div>
                                        </div>
                                    </c:forEach>
                                    <a href="#" class="view-all">Clear History</a>
                                </c:when>
                                <c:otherwise>
                                    <p style="font-size: 0.85rem; color: #94a3b8; padding: 10px 0;">No items viewed yet.
                                    </p>
                                    <!-- Optional: Show one static example if history is empty to show it "exists" -->
                                </c:otherwise>
                            </c:choose>
                        </div>

                        <div class="widget-card">
                            <div class="widget-title">Suggestions for You</div>
                            <c:choose>
                                <c:when test="${not empty suggestions}">
                                    <c:forEach var="item" items="${suggestions}">
                                        <div class="widget-item" style="cursor: pointer;"
                                            onclick="location.href='${pageContext.request.contextPath}/router/shop/item?id=${item.id}'">
                                            <img src="${not empty item.imagePath ? item.imagePath : pageContext.request.contextPath.concat('/assets/logo.png')}"
                                                alt="${item.name}">
                                            <div class="widget-info">
                                                <h4>${item.name}</h4>
                                                <p>${item.category}</p>
                                            </div>
                                        </div>
                                    </c:forEach>
                                </c:when>
                                <c:otherwise>
                                    <!-- Fallback static suggestions if DB is empty -->
                                    <div class="widget-item">
                                        <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medicine">
                                        <div class="widget-info">
                                            <h4>Paracetamol 500mg</h4>
                                            <p>Pain Relief</p>
                                        </div>
                                    </div>
                                    <div class="widget-item">
                                        <img src="${pageContext.request.contextPath}/assets/logo.png" alt="Medicine">
                                        <div class="widget-info">
                                            <h4>Amoxicillin</h4>
                                            <p>Antibiotics</p>
                                        </div>
                                    </div>
                                </c:otherwise>
                            </c:choose>
                            <a href="${pageContext.request.contextPath}/router/shop" class="view-all">See More</a>
                        </div>
                    </aside>

                </div>



        </body>

        </html>