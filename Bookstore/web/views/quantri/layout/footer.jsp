<%@ page language="java" contentType="text/html; charset=UTF-8" pageEncoding="UTF-8"%>
<%@ taglib uri="http://java.sun.com/jsp/jstl/core" prefix="c" %>
<c:set var="baseURL" value="${pageContext.request.contextPath}" />

<footer class="admin-footer">
    <div class="footer-content">
        <p>&copy; 2025 BookStore Admin Panel. All rights reserved.</p>
        <p>Version 1.0.0</p>
    </div>
</footer>

<script src="${baseURL}/js/Admin/admin.js"></script>

<style>
    .admin-footer {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        text-align: center;
        color: #6c757d;
        font-size: 0.9rem;
        border-top: 1px solid #dee2e6;
        margin-left: 260px;
        transition: margin-left 0.3s ease;
    }

    body.sidebar-collapsed .admin-footer {
        margin-left: 80px;
    }

    @media (max-width: 768px) {
        .admin-footer {
            margin-left: 0;
        }
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }

    @media (max-width: 600px) {
        .footer-content {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>



















