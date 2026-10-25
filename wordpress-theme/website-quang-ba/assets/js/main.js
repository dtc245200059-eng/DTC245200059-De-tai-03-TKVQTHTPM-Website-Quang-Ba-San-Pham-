/**
 * Main JavaScript for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
  'use strict';

  // ---------------------------------------------------------------------------
  // 1. MOBILE NAVIGATION TOGGLE
  // ---------------------------------------------------------------------------
  const navToggle = document.getElementById('nav-toggle');
  const primaryNav = document.getElementById('primary-nav');
  const siteHeader = document.getElementById('site-header');

  if (navToggle && primaryNav) {
    navToggle.addEventListener('click', function () {
      const isExpanded = navToggle.getAttribute('aria-expanded') === 'true';
      navToggle.setAttribute('aria-expanded', !isExpanded);
      primaryNav.classList.toggle('is-open');

      if (!isExpanded) {
        navToggle.setAttribute('aria-label', 'Đóng menu điều hướng');
      } else {
        navToggle.setAttribute('aria-label', 'Mở menu điều hướng');
      }
    });

    // Tự động đóng menu trên mobile khi click vào link neo
    const navLinks = primaryNav.querySelectorAll('.nav-link');
    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 991) {
          primaryNav.classList.remove('is-open');
          navToggle.setAttribute('aria-expanded', 'false');
          navToggle.setAttribute('aria-label', 'Mở menu điều hướng');
        }
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 2. STICKY HEADER BOX SHADOW TRÊN SCROLL
  // ---------------------------------------------------------------------------
  if (siteHeader) {
    const handleScroll = function () {
      if (window.scrollY > 15) {
        siteHeader.classList.add('scrolled');
      } else {
        siteHeader.classList.remove('scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // ---------------------------------------------------------------------------
  // 3. PRODUCT CATEGORY FILTER TABS
  // ---------------------------------------------------------------------------
  const filterBtns = document.querySelectorAll('.filter-btn');
  const productCards = document.querySelectorAll('.product-card');

  if (filterBtns.length > 0 && productCards.length > 0) {
    filterBtns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        // Cập nhật trạng thái active cho nút tab
        filterBtns.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');

        const targetCategory = btn.getAttribute('data-filter');

        // Lọc danh sách sản phẩm
        productCards.forEach(function (card) {
          const cardCategory = card.getAttribute('data-category');
          if (targetCategory === 'all' || cardCategory === targetCategory) {
            card.style.display = 'flex';
            setTimeout(function () {
              card.style.opacity = '1';
              card.style.transform = 'translateY(0)';
            }, 10);
          } else {
            card.style.opacity = '0';
            card.style.transform = 'translateY(10px)';
            setTimeout(function () {
              card.style.display = 'none';
            }, 200);
          }
        });
      });
    });
  }

  // ---------------------------------------------------------------------------
  // 4. PRODUCT QUICK VIEW MODAL
  // ---------------------------------------------------------------------------
  const modalBackdrop = document.getElementById('product-modal');
  const modalCloseBtn = document.getElementById('modal-close-btn');
  const quickViewBtns = document.querySelectorAll('.btn-quick-view');
  let lastFocusedElement = null;

  function openModal(data) {
    if (!modalBackdrop) return;

    lastFocusedElement = document.activeElement;

    // Cập nhật nội dung modal từ dữ liệu thẻ
    document.getElementById('modal-title').textContent = data.name || '';
    document.getElementById('modal-category').textContent = data.category || '';
    document.getElementById('modal-price').textContent = data.price || '';
    document.getElementById('modal-desc').textContent = data.description || '';

    // Render bảng thông số kỹ thuật
    const specsTable = document.getElementById('modal-specs-body');
    if (specsTable) {
      specsTable.innerHTML = '';
      if (data.specs) {
        try {
          const specsObj = typeof data.specs === 'string' ? JSON.parse(data.specs) : data.specs;
          for (const [key, value] of Object.entries(specsObj)) {
            const tr = document.createElement('tr');
            const tdKey = document.createElement('td');
            tdKey.textContent = key;
            const tdVal = document.createElement('td');
            tdVal.textContent = value;
            tr.appendChild(tdKey);
            tr.appendChild(tdVal);
            specsTable.appendChild(tr);
          }
        } catch (e) {
          console.warn('Lỗi phân tích specs:', e);
        }
      }
    }

    modalBackdrop.classList.add('is-visible');
    modalBackdrop.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    if (modalCloseBtn) {
      modalCloseBtn.focus();
    }
  }

  function closeModal() {
    if (!modalBackdrop) return;
    modalBackdrop.classList.remove('is-visible');
    modalBackdrop.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';

    if (lastFocusedElement) {
      lastFocusedElement.focus();
    }
  }

  if (quickViewBtns.length > 0) {
    quickViewBtns.forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        const card = btn.closest('.product-card');
        if (!card) return;

        const data = {
          name: card.getAttribute('data-name'),
          category: card.getAttribute('data-category-name'),
          price: card.getAttribute('data-price'),
          description: card.getAttribute('data-desc'),
          specs: card.getAttribute('data-specs')
        };
        openModal(data);
      });
    });
  }

  if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', closeModal);
  }

  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', function (e) {
      if (e.target === modalBackdrop) {
        closeModal();
      }
    });
  }

  // Đóng Modal hoặc Mobile Nav khi nhấn phím ESC
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) {
      if (modalBackdrop && modalBackdrop.classList.contains('is-visible')) {
        closeModal();
      }
      if (primaryNav && primaryNav.classList.contains('is-open')) {
        primaryNav.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // ---------------------------------------------------------------------------
  // 5. DEMO CONTACT FORM HANDLER & TOAST NOTIFICATION
  // ---------------------------------------------------------------------------
  const contactForm = document.getElementById('demo-contact-form');
  const toastContainer = document.getElementById('toast-container');

  function showToast(message, type) {
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    if (type === 'error') {
      toast.style.borderLeftColor = 'var(--color-accent-red)';
    }

    toast.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
      <span>${message}</span>
    `;

    toastContainer.appendChild(toast);

    setTimeout(function () {
      toast.classList.add('is-show');
    }, 10);

    setTimeout(function () {
      toast.classList.remove('is-show');
      setTimeout(function () {
        toast.remove();
      }, 300);
    }, 4500);
  }

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();

      const nameInput = document.getElementById('contact-name');
      const emailInput = document.getElementById('contact-email');
      const phoneInput = document.getElementById('contact-phone');

      if (!nameInput.value.trim() || !emailInput.value.trim() || !phoneInput.value.trim()) {
        showToast('Vui lòng điền đầy đủ họ tên, email và số điện thoại.', 'error');
        return;
      }

      // Mô phỏng gửi thành công (Demo UI phía trình duyệt)
      showToast('Giao diện mô phỏng (Demo UI): Dữ liệu chưa được gửi hay lưu trữ tại máy chủ do chưa tích hợp backend.', 'success');
      contactForm.reset();
    });
  }
});
