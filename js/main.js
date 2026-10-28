/* ==========================================
   SOBHA LUXURY — Main JavaScript
   ========================================== */

(function () {
  'use strict';

  /* ---------- Navbar scroll effect ---------- */
  const navbar = document.getElementById('navbar');
  function handleNavScroll() {
    if (!navbar) return;
    if (window.scrollY > 60) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  }
  window.addEventListener('scroll', handleNavScroll, { passive: true });
  handleNavScroll();

  /* ---------- Mobile nav ---------- */
  const menuToggle = document.getElementById('menuToggle');
  const mobileNav = document.getElementById('mobileNav');
  const navOverlay = document.getElementById('navOverlay');

  function openMobileNav() {
    if (!mobileNav) return;
    menuToggle.classList.add('open');
    mobileNav.classList.add('open');
    navOverlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeMobileNav() {
    if (!mobileNav) return;
    menuToggle.classList.remove('open');
    mobileNav.classList.remove('open');
    navOverlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (menuToggle) {
    menuToggle.addEventListener('click', function () {
      if (mobileNav.classList.contains('open')) {
        closeMobileNav();
      } else {
        openMobileNav();
      }
    });
  }

  if (navOverlay) {
    navOverlay.addEventListener('click', closeMobileNav);
  }

  const mobileCloseBtn = document.getElementById('mobileCloseBtn');
  if (mobileCloseBtn) {
    mobileCloseBtn.addEventListener('click', closeMobileNav);
  }

  /* ---------- Luxury Editorial FAQ Accordion ---------- */
  const luxuryFaqTriggers = document.querySelectorAll('.faq-luxury-trigger');
  luxuryFaqTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      const item = trigger.closest('.faq-luxury-item');
      const answer = item ? item.querySelector('.faq-luxury-answer') : null;
      if (!item || !answer) return;

      const isAlreadyOpen = item.classList.contains('open');

      // Close all items (allow only one open at a time)
      document.querySelectorAll('.faq-luxury-item.open').forEach(function (openItem) {
        openItem.classList.remove('open');
        const openAnswer = openItem.querySelector('.faq-luxury-answer');
        const openTrigger = openItem.querySelector('.faq-luxury-trigger');
        if (openAnswer) openAnswer.style.maxHeight = null;
        if (openTrigger) openTrigger.setAttribute('aria-expanded', 'false');
      });

      // If clicked item was not open, open it now
      if (!isAlreadyOpen) {
        item.classList.add('open');
        answer.style.maxHeight = answer.scrollHeight + 32 + 'px';
        trigger.setAttribute('aria-expanded', 'true');
      }
    });
  });

  // Re-calculate open FAQ answer height on window resize
  window.addEventListener('resize', function () {
    const activeOpen = document.querySelector('.faq-luxury-item.open .faq-luxury-answer');
    if (activeOpen) {
      activeOpen.style.maxHeight = activeOpen.scrollHeight + 32 + 'px';
    }
  });

  /* ---------- Legacy FAQ Accordion Support ---------- */
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const item = btn.closest('.faq-item');
      if (!item) return;
      const isOpen = item.classList.contains('active') || item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(function (openItem) {
        openItem.classList.remove('open', 'active');
      });
      if (!isOpen) {
        item.classList.add('open', 'active');
      }
    });
  });

  /* ---------- Intersection Observer for animations ---------- */
  const fadeEls = document.querySelectorAll('.fade-in-up');
  if (fadeEls.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );
    fadeEls.forEach(function (el) {
      observer.observe(el);
    });
  } else {
    fadeEls.forEach(function (el) {
      el.classList.add('visible');
    });
  }

  /* ---------- Cookie Banner ---------- */
  const cookieBanner = document.getElementById('cookie-banner');
  const cookieAccept = document.getElementById('cookieAccept');
  const cookieReject = document.getElementById('cookieReject');
  const cookieManage = document.getElementById('cookieManage');

  if (cookieBanner && !localStorage.getItem('sobha_cookie_choice')) {
    setTimeout(function () {
      cookieBanner.classList.add('visible');
    }, 1500);
  }

  function dismissCookie(choice) {
    localStorage.setItem('sobha_cookie_choice', choice);
    if (cookieBanner) {
      cookieBanner.style.transform = 'translateY(100%)';
    }
  }

  if (cookieAccept) cookieAccept.addEventListener('click', function () { dismissCookie('accept'); });
  if (cookieReject) cookieReject.addEventListener('click', function () { dismissCookie('reject'); });
  if (cookieManage) cookieManage.addEventListener('click', function () {
    window.location.href = 'cookie-policy.html';
  });

  /* ---------- Enquiry Form Handling & URL Prefill ---------- */
  /* ---------- Luxury Private Consultation Form Handling ---------- */
  function initConsultationForm() {
    const form = document.getElementById('consultForm');
    if (!form) return;

    const nameInput = document.getElementById('consultName');
    const emailInput = document.getElementById('consultEmail');
    const phoneInput = document.getElementById('consultPhone');
    const devSelect = document.getElementById('consultDev');
    const countryCodeInput = document.getElementById('consultCountryCode');
    const ccPicker = document.getElementById('consultCCPicker');
    const ccDropdown = document.getElementById('consultCCDropdown');
    const ccFlag = document.getElementById('consultCCFlag');
    const ccCode = document.getElementById('consultCCCode');

    const submitBtn = document.getElementById('consultSubmitBtn');
    const submitText = document.getElementById('consultSubmitText');
    const submitArrow = submitBtn ? submitBtn.querySelector('.consult-submit__arrow') : null;
    const submitSpinner = document.getElementById('consultSubmitSpinner');
    const globalError = document.getElementById('consultGlobalError');
    const successView = document.getElementById('consultSuccess');

    // Country Code Dropdown Toggle
    if (ccPicker && ccDropdown) {
      ccPicker.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = ccDropdown.classList.contains('open');
        if (isOpen) {
          ccDropdown.classList.remove('open');
          ccPicker.setAttribute('aria-expanded', 'false');
        } else {
          ccDropdown.classList.add('open');
          ccPicker.setAttribute('aria-expanded', 'true');
        }
      });

      ccDropdown.querySelectorAll('li').forEach(function (item) {
        item.addEventListener('click', function (e) {
          e.stopPropagation();
          const code = item.getAttribute('data-code');
          const flag = item.getAttribute('data-flag');
          if (countryCodeInput) countryCodeInput.value = code;
          if (ccCode) ccCode.textContent = code;
          if (ccFlag) ccFlag.textContent = flag;
          ccDropdown.querySelectorAll('li').forEach(function (li) { li.classList.remove('active'); });
          item.classList.add('active');
          ccDropdown.classList.remove('open');
          ccPicker.setAttribute('aria-expanded', 'false');
          if (phoneInput) phoneInput.focus();
        });
      });

      document.addEventListener('click', function (e) {
        if (!ccDropdown.contains(e.target) && !ccPicker.contains(e.target)) {
          ccDropdown.classList.remove('open');
          ccPicker.setAttribute('aria-expanded', 'false');
        }
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && ccDropdown.classList.contains('open')) {
          ccDropdown.classList.remove('open');
          ccPicker.setAttribute('aria-expanded', 'false');
        }
      });
    }

    // Validation Helpers
    function setFieldError(fieldId, errorMsg) {
      const field = document.querySelector(`.consult-field[data-field="${fieldId}"]`);
      const errEl = document.getElementById(`consult${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)}Error`);
      if (field) field.classList.add('has-error');
      if (errEl) {
        errEl.textContent = errorMsg;
        errEl.style.display = 'block';
      }
    }

    function clearFieldError(fieldId) {
      const field = document.querySelector(`.consult-field[data-field="${fieldId}"]`);
      const errEl = document.getElementById(`consult${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)}Error`);
      if (field) field.classList.remove('has-error');
      if (errEl) {
        errEl.textContent = '';
        errEl.style.display = 'none';
      }
      if (globalError) {
        globalError.style.display = 'none';
      }
    }

    // Clear errors on input
    if (nameInput) nameInput.addEventListener('input', function () { clearFieldError('name'); });
    if (emailInput) emailInput.addEventListener('input', function () { clearFieldError('email'); });
    if (phoneInput) phoneInput.addEventListener('input', function () { clearFieldError('phone'); });
    if (devSelect) devSelect.addEventListener('change', function () { clearFieldError('dev'); });

    // Pre-fill development if query string or data-project exists
    function applyFormPreferences(project) {
      if (project && devSelect) {
        const p = project.toLowerCase();
        if (p.includes('solis')) devSelect.value = 'Sobha Solis';
        else if (p.includes('orbis')) devSelect.value = 'Sobha Orbis';
        else if (p.includes('sanctuary')) devSelect.value = 'Sobha Sanctuary Villas';
        else if (p.includes('central')) devSelect.value = 'Sobha Central';
        else if (p.includes('siniya')) devSelect.value = 'Sobha Siniya Island';
        else devSelect.value = 'All Developments';
      }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const preProject = urlParams.get('project');
    if (preProject) {
      applyFormPreferences(preProject);
    }

    // Handle in-page links with data-project or data-development
    document.querySelectorAll('a[href="#contact"]').forEach(function (link) {
      link.addEventListener('click', function () {
        const proj = link.getAttribute('data-development') || link.getAttribute('data-project');
        if (proj) applyFormPreferences(proj);
      });
    });

    let isSubmitting = false;

    // Set render timestamp
    const renderTsEl = document.getElementById('consultRenderTs');
    if (renderTsEl) renderTsEl.value = Math.floor(Date.now() / 1000);

    // Form Submit Handler
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      e.stopPropagation();

      if (isSubmitting) return;

      if (globalError) globalError.style.display = 'none';

      let isValid = true;
      let firstInvalidInput = null;

      // 1. Validate Name
      const nameVal = nameInput ? nameInput.value.trim() : '';
      if (!nameVal || nameVal.length < 2 || !/^[\p{L}\s'\-\.]{2,80}$/u.test(nameVal)) {
        setFieldError('name', 'Please enter your full name (letters only, min 2 characters).');
        isValid = false;
        if (!firstInvalidInput) firstInvalidInput = nameInput;
      } else {
        clearFieldError('name');
      }

      // 2. Validate Email
      const emailVal = emailInput ? emailInput.value.trim().toLowerCase() : '';
      const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
      const disposableDomains = ['mailinator.com', 'tempmail.com', 'guerrillamail.com', '10minutemail.com', 'throwawaymail.com', 'yopmail.com'];
      const emailDomain = emailVal.split('@')[1];

      if (!emailVal || !emailRegex.test(emailVal)) {
        setFieldError('email', 'Please enter a valid email address.');
        isValid = false;
        if (!firstInvalidInput) firstInvalidInput = emailInput;
      } else if (emailDomain && disposableDomains.includes(emailDomain)) {
        setFieldError('email', 'Please enter a valid personal or business email address.');
        isValid = false;
        if (!firstInvalidInput) firstInvalidInput = emailInput;
      } else {
        clearFieldError('email');
      }

      // 3. Validate Phone
      const phoneVal = phoneInput ? phoneInput.value.trim() : '';
      const cleanPhone = phoneVal.replace(/[\s\-\(\)\.]/g, '');
      const isSequential = '0123456789012345'.includes(cleanPhone) || '9876543210987654'.includes(cleanPhone);
      const isRepetitive = /^(\d)\1+$/.test(cleanPhone);

      if (!phoneVal || cleanPhone.length < 6 || cleanPhone.length > 15 || isSequential || isRepetitive) {
        setFieldError('phone', 'Please enter a valid, active contact phone number.');
        isValid = false;
        if (!firstInvalidInput) firstInvalidInput = phoneInput;
      } else {
        clearFieldError('phone');
      }

      // 4. Validate Development
      const devVal = devSelect ? devSelect.value : '';
      if (!devVal) {
        setFieldError('dev', 'Please select a development of interest.');
        isValid = false;
        if (!firstInvalidInput) firstInvalidInput = devSelect;
      } else {
        clearFieldError('dev');
      }

      if (!isValid) {
        if (firstInvalidInput) firstInvalidInput.focus();
        return;
      }

      // Start Submission / Loading State
      isSubmitting = true;
      if (submitBtn) submitBtn.disabled = true;
      if (submitText) submitText.textContent = 'SUBMITTING ENQUIRY...';
      if (submitArrow) submitArrow.style.display = 'none';
      if (submitSpinner) submitSpinner.style.display = 'inline-flex';

      const ccVal = countryCodeInput ? countryCodeInput.value : '+971';
      const honeypotVal = document.getElementById('consultHoneypot') ? document.getElementById('consultHoneypot').value : '';
      const renderTs = renderTsEl ? renderTsEl.value : Math.floor(Date.now() / 1000);

      // Parse UTM parameters
      const urlParams = new URLSearchParams(window.location.search);
      const leadPayload = {
        name: nameVal,
        email: emailVal,
        phone: phoneVal,
        country_code: ccVal,
        project: devVal,
        source_button: 'Home Consultation Section',
        page_url: window.location.href,
        referrer_url: document.referrer || '',
        company_website: honeypotVal,
        form_render_ts: renderTs,
        utm_source: urlParams.get('utm_source') || '',
        utm_medium: urlParams.get('utm_medium') || '',
        utm_campaign: urlParams.get('utm_campaign') || '',
        utm_term: urlParams.get('utm_term') || '',
        utm_content: urlParams.get('utm_content') || ''
      };

      // Backup locally
      try {
        const localLeads = JSON.parse(localStorage.getItem('sobha_luxury_enquiries') || '[]');
        localLeads.unshift(leadPayload);
        localStorage.setItem('sobha_luxury_enquiries', JSON.stringify(localLeads));
      } catch (e) {}

      // Send to api/submit-lead.php
      fetch('api/submit-lead.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(leadPayload)
      })
      .then(function (res) {
        return res.json().catch(function () { return { success: true, redirect_url: 'thank-you.html' }; });
      })
      .then(function (data) {
        if (data && data.success === false) {
          const errorMsg = data.error || data.message || 'Verification failed. Please review your details.';
          if (data.field === 'name' || data.field === 'full_name') setFieldError('name', errorMsg);
          else if (data.field === 'email') setFieldError('email', errorMsg);
          else if (data.field === 'phone') setFieldError('phone', errorMsg);
          else showGlobalError(errorMsg);
          resetSubmitButton();
          return;
        }

        try { window.dispatchEvent(new CustomEvent('sobha:enquirySubmitted', { detail: leadPayload })); } catch (e) {}
        window.location.href = (data && data.redirect_url) ? data.redirect_url : 'thank-you.html';
      })
      .catch(function () {
        // Graceful redirect fallback
        window.location.href = 'thank-you.html';
      });
    });

    function resetSubmitButton() {
      isSubmitting = false;
      if (submitBtn) submitBtn.disabled = false;
      if (submitText) submitText.textContent = 'SUBMIT ENQUIRY';
      if (submitArrow) submitArrow.style.display = 'inline-flex';
      if (submitSpinner) submitSpinner.style.display = 'none';
    }

    function showGlobalError(msg) {
      if (globalError) {
        globalError.innerHTML = msg;
        globalError.style.display = 'block';
        globalError.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    }

    function finalizeSuccess(payload, nameVal, devVal, refId) {
      try {
        const localLeads = JSON.parse(localStorage.getItem('sobha_luxury_enquiries') || '[]');
        localLeads.unshift(payload);
        localStorage.setItem('sobha_luxury_enquiries', JSON.stringify(localLeads));
        window.dispatchEvent(new CustomEvent('sobha:enquirySubmitted', { detail: payload }));
      } catch (e) {}

      form.style.display = 'none';
      if (successView) {
        const nameEl = document.getElementById('consultSuccessName');
        const projEl = document.getElementById('consultSuccessProject');
        const refEl = document.getElementById('consultSuccessRef');
        if (nameEl) nameEl.textContent = nameVal.split(' ')[0] || nameVal;
        if (projEl) projEl.textContent = devVal;
        if (refEl) refEl.textContent = '#' + refId;
        successView.style.display = 'block';
        successView.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      form.reset();
      isSubmitting = false;
    }
  }

  // Initialize consultation form on DOM load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConsultationForm);
  } else {
    initConsultationForm();
  }

  /* ---------- FAQ Category Filter ---------- */
  const catBtns = document.querySelectorAll('.faq-cat-btn');
  catBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      catBtns.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      const target = btn.getAttribute('data-cat');
      document.querySelectorAll('.faq-category-section').forEach(function (section) {
        if (target === 'all' || section.getAttribute('data-cat') === target) {
          section.style.display = '';
        } else {
          section.style.display = 'none';
        }
      });
    });
  });

  /* ---------- Project filter (homepage) ---------- */
  const filterBtn = document.getElementById('filterBtn');
  if (filterBtn) {
    filterBtn.addEventListener('click', function () {
      const location = document.getElementById('filterLocation')?.value;
      const type = document.getElementById('filterType')?.value;
      const beds = document.getElementById('filterBeds')?.value;
      // Build query string and navigate
      const params = new URLSearchParams();
      if (location) params.set('location', location);
      if (type) params.set('type', type);
      if (beds) params.set('beds', beds);
      window.location.href = 'developments.html?' + params.toString();
    });
  }

  /* ---------- Smooth scroll for anchor links ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        closeMobileNav();
      }
    });
  });

  /* ---------- Active nav link ---------- */
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a, .mobile-nav-links a').forEach(function (link) {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  /* ---------- Premium 3-Card Developments Carousel ---------- */
  const trackContainer = document.getElementById('carouselTrackContainer');
  const track = document.getElementById('carouselTrack');
  const prevBtn = document.getElementById('carouselPrev');
  const nextBtn = document.getElementById('carouselNext');
  const pagDots = document.querySelectorAll('.pag-dot');

  if (trackContainer && track) {
    const originalCards = Array.from(track.querySelectorAll('.luxury-dev-card'));
    const realCount = originalCards.length;

    if (realCount > 0) {
      // Clone 2 cards before and 2 cards after to create seamless infinite sliding
      const clonesBefore = 2;
      const clonesAfter = 2;

      // Clone last 2 cards to the front in correct order
      for (let i = realCount - clonesBefore; i < realCount; i++) {
        const clone = originalCards[i].cloneNode(true);
        clone.classList.add('card-clone');
        track.insertBefore(clone, originalCards[0]);
      }

      // Clone first 2 cards to the back
      for (let i = 0; i < clonesAfter; i++) {
        const clone = originalCards[i].cloneNode(true);
        clone.classList.add('card-clone');
        track.appendChild(clone);
      }

      // Initial active card is index 2 (SOBHA SANCTUARY VILLAS), matching reference design
      let currentDOMIndex = 2 + clonesBefore; // index 4 in 9-element array
      let isTransitioning = false;

      function updateSlidePosition(withAnimation) {
        const allCards = Array.from(track.children);
        const currentCard = allCards[currentDOMIndex];
        if (!currentCard) return;

        const cardCenter = currentCard.offsetLeft + currentCard.offsetWidth / 2;
        const containerCenter = trackContainer.offsetWidth / 2;
        const targetOffset = cardCenter - containerCenter;

        if (withAnimation) {
          track.style.transition = 'transform 0.55s cubic-bezier(0.2, 0.8, 0.25, 1)';
        } else {
          track.style.transition = 'none';
        }

        track.style.transform = `translateX(${-targetOffset}px)`;

        // Update card visual hierarchy
        allCards.forEach(function (card, i) {
          card.classList.remove('card-featured', 'card-dimmed');
          if (i === currentDOMIndex) {
            card.classList.add('card-featured');
          } else if (Math.abs(i - currentDOMIndex) > 1) {
            card.classList.add('card-dimmed');
          }
        });

        // Update active pagination dot
        const realIndex = ((currentDOMIndex - clonesBefore) % realCount + realCount) % realCount;
        pagDots.forEach(function (dot, i) {
          if (i === realIndex) {
            dot.classList.add('active');
          } else {
            dot.classList.remove('active');
          }
        });
      }

      function goToDOMIndex(newIndex) {
        if (isTransitioning) return;
        isTransitioning = true;
        currentDOMIndex = newIndex;
        updateSlidePosition(true);
      }

      function slideNext() {
        goToDOMIndex(currentDOMIndex + 1);
      }

      function slidePrev() {
        goToDOMIndex(currentDOMIndex - 1);
      }

      if (prevBtn) {
        prevBtn.addEventListener('click', function (e) {
          e.preventDefault();
          slidePrev();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', function (e) {
          e.preventDefault();
          slideNext();
        });
      }

      // Silent teleport when landing on a clone
      track.addEventListener('transitionend', function (e) {
        if (e.target !== track) return;
        isTransitioning = false;

        if (currentDOMIndex >= clonesBefore + realCount) {
          currentDOMIndex = currentDOMIndex - realCount;
          updateSlidePosition(false);
          track.offsetHeight; // force reflow
        } else if (currentDOMIndex < clonesBefore) {
          currentDOMIndex = currentDOMIndex + realCount;
          updateSlidePosition(false);
          track.offsetHeight; // force reflow
        }
      });

      // Pagination click navigation
      pagDots.forEach(function (dot, dotIndex) {
        dot.addEventListener('click', function () {
          goToDOMIndex(dotIndex + clonesBefore);
        });
      });

      // Ensure clicking anywhere on ANY card (or clicking Explore) opens the project
      let hasSwiped = false;
      function attachCardClickHandlers() {
        const allCards = Array.from(track.children);
        allCards.forEach(function (card) {
          card.addEventListener('click', function (e) {
            if (hasSwiped) {
              e.preventDefault();
              return;
            }
            const href = card.getAttribute('href');
            if (href) {
              window.location.href = href;
            }
          });
        });
      }
      attachCardClickHandlers();

      // Keyboard arrow keys when developments section is in view
      document.addEventListener('keydown', function (e) {
        const section = document.getElementById('developments');
        if (!section) return;
        const rect = section.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          if (e.key === 'ArrowLeft') {
            slidePrev();
          } else if (e.key === 'ArrowRight') {
            slideNext();
          }
        }
      });

      // Touch swipe gestures
      let touchStartX = 0;
      let touchStartY = 0;
      let isSwiping = false;

      trackContainer.addEventListener('touchstart', function (e) {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = true;
        hasSwiped = false;
      }, { passive: true });

      trackContainer.addEventListener('touchmove', function (e) {
        if (!isSwiping) return;
        const diffX = e.touches[0].clientX - touchStartX;
        const diffY = e.touches[0].clientY - touchStartY;
        if (Math.abs(diffX) > 12) {
          hasSwiped = true;
        }
      }, { passive: true });

      trackContainer.addEventListener('touchend', function (e) {
        if (!isSwiping) return;
        isSwiping = false;
        const diffX = e.changedTouches[0].clientX - touchStartX;
        const diffY = e.changedTouches[0].clientY - touchStartY;
        // Ensure horizontal intent
        if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 40) {
          if (diffX > 0) {
            slidePrev();
          } else {
            slideNext();
          }
        }
      }, { passive: true });

      // Window resize re-centering
      let resizeTimer;
      window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          updateSlidePosition(false);
        }, 80);
      });

      // Initial alignment
      window.addEventListener('load', function () {
        updateSlidePosition(false);
      });
      setTimeout(function () {
        updateSlidePosition(false);
      }, 50);
    }
  }

  /* ==========================================================================
     CONTENT & ASSET PROTECTION
     Discourages casual right-click saving, text copying, and source shortcuts
     without breaking form inputs, links, or navigation.
     ========================================================================== */
  function initContentProtection() {
    // 1. Disable Context Menu (Right Click) on protected content & images
    document.addEventListener('contextmenu', function (e) {
      // Inputs, textareas, selects, and editable content always keep native context menu
      if (e.target.closest('input, textarea, select, [contenteditable="true"]')) {
        return;
      }
      // If clicking protected media (even if wrapped inside an <a> tag), prevent context menu
      if (e.target.closest('img, picture, video, .satellite-map-canvas, .hero-bg-exact, .gallery-slide-img')) {
        e.preventDefault();
        return;
      }
      // Allow context menu on standard text/navigation links
      if (e.target.closest('a[href]')) {
        return;
      }
      e.preventDefault();
    });

    // 2. Disable Drag-and-Drop saving of images
    document.addEventListener('dragstart', function (e) {
      if (e.target.tagName === 'IMG' || e.target.closest('img') || e.target.tagName === 'VIDEO') {
        e.preventDefault();
      }
    });

    // 3. Disable Copy & Cut actions on public content
    document.addEventListener('copy', function (e) {
      if (!e.target.closest('input, textarea, select, [contenteditable="true"]')) {
        e.preventDefault();
      }
    });

    document.addEventListener('cut', function (e) {
      if (!e.target.closest('input, textarea, select, [contenteditable="true"]')) {
        e.preventDefault();
      }
    });

    // 4. Discourage Casual Shortcut Keys (View Source Ctrl+U, Save Ctrl+S, Inspect Keys)
    document.addEventListener('keydown', function (e) {
      var isInput = !!e.target.closest('input, textarea, select, [contenteditable="true"]');
      var key = e.key ? e.key.toLowerCase() : '';
      var ctrlOrMeta = e.ctrlKey || e.metaKey;

      // Prevent Ctrl+U / Cmd+U (View Page Source)
      if (ctrlOrMeta && key === 'u') {
        e.preventDefault();
        return;
      }

      // Prevent Ctrl+S / Cmd+S (Save Webpage)
      if (ctrlOrMeta && key === 's') {
        e.preventDefault();
        return;
      }

      // Prevent Ctrl+C on non-form text
      if (ctrlOrMeta && key === 'c' && !isInput) {
        var selection = window.getSelection ? window.getSelection().toString() : '';
        if (selection.length > 0) {
          e.preventDefault();
          return;
        }
      }

      // Discourage F12 and Ctrl+Shift+I / Ctrl+Shift+J / Ctrl+Shift+C
      if (e.key === 'F12' || (ctrlOrMeta && e.shiftKey && (key === 'i' || key === 'j' || key === 'c'))) {
        e.preventDefault();
        return;
      }
    });
  }

  initContentProtection();

})();

