package controller.Admin;

import database.ConfigDAO;
import jakarta.servlet.RequestDispatcher;
import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import model.Config;

import java.io.IOException;
import java.util.ArrayList;

@WebServlet(name = "SettingsServlet", urlPatterns = {"/admin/config/settings"})
public class SettingsServlet extends HttpServlet {

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        ConfigDAO configDAO = new ConfigDAO();
        ArrayList<Config> configs = configDAO.selectAll();
        
        // Convert to Map for easier access in JSP
        java.util.Map<String, Config> configMap = new java.util.HashMap<>();
        for (Config config : configs) {
            configMap.put(config.getKey(), config);
        }

        request.setAttribute("configs", configs);
        request.setAttribute("configMap", configMap);
        RequestDispatcher rd = getServletContext().getRequestDispatcher("/views/quantri/config/settings.jsp");
        rd.forward(request, response);
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        request.setCharacterEncoding("UTF-8");
        response.setCharacterEncoding("UTF-8");
        response.setContentType("text/html; charset=UTF-8");

        ConfigDAO configDAO = new ConfigDAO();
        
        // Update all config values
        String[] keys = {"site_name", "site_logo", "site_phone", "site_email", "site_address", 
                        "site_description", "facebook_url", "twitter_url", "instagram_url",
                        "shipping_fee", "free_shipping_threshold"};
        
        for (String key : keys) {
            String value = request.getParameter(key);
            if (value != null) {
                Config config = configDAO.selectByKey(key);
                if (config != null) {
                    config.setValue(value);
                    configDAO.update(config);
                } else {
                    // Create new if doesn't exist
                    config = new Config(key, value, "");
                    configDAO.insert(config);
                }
            }
        }

        request.getSession().setAttribute("successMessage", "Cập nhật cấu hình thành công.");
        response.sendRedirect(request.getContextPath() + "/admin/config/settings");
    }
}




