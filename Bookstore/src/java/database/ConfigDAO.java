package database;

import model.Config;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

public class ConfigDAO {
    
    public Config selectByKey(String key) {
        Config config = null;
        String sql = "SELECT * FROM `config` WHERE `key` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, key);
            try (ResultSet rs = st.executeQuery()) {
                if (rs.next()) {
                    config = mapRowToConfig(rs);
                }
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectByKey: " + e.getMessage());
            e.printStackTrace();
        }
        return config;
    }

    public ArrayList<Config> selectAll() {
        ArrayList<Config> configs = new ArrayList<>();
        String sql = "SELECT * FROM `config` ORDER BY `key`";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql);
             ResultSet rs = st.executeQuery()) {
            
            while (rs.next()) {
                configs.add(mapRowToConfig(rs));
            }
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi selectAll: " + e.getMessage());
            e.printStackTrace();
        }
        return configs;
    }

    public int update(Config config) {
        int result = 0;
        String sql = "UPDATE `config` SET `value` = ?, `description` = ? WHERE `key` = ?";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, config.getValue());
            st.setString(2, config.getDescription());
            st.setString(3, config.getKey());
            result = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi update Config: " + e.getMessage());
            e.printStackTrace();
        }
        return result;
    }

    public int insert(Config config) {
        int result = 0;
        String sql = "INSERT INTO `config` (`key`, `value`, `description`) VALUES (?, ?, ?)";
        
        try (Connection con = JDBCUtil.getConnection();
             PreparedStatement st = con.prepareStatement(sql)) {
            
            st.setString(1, config.getKey());
            st.setString(2, config.getValue());
            st.setString(3, config.getDescription());
            result = st.executeUpdate();
        } catch (SQLException e) {
            System.err.println("Lỗi SQL khi insert Config: " + e.getMessage());
            e.printStackTrace();
        }
        return result;
    }

    private Config mapRowToConfig(ResultSet rs) throws SQLException {
        Config config = new Config();
        try {
            config.setKey(rs.getString("key"));
        } catch (SQLException e) {
            config.setKey(rs.getString("`key`"));
        }
        config.setValue(rs.getString("value"));
        config.setDescription(rs.getString("description"));
        return config;
    }
}






















