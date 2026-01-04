<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />
<c:set var="currentPath" value="${requestScope['jakarta.servlet.forward.request_uri']}" />

<aside class="admin-sidebar" id="adminSidebar">
    <nav class="sidebar-nav">
        <div class="nav-section">
            <h3 class="nav-title">Tổng quan</h3>
            <ul class="nav-list">
                <li>
                    <a href="${baseURL}/admin/dashboard" class="nav-link ${currentPath.contains('/dashboard') ? 'active' : ''}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/statistics" class="nav-link ${currentPath.contains('/statistics') ? 'active' : ''}">
                        <i class="fa-solid fa-chart-bar"></i>
                        <span>Thống kê</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <h3 class="nav-title">Quản lý</h3>
            <ul class="nav-list">
                <li>
                    <a href="${baseURL}/admin/products" class="nav-link ${currentPath.contains('/products') ? 'active' : ''}">
                        <i class="fa-solid fa-book"></i>
                        <span>Sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/orders" class="nav-link ${currentPath.contains('/orders') ? 'active' : ''}">
                        <i class="fa-solid fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/users" class="nav-link ${currentPath.contains('/users') ? 'active' : ''}">
                        <i class="fa-solid fa-users"></i>
                        <span>Người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/news" class="nav-link ${currentPath.contains('/news') ? 'active' : ''}">
                        <i class="fa-solid fa-newspaper"></i>
                        <span>Tin tức</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/promotions" class="nav-link ${currentPath.contains('/promotions') ? 'active' : ''}">
                        <i class="fa-solid fa-tags"></i>
                        <span>Khuyến mãi</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <h3 class="nav-title">Danh mục</h3>
            <ul class="nav-list">
                <li>
                    <a href="${baseURL}/admin/categories" class="nav-link ${currentPath.contains('/categories') ? 'active' : ''}">
                        <i class="fa-solid fa-list"></i>
                        <span>Thể loại</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/authors" class="nav-link ${currentPath.contains('/authors') ? 'active' : ''}">
                        <i class="fa-solid fa-user-pen"></i>
                        <span>Tác giả</span>
                    </a>
                </li>
                <li>
                    <a href="${baseURL}/admin/publishers" class="nav-link ${currentPath.contains('/publishers') ? 'active' : ''}">
                        <i class="fa-solid fa-building"></i>
                        <span>Nhà xuất bản</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="nav-section">
            <h3 class="nav-title">Hệ thống</h3>
            <ul class="nav-list">
                <li>
                    <a href="${baseURL}/admin/config/settings" class="nav-link ${currentPath.contains('/config') ? 'active' : ''}">
                        <i class="fa-solid fa-cog"></i>
                        <span>Cài đặt</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>

<style>
    .admin-sidebar {
        position: fixed;
        left: 0;
        top: 70px;
        width: 260px;
        height: calc(100vh - 70px);
        background: white;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        overflow-y: auto;
        transition: all 0.3s ease;
        z-index: 999;
    }

    .admin-sidebar.collapsed {
        width: 80px;
    }

    .admin-sidebar.collapsed .nav-link span,
    .admin-sidebar.collapsed .nav-title {
        display: none;
    }

    .admin-sidebar.collapsed .nav-link {
        justify-content: center;
    }

    .sidebar-nav {
        padding: 1.5rem 0;
    }

    .nav-section {
        margin-bottom: 2rem;
    }

    .nav-title {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #6c757d;
        padding: 0 1.5rem;
        margin-bottom: 0.75rem;
        letter-spacing: 0.5px;
    }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        color: #495057;
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }

    .nav-link:hover {
        background: #f8f9fa;
        color: #667eea;
        border-left-color: #667eea;
    }

    .nav-link.active {
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
        color: #667eea;
        border-left-color: #667eea;
        font-weight: 600;
    }

    .nav-link i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .nav-link span {
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }

        .admin-sidebar.show {
            transform: translateX(0);
        }
    }
</style>



















