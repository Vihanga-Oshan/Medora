<%@page contentType="text/html;charset=UTF-8" language="java" %>
<html>
<head>
    <title>Title</title>
</head>
<body>
<%  if(session.getAttribute("username") == null){
        response.sendRedirect("login.jsp");
    }
%>

yooooooooo<br></b><br><br><br>
welcome ${username}<br><br><br>



<a href="videos.jsp">videos</a><br><br>

<form action="logout" method="post">
    <button type="submit">Logout</button>
</form>
</body>
</html>
