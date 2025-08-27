package com.example.base;

import javax.servlet.*;
import javax.servlet.annotation.WebFilter;
import javax.servlet.http.HttpServletRequest;
import java.io.IOException;
import java.net.StandardSocketOptions;

@WebFilter("/addalien")
public class filterexample implements Filter {

    public void destroy() {
        // cleanup code
    }


    public void doFilter(ServletRequest request, ServletResponse response, FilterChain chain)
            throws IOException, ServletException {

        HttpServletRequest req = (HttpServletRequest) request;
       int aid = Integer.parseInt(request.getParameter("aid"));
         if(aid<1){
             chain.doFilter(request, response);
         }else{
             System.out.println("invalid value");
         }





    }

    public void init(FilterConfig filterConfig) throws ServletException {
        // initialization code
    }


}
