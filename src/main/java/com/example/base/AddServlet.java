package com.example.base;

import javax.servlet.RequestDispatcher;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;


public class AddServlet extends HttpServlet {
    public void doGet(HttpServletRequest request, HttpServletResponse res) throws java.io.IOException, ServletException {

        student s = new student(101, "navin");

        request.setAttribute("student",s);
        request.getRequestDispatcher("/display.jsp").forward(request, res);
    }
}
