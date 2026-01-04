/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Classes/Class.java to edit this template
 */
package util;

import util.PasswordUtil; // Import class PasswordUtil của bạn

public class GeneratePasswordHash {
    public static void main(String[] args) {
        String plainPassword = "pass123"; // Mật khẩu bạn muốn
        String hashedPassword = PasswordUtil.hash(plainPassword);
        System.out.println("Mật khẩu gốc: " + plainPassword);
        System.out.println("Mật khẩu băm (BCrypt): " + hashedPassword);
    }
}