package com.example.base.controller;

import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;
import java.io.IOException;

@WebServlet(name = "login", urlPatterns = "/login")
public class login extends HttpServlet {

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws IOException {
        request.setCharacterEncoding("UTF-8");

        String username = request.getParameter("username");
        String pass  = request.getParameter("pass");

        if (username == null || pass == null) {
            response.sendRedirect(request.getContextPath() + "/login.jsp");
            return;
        }
        if ("yankee".equals(username) && "admin".equals(pass)) {
            HttpSession session = request.getSession();
            session.setAttribute("username", username);
            response.sendRedirect(request.getContextPath() + "/welcome.jsp");
        }else{
            response.sendRedirect(request.getContextPath() + "/error.jsp");
        }

    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws IOException {
        // Visiting /login directly shows the form
        response.sendRedirect(request.getContextPath() + "/login.jsp");
    }
}
