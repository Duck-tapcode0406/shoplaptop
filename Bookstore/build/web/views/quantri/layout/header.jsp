<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="admin" value="${sessionScope.user}" />

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
        <h1 class="logo">
            <a href="${baseURL}/admin/dashboard">
                <i class="fa-solid fa-book"></i> BookStore Admin
            </a>
        </h1>
    </div>
    <div class="header-right">
        <div class="user-info">
            <span class="welcome-text">Xin chào, <strong>${admin != null ? admin.hoVaTen : 'Admin'}</strong></span>
            <div class="user-menu">
                <button class="user-btn">
                    <i class="fa-solid fa-user-circle"></i>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="user-dropdown">
                    <a href="${baseURL}/admin/config/settings"><i class="fa-solid fa-cog"></i> Cài đặt</a>
                    <a href="${baseURL}/trang-chu" target="_blank"><i class="fa-solid fa-external-link-alt"></i> Xem website</a>
                    <hr>
                    <a href="${baseURL}/dang-xuat"><i class="fa-solid fa-sign-out-alt"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    .admin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .header-left {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .sidebar-toggle {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        cursor: pointer;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .sidebar-toggle:hover {
        background: rgba(255,255,255,0.3);
    }

    .logo a {
        color: white;
        text-decoration: none;
        font-size: 1.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-right {
        display: flex;
        align-items: center;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .welcome-text {
        font-size: 0.95rem;
    }

    .user-menu {
        position: relative;
    }

    .user-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .user-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .user-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        min-width: 200px;
        visibility: hidden;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1001;
        pointer-events: none;
    }

    /* Tạo cầu nối vô hình giữa button và dropdown để hover không bị gián đoạn */
    .user-dropdown::before {
        content: '';
        position: absolute;
        bottom: 100%;
        right: 0;
        width: 100%;
        height: 8px;
        background: transparent;
    }

    /* Hiển thị dropdown khi hover vào user-menu hoặc user-dropdown */
    .user-menu:hover .user-dropdown,
    .user-menu.active .user-dropdown,
    .user-dropdown:hover {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .user-dropdown a {
        display: block;
        padding: 0.75rem 1rem;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .user-dropdown a:hover {
        background: #f8f9fa;
        color: #667eea;
    }

    .user-dropdown hr {
        margin: 0.5rem 0;
        border: none;
        border-top: 1px solid #dee2e6;
    }
</style>

