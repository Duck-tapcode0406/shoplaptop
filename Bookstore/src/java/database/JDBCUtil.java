package database;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class JDBCUtil {

    public static Connection getConnection() {
        Connection c = null;
        try {
            try {
                 Class.forName("com.mysql.cj.jdbc.Driver");
            } catch (ClassNotFoundException e) {
                 System.err.println("Không tìm thấy MySQL JDBC Driver!");
                 e.printStackTrace();
                 return null;
            }

            String dbName = "bookstore"; 

            String url = "jdbc:mysql://localhost:3306/" + dbName + "?useSSL=false&serverTimezone=UTC&characterEncoding=UTF-8";
            String username = "root";
            String password = ""; 

            c = DriverManager.getConnection(url, username, password);

        } catch (SQLException e) {
            System.err.println("Kết nối CSDL '" + "bookstore" + "' thất bại!");
            e.printStackTrace();
        }
        return c; 
    }
    public static void closeConnection(Connection c) {
        try {
            if (c != null && !c.isClosed()) {
                c.close();
            }
        } catch (SQLException e) {
            System.err.println("Lỗi khi đóng kết nối CSDL!");
            e.printStackTrace();
        }
    }
    public static void main(String[] args) {
        Connection testConnection = getConnection();
        if (testConnection != null) {
            System.out.println("Kiểm tra kết nối thành công!");
            closeConnection(testConnection);
        } else {
            System.out.println("Kiểm tra kết nối thất bại!");
        }
    }
}