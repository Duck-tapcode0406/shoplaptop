document.addEventListener("DOMContentLoaded", function () {
  /**
   * ================================================
   * 1️⃣ MENU MOBILE TOGGLE
   * ================================================
   */
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle button");
  const mobileNav = document.getElementById("mobileNav");

  if (mobileMenuToggle && mobileNav) {
    // Mở / đóng menu khi click nút
    mobileMenuToggle.addEventListener("click", function () {
      mobileNav.classList.toggle("active"); // CSS xử lý hiển thị
      const icon = mobileMenuToggle.querySelector("i");
      if (mobileNav.classList.contains("active")) {
        icon.classList.remove("fa-bars");
        icon.classList.add("fa-times");
        mobileMenuToggle.setAttribute("aria-expanded", "true");
        mobileMenuToggle.setAttribute("aria-label", "Đóng menu");
      } else {
        icon.classList.remove("fa-times");
        icon.classList.add("fa-bars");
        mobileMenuToggle.setAttribute("aria-expanded", "false");
        mobileMenuToggle.setAttribute("aria-label", "Mở menu");
      }
    });

    // Đóng menu khi click ra ngoài
    document.addEventListener("click", function (event) {
      const isClickInsideNav = mobileNav.contains(event.target);
      const isClickOnToggle = mobileMenuToggle.contains(event.target);

      if (
        !isClickInsideNav &&
        !isClickOnToggle &&
        mobileNav.classList.contains("active")
      ) {
        mobileNav.classList.remove("active");
        const icon = mobileMenuToggle.querySelector("i");
        icon.classList.remove("fa-times");
        icon.classList.add("fa-bars");
        mobileMenuToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  /**
   * ================================================
   * 2️⃣ TỰ ĐỘNG ẨN THÔNG BÁO (ALERT)
   * ================================================
   */
  const alerts = document.querySelectorAll(".global-alerts-container .alert");

  alerts.forEach(function (alert) {
    // Tự động ẩn sau 5 giây
    const autoCloseTimeout = setTimeout(() => {
      closeAlert(alert);
    }, 5000);

    // Nút X để đóng thủ công
    const closeButton = alert.querySelector(".close-alert");
    if (closeButton) {
      closeButton.addEventListener("click", function () {
        clearTimeout(autoCloseTimeout);
        closeAlert(alert);
      });
    }
  });

  // Hàm đóng alert kèm hiệu ứng
  function closeAlert(alertElement) {
    if (!alertElement) return;
    alertElement.style.transition =
      "opacity 0.5s ease, transform 0.5s ease, margin 0.5s ease";
    alertElement.style.opacity = "0";
    alertElement.style.transform = "translateY(-20px)";
    alertElement.style.marginTop = "0";
    alertElement.style.marginBottom = "0";
    alertElement.style.paddingTop = "0";
    alertElement.style.paddingBottom = "0";
    alertElement.style.border = "none";

    setTimeout(() => {
      if (alertElement.parentNode) alertElement.remove();
    }, 500);
  }

  /**
   * ================================================
   * 3️⃣ CHUYỂN TAB (TRANG CHI TIẾT SẢN PHẨM)
   * ================================================
   */
  const tabHeaders = document.querySelectorAll(".product-tabs .tab-link");
  const tabContents = document.querySelectorAll(".product-tabs .tab-content");

  function activateTab(tabIdToShow) {
    if (!tabIdToShow) return;

    // Remove active từ tất cả tabs
    tabHeaders.forEach((th) => th.classList.remove("active"));
    tabContents.forEach((tc) => {
      tc.classList.remove("active");
      tc.style.display = "none";
    });

    // Activate tab được chọn
    const headerToActivate = document.querySelector(
      `.tab-link[data-tab="${tabIdToShow}"]`
    );
    const contentToShow = document.getElementById(tabIdToShow);

    if (headerToActivate) {
      headerToActivate.classList.add("active");
    }
    if (contentToShow) {
      contentToShow.classList.add("active");
      contentToShow.style.display = "block";
    }
  }

  if (tabHeaders.length > 0) {
    // Thêm event listener cho mỗi tab
    tabHeaders.forEach((header) => {
      header.addEventListener("click", function (e) {
        e.preventDefault(); // Ngăn hành vi mặc định nếu có
        const tabId = this.getAttribute("data-tab");
        if (tabId) {
          activateTab(tabId);
          // Cập nhật hash URL để có thể bookmark
          if (history.pushState) {
            history.pushState(null, null, "#" + tabId);
          }
        }
      });
    });

    // Khi tải trang, kiểm tra hash URL hoặc active tab đầu tiên
    const currentHash = window.location.hash.substring(1);
    if (currentHash && document.getElementById(currentHash)) {
      activateTab(currentHash);
    } else {
      // Tìm tab có class 'active' hoặc tab đầu tiên
      const activeTab = document.querySelector(".tab-link.active");
      if (activeTab) {
        const activeTabId = activeTab.getAttribute("data-tab");
        if (activeTabId) {
          activateTab(activeTabId);
        }
      } else if (tabHeaders.length > 0) {
        const firstTabId = tabHeaders[0].getAttribute("data-tab");
        if (firstTabId) {
          activateTab(firstTabId);
        }
      }
    }
  }

  /**
   * ================================================
   * 4️⃣ VALIDATION FORM (ĐĂNG KÝ, ĐỔI MK, ĐẶT LẠI MK)
   * ================================================
   */
  const registerForm = document.querySelector('form[action$="/dang-ky"]');
  const changePasswordForm = document.querySelector(
    'form[action$="/tai-khoan/thay-doi-mat-khau"]'
  );
  const resetPasswordForm = document.querySelector(
    'form[action$="/dat-lai-mat-khau"]'
  );

  if (registerForm)
    addPasswordConfirmationValidation(
      registerForm,
      "#password",
      "#confirmPassword"
    );

  if (changePasswordForm)
    addPasswordConfirmationValidation(
      changePasswordForm,
      "#newPassword",
      "#confirmPassword"
    );

  if (resetPasswordForm)
    addPasswordConfirmationValidation(
      resetPasswordForm,
      "#newPassword",
      "#confirmPassword"
    );

  // Hàm dùng chung kiểm tra xác nhận mật khẩu
  function addPasswordConfirmationValidation(
    formElement,
    passwordSelector,
    confirmPasswordSelector
  ) {
    formElement.addEventListener("submit", function (event) {
      const passwordInput = formElement.querySelector(passwordSelector);
      const confirmInput = formElement.querySelector(confirmPasswordSelector);
      const existingError = formElement.querySelector(
        ".password-mismatch-error"
      );

      if (existingError) existingError.remove();

      if (
        passwordInput &&
        confirmInput &&
        passwordInput.value !== confirmInput.value
      ) {
        event.preventDefault();

        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message password-mismatch-error";
        errorDiv.style.marginBottom = "1rem";
        errorDiv.innerHTML =
          '<i class="fa-solid fa-circle-exclamation"></i> Mật khẩu xác nhận không khớp!';

        const submitButton = formElement.querySelector('button[type="submit"]');
        if (submitButton) formElement.insertBefore(errorDiv, submitButton);
        else formElement.appendChild(errorDiv);

        confirmInput.focus();
        confirmInput.style.borderColor = "red";
        passwordInput.style.borderColor = "red";

        // Bỏ highlight khi người dùng sửa lại
        const removeError = () => {
          confirmInput.style.borderColor = "";
          passwordInput.style.borderColor = "";
          const error = formElement.querySelector(".password-mismatch-error");
          if (error) error.remove();
        };
        confirmInput.addEventListener("input", removeError, { once: true });
        passwordInput.addEventListener("input", removeError, { once: true });
      }
    });
  }

  /**
   * ================================================
   * 5️⃣ SAO ĐÁNH GIÁ (TRANG REVIEW)
   * ================================================
   */
  const ratingStarsContainer = document.querySelector(
    ".review-form .rating-stars"
  );
  if (ratingStarsContainer) {
    ratingStarsContainer.addEventListener("change", function (event) {
      if (event.target.type === "radio" && event.target.name === "rating") {
        // console.log(`Đã chọn ${event.target.value} sao`);
      }
    });
  }

  /**
   * ================================================
   * 6️⃣ XEM TRƯỚC ẢNH ĐẠI DIỆN (PROFILE)
   * ================================================
   */
  const avatarInput = document.getElementById("avatarInput");
  const avatarPreview = document.getElementById("avatarPreview");
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener("change", function (event) {
      const file = event.target.files[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
          avatarPreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    });
  }
}); // --- KẾT THÚC DOMContentLoaded ---

/**
 * ================================================
 * 🔄 Hàm toggle menu riêng (nếu muốn gọi từ HTML onclick)
 * ================================================
 */
function toggleMobileMenu() {
  const mobileNav = document.getElementById("mobileNav");
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle button");
  if (mobileNav && mobileMenuToggle) {
    mobileNav.classList.toggle("active");
    const icon = mobileMenuToggle.querySelector("i");
    if (mobileNav.classList.contains("active")) {
      icon.classList.remove("fa-bars");
      icon.classList.add("fa-times");
      mobileMenuToggle.setAttribute("aria-label", "Đóng menu");
    } else {
      icon.classList.remove("fa-times");
      icon.classList.add("fa-bars");
      mobileMenuToggle.setAttribute("aria-label", "Mở menu");
    }
  }
}
