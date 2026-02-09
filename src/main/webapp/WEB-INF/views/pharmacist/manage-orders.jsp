<%@ page contentType="text/html;charset=UTF-8" language="java" %>
    <%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
        <html>

        <head>
            <title>Manage Orders</title>
            <link rel="stylesheet" href="${pageContext.request.contextPath}/css/style.css">
            <style>
                .container {
                    padding: 20px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    background: white;
                }

                th,
                td {
                    padding: 12px;
                    border: 1px solid #ddd;
                }

                .btn-approve {
                    background: green;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    cursor: pointer;
                    text-decoration: none;
                }

                .btn-reject {
                    background: red;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    cursor: pointer;
                    text-decoration: none;
                }
            </style>
        </head>

        <body>
            <jsp:include page="/WEB-INF/views/components/header.jsp" /> <!-- Assuming header exists -->

            <div class="container">
                <h2>Customer Orders</h2>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient NIC</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <c:forEach var="order" items="${orders}">
                            <tr>
                                <td>${order.id}</td>
                                <td>${order.patientNic}</td>
                                <td>${order.createdAt}</td>
                                <td>$${order.totalAmount}</td>
                                <td>${order.status}</td>
                                <td>
                                    <c:if test="${order.status == 'PENDING'}">
                                        <a href="${pageContext.request.contextPath}/router/pharmacist/orders/update?id=${order.id}&status=APPROVED"
                                            class="btn-approve">Approve</a>
                                        <a href="${pageContext.request.contextPath}/router/pharmacist/orders/update?id=${order.id}&status=REJECTED"
                                            class="btn-reject">Reject</a>
                                    </c:if>
                                </td>
                            </tr>
                        </c:forEach>
                    </tbody>
                </table>
            </div>
        </body>

        </html>