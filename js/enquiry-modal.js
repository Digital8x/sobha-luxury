/* ==========================================================================
   SOBHA LUXURY — Global Reusable Enquiry Modal Component
   Provides seamless, luxury enquiry submission across all site pages
   ========================================================================== */

(function () {
  'use strict';

  // Comprehensive list of countries with dial codes, ISO2 and names
  const COUNTRY_CODES = [
    { name: 'India', code: '+91', iso: 'in' },
    { name: 'United Arab Emirates', code: '+971', iso: 'ae' },
    { name: 'United States', code: '+1', iso: 'us' },
    { name: 'United Kingdom', code: '+44', iso: 'gb' },
    { name: 'Saudi Arabia', code: '+966', iso: 'sa' },
    { name: 'Qatar', code: '+974', iso: 'qa' },
    { name: 'Kuwait', code: '+965', iso: 'kw' },
    { name: 'Oman', code: '+968', iso: 'om' },
    { name: 'Bahrain', code: '+973', iso: 'bh' },
    { name: 'Canada', code: '+1', iso: 'ca' },
    { name: 'Australia', code: '+61', iso: 'au' },
    { name: 'Singapore', code: '+65', iso: 'sg' },
    { name: 'Germany', code: '+49', iso: 'de' },
    { name: 'France', code: '+33', iso: 'fr' },
    { name: 'Italy', code: '+39', iso: 'it' },
    { name: 'Spain', code: '+34', iso: 'es' },
    { name: 'Switzerland', code: '+41', iso: 'ch' },
    { name: 'Netherlands', code: '+31', iso: 'nl' },
    { name: 'Belgium', code: '+32', iso: 'be' },
    { name: 'Austria', code: '+43', iso: 'at' },
    { name: 'Sweden', code: '+46', iso: 'se' },
    { name: 'Norway', code: '+47', iso: 'no' },
    { name: 'Denmark', code: '+45', iso: 'dk' },
    { name: 'Ireland', code: '+353', iso: 'ie' },
    { name: 'Russia', code: '+7', iso: 'ru' },
    { name: 'China', code: '+86', iso: 'cn' },
    { name: 'Hong Kong', code: '+852', iso: 'hk' },
    { name: 'Japan', code: '+81', iso: 'jp' },
    { name: 'South Korea', code: '+82', iso: 'kr' },
    { name: 'Malaysia', code: '+60', iso: 'my' },
    { name: 'Thailand', code: '+66', iso: 'th' },
    { name: 'Indonesia', code: '+62', iso: 'id' },
    { name: 'Philippines', code: '+63', iso: 'ph' },
    { name: 'Vietnam', code: '+84', iso: 'vn' },
    { name: 'Pakistan', code: '+92', iso: 'pk' },
    { name: 'Bangladesh', code: '+880', iso: 'bd' },
    { name: 'Sri Lanka', code: '+94', iso: 'lk' },
    { name: 'Nepal', code: '+977', iso: 'np' },
    { name: 'Turkey', code: '+90', iso: 'tr' },
    { name: 'Egypt', code: '+20', iso: 'eg' },
    { name: 'Jordan', code: '+962', iso: 'jo' },
    { name: 'Lebanon', code: '+961', iso: 'lb' },
    { name: 'South Africa', code: '+27', iso: 'za' },
    { name: 'Nigeria', code: '+234', iso: 'ng' },
    { name: 'Kenya', code: '+254', iso: 'ke' },
    { name: 'Ghana', code: '+233', iso: 'gh' },
    { name: 'Brazil', code: '+55', iso: 'br' },
    { name: 'Mexico', code: '+52', iso: 'mx' },
    { name: 'Argentina', code: '+54', iso: 'ar' },
    { name: 'New Zealand', code: '+64', iso: 'nz' },
    { name: 'Poland', code: '+48', iso: 'pl' },
    { name: 'Portugal', code: '+351', iso: 'pt' },
    { name: 'Greece', code: '+30', iso: 'gr' },
    { name: 'Czech Republic', code: '+420', iso: 'cz' },
    { name: 'Hungary', code: '+36', iso: 'hu' },
    { name: 'Romania', code: '+40', iso: 'ro' },
    { name: 'Kazakhstan', code: '+7', iso: 'kz' },
    { name: 'Uzbekistan', code: '+998', iso: 'uz' },
    { name: 'Azerbaijan', code: '+994', iso: 'az' },
    { name: 'Cyprus', code: '+357', iso: 'cy' },
    { name: 'Malta', code: '+356', iso: 'mt' },
    { name: 'Luxembourg', code: '+352', iso: 'lu' },
    { name: 'Monaco', code: '+377', iso: 'mc' }
  ];

  function getFlagHtml(isoCode, altText) {
    const iso = (isoCode || 'ae').toLowerCase();
    return `<img src="https://flagcdn.com/w40/${iso}.png" srcset="https://flagcdn.com/w80/${iso}.png 2x" width="20" height="14" alt="${altText || iso.toUpperCase()}" class="country-flag-img" loading="lazy">`;
  }

  // Development mapping helper
  const DEVELOPMENTS = [
    'Open to All Developments',
    'SOBHA Solis',
    'SOBHA Orbis',
    'SOBHA Sanctuary Villas',
    'SOBHA Central',
    'SOBHA Siniya Island'
  ];

  function getPageDefaultDevelopment() {
    const path = window.location.pathname.toLowerCase();
    if (path.includes('solis')) return 'SOBHA Solis';
    if (path.includes('orbis')) return 'SOBHA Orbis';
    if (path.includes('sanctuary')) return 'SOBHA Sanctuary Villas';
    if (path.includes('central')) return 'SOBHA Central';
    if (path.includes('siniya')) return 'SOBHA Siniya Island';
    return 'Open to All Developments';
  }

  function resolveDevelopment(targetIdentifier) {
    if (!targetIdentifier) return getPageDefaultDevelopment();
    const str = String(targetIdentifier).toLowerCase();
    if (str.includes('solis')) return 'SOBHA Solis';
    if (str.includes('orbis')) return 'SOBHA Orbis';
    if (str.includes('sanctuary') || str.includes('brooks') || str.includes('grove') || str.includes('serenity') || str.includes('willows') || str.includes('woods')) return 'SOBHA Sanctuary Villas';
    if (str.includes('central')) return 'SOBHA Central';
    if (str.includes('siniya') || str.includes('coral') || str.includes('palm grove')) return 'SOBHA Siniya Island';
    return 'Open to All Developments';
  }

  // Inject modal into DOM if not present
  function ensureModalExists() {
    let modal = document.getElementById('luxuryEnquiryModal');
    if (modal) return modal;

    const modalHTML = `
      <div id="luxuryEnquiryModal" class="enquiry-modal-backdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="enquiryModalTitle">
        <div class="enquiry-modal-dialog">
          <!-- Close Button -->
          <button type="button" class="enquiry-modal-close" id="enquiryModalClose" aria-label="Close Enquiry Dialog">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>

          <!-- Form View -->
          <div class="enquiry-modal-body" id="enquiryModalBody">
            <div class="enquiry-modal-eyebrow">
              <span class="eyebrow-line"></span>
              <span class="eyebrow-text">PRIVATE CONSULTATION</span>
              <span class="eyebrow-line"></span>
            </div>

            <h2 class="enquiry-modal-title" id="enquiryModalTitle">
              DISCOVER YOUR NEXT<br>
              <span class="title-gold">RESIDENCE</span>
            </h2>

            <p class="enquiry-modal-subtitle" id="enquiryModalSubtitle">
              Share your details and our property advisors will help you explore exclusive SOBHA developments, pricing, availability, and payment plans.
            </p>

            <form id="globalEnquiryForm" class="enquiry-modal-form" novalidate>
              <input type="hidden" id="enquirySourcePage" name="sourcePage" value="">
              <input type="hidden" id="enquirySourceContext" name="sourceContext" value="">
              <input type="hidden" id="enquiryRenderTs" name="form_render_ts" value="">
              
              <!-- Honeypot Field (invisible to genuine users, traps automated bots) -->
              <div style="opacity:0;position:absolute;top:0;left:0;height:0;width:0;z-index:-1;overflow:hidden;">
                <label for="enquiryHoneypot">Leave this blank</label>
                <input type="text" id="enquiryHoneypot" name="company_website" tabindex="-1" autocomplete="off" value="">
              </div>

              <div class="enquiry-form-grid">
                <!-- Full Name -->
                <div class="enquiry-field-group">
                  <label for="enquiryFullName" class="enquiry-field-label">FULL NAME *</label>
                  <div class="enquiry-input-box" id="enquiryFullNameBox">
                    <span class="enquiry-input-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                      </svg>
                    </span>
                    <input type="text" id="enquiryFullName" name="fullName" placeholder="Enter your full name" autocomplete="name" required>
                  </div>
                  <div class="enquiry-field-error" id="enquiryFullNameError"></div>
                </div>

                <!-- Email Address -->
                <div class="enquiry-field-group">
                  <label for="enquiryEmail" class="enquiry-field-label">EMAIL ADDRESS *</label>
                  <div class="enquiry-input-box" id="enquiryEmailBox">
                    <span class="enquiry-input-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                      </svg>
                    </span>
                    <input type="email" id="enquiryEmail" name="email" placeholder="Enter your email address" autocomplete="email" required>
                  </div>
                  <div class="enquiry-field-error" id="enquiryEmailError"></div>
                </div>

                <!-- Phone Number with Country Code -->
                <div class="enquiry-field-group">
                  <label for="enquiryPhone" class="enquiry-field-label">PHONE NUMBER *</label>
                  <div class="enquiry-input-box phone-input-box" id="enquiryPhoneBox">
                    <span class="enquiry-input-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                      </svg>
                    </span>
                    
                    <div class="country-code-picker" id="countryCodePicker" tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox">
                      <span class="country-flag" id="selectedFlag"><img src="https://flagcdn.com/w40/ae.png" srcset="https://flagcdn.com/w80/ae.png 2x" width="20" height="14" alt="AE" class="country-flag-img"></span>
                      <span class="country-dial-code" id="selectedDialCode">+971</span>
                      <svg class="chevron-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                      
                      <div class="country-dropdown-menu" id="countryDropdownMenu" role="listbox"></div>
                    </div>
                    <input type="hidden" id="enquiryCountryCode" name="countryCode" value="+971">

                    <span class="phone-divider"></span>

                    <input type="tel" id="enquiryPhone" name="phone" placeholder="Enter your phone number" autocomplete="tel" required>
                  </div>
                  <div class="enquiry-field-error" id="enquiryPhoneError"></div>
                </div>

                <!-- Development of Interest -->
                <div class="enquiry-field-group">
                  <label for="enquiryDevelopment" class="enquiry-field-label">DEVELOPMENT OF INTEREST *</label>
                  <div class="enquiry-input-box select-box" id="enquiryDevBox">
                    <span class="enquiry-input-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="2"></line>
                        <path d="M14 6h2"></path>
                        <path d="M14 10h2"></path>
                        <path d="M14 14h2"></path>
                        <path d="M14 18h2"></path>
                      </svg>
                    </span>
                    <select id="enquiryDevelopment" name="development" required>
                      <option value="Open to All Developments">Open to All Developments</option>
                      <option value="SOBHA Solis">SOBHA Solis</option>
                      <option value="SOBHA Orbis">SOBHA Orbis</option>
                      <option value="SOBHA Sanctuary Villas">SOBHA Sanctuary Villas</option>
                      <option value="SOBHA Central">SOBHA Central</option>
                      <option value="SOBHA Siniya Island">SOBHA Siniya Island</option>
                    </select>
                    <span class="select-chevron">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </span>
                  </div>
                  <div class="enquiry-field-error" id="enquiryDevError"></div>
                </div>
              </div>

              <!-- Submit Button -->
              <button type="submit" class="enquiry-submit-btn" id="enquirySubmitBtn">
                <span class="btn-text">SUBMIT ENQUIRY</span>
                <span class="btn-arrow">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </span>
                <span class="btn-spinner" style="display:none;">
                  <svg class="spinner-svg" width="18" height="18" viewBox="0 0 24 24"><circle class="path" cx="12" cy="12" r="9" fill="none" stroke-width="2.5"></circle></svg>
                </span>
              </button>

              <div class="enquiry-global-error" id="enquiryGlobalError" style="display:none;"></div>

              <!-- Confidentiality Note -->
              <div class="enquiry-modal-footer">
                <span class="lock-icon">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                  </svg>
                </span>
                <span class="footer-note">Your information is kept confidential and used only for property advisory purposes.</span>
              </div>
            </form>
          </div>

          <!-- Success Confirmation View -->
          <div class="enquiry-success-view" id="enquirySuccessView" style="display:none;">
            <div class="success-emblem-wrap">
              <div class="success-ring"></div>
              <div class="success-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
              </div>
            </div>
            <p class="success-eyebrow">ENQUIRY CONFIRMED</p>
            <h3 class="success-title">Thank You, <span id="successClientName">Valued Client</span></h3>
            <p class="success-desc">
              Your confidential enquiry regarding <strong id="successProjectName" style="color:#C5A880;">SOBHA Developments</strong> has been prioritized. Our senior property advisor will contact you within 2 business hours with exclusive inventory, payment schedules, and verified developer pricing.
            </p>
            <div class="success-ref-card">
              <span class="ref-label">VIP REFERENCE ID</span>
              <span class="ref-code" id="successRefCode">#SBH-94821</span>
            </div>
            <div>
              <button type="button" class="success-close-btn" id="successCloseBtn">
                <span>RETURN TO RESIDENCES</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    return document.getElementById('luxuryEnquiryModal');
  }

  // Auto-detect visitor country via internal geo-detect API with client-side fallback
  let detectedCountry = null;
  function detectVisitorCountry(callback) {
    if (detectedCountry) {
      if (callback) callback(detectedCountry);
      return;
    }

    function tryExternalFallback() {
      // Free public client fallback in case local server doesn't execute PHP (e.g. static server / Live Server port 5500)
      fetch('https://ipapi.co/json/')
        .then(function (res) { return res.json(); })
        .then(function (data) {
          if (data && data.country_calling_code) {
            detectedCountry = {
              code: data.country_calling_code.startsWith('+') ? data.country_calling_code : '+' + data.country_calling_code,
              iso: (data.country_code || 'AE').toLowerCase(),
              name: data.country_name || 'United Arab Emirates'
            };
            if (callback) callback(detectedCountry);
          } else {
            fallbackDefault();
          }
        })
        .catch(fallbackDefault);
    }

    function fallbackDefault() {
      detectedCountry = { code: '+971', iso: 'ae', name: 'United Arab Emirates' };
      if (callback) callback(detectedCountry);
    }

    fetch('api/geo-detect.php')
      .then(function (res) {
        if (!res.ok) throw new Error('Not found');
        return res.json();
      })
      .then(function (data) {
        if (data && data.success && data.dial_code) {
          detectedCountry = {
            code: data.dial_code,
            iso: (data.country_code || 'AE').toLowerCase(),
            name: data.country || 'United Arab Emirates'
          };
          if (callback) callback(detectedCountry);
        } else {
          tryExternalFallback();
        }
      })
      .catch(tryExternalFallback);
  }

  // Populate Country Dropdown with Search & Auto-Detection
  function initCountryDropdown() {
    const menu = document.getElementById('countryDropdownMenu');
    const picker = document.getElementById('countryCodePicker');
    const selectedFlag = document.getElementById('selectedFlag');
    const selectedDial = document.getElementById('selectedDialCode');
    const hiddenInput = document.getElementById('enquiryCountryCode');

    if (!menu || !picker) return;

    menu.innerHTML = `
      <div class="country-dropdown-search-wrap">
        <input type="text" class="country-dropdown-search" id="countrySearchInput" placeholder="Search country or code..." autocomplete="off">
      </div>
      <div class="country-dropdown-list" id="countryDropdownList"></div>
    `;

    const searchInput = document.getElementById('countrySearchInput');
    const listContainer = document.getElementById('countryDropdownList');

    function renderOptions(filterText) {
      listContainer.innerHTML = '';
      const filter = (filterText || '').toLowerCase().trim();
      const filtered = COUNTRY_CODES.filter(function (c) {
        return !filter || c.name.toLowerCase().includes(filter) || c.code.includes(filter) || c.iso.includes(filter);
      });

      if (filtered.length === 0) {
        listContainer.innerHTML = '<div style="padding:10px 14px;color:#8E8A82;font-size:12px;">No countries found</div>';
        return;
      }

      filtered.forEach(function (c) {
        const opt = document.createElement('div');
        opt.className = 'country-opt' + (hiddenInput.value === c.code ? ' selected' : '');
        opt.setAttribute('data-code', c.code);
        opt.setAttribute('data-iso', c.iso);
        opt.innerHTML = `
          <span class="opt-flag">${getFlagHtml(c.iso, c.name)}</span>
          <span class="opt-name">${c.name}</span>
          <span class="opt-code">${c.code}</span>
        `;
        opt.addEventListener('click', function (e) {
          e.stopPropagation();
          setCountry(c.iso, c.code);
          picker.classList.remove('open');
          picker.setAttribute('aria-expanded', 'false');
          const phoneInput = document.getElementById('enquiryPhone');
          if (phoneInput) phoneInput.focus();
        });
        listContainer.appendChild(opt);
      });
    }

    function setCountry(iso, code) {
      if (selectedFlag) selectedFlag.innerHTML = getFlagHtml(iso);
      if (selectedDial) selectedDial.textContent = code;
      if (hiddenInput) hiddenInput.value = code;
      renderOptions(searchInput ? searchInput.value : '');
    }

    if (searchInput) {
      searchInput.addEventListener('input', function () {
        renderOptions(this.value);
      });
      searchInput.addEventListener('click', function (e) {
        e.stopPropagation();
      });
    }

    // Auto-detect visitor's country on load
    detectVisitorCountry(function (geo) {
      const match = COUNTRY_CODES.find(function (c) {
        return (geo.iso && c.iso === geo.iso.toLowerCase()) || c.code === geo.code;
      });
      if (match) {
        setCountry(match.iso, match.code);
      } else {
        setCountry(geo.iso || 'ae', geo.code || '+971');
      }
    });

    renderOptions('');

    picker.addEventListener('click', function (e) {
      e.stopPropagation();
      const isOpen = picker.classList.contains('open');
      picker.classList.toggle('open', !isOpen);
      picker.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
      if (!isOpen && searchInput) {
        setTimeout(function () { searchInput.focus(); }, 100);
      }
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!picker.contains(e.target)) {
        picker.classList.remove('open');
        picker.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Validation functions
  // Global disposable email domains list
  const DISPOSABLE_EMAIL_DOMAINS = [
    'mailinator.com', 'tempmail.com', 'guerrillamail.com', '10minutemail.com',
    'throwawaymail.com', 'yopmail.com', 'trashmail.com', 'sharklasers.com',
    'getairmail.com', 'dispostable.com', 'fakeinbox.com', 'fakemailgenerator.com'
  ];

  function validateFullName(name) {
    if (!name || name.trim().length < 2) {
      return 'Please enter your full name (at least 2 letters)';
    }
    // Letters, spaces, apostrophes and hyphens only
    if (!/^[\p{L}\s'\-\.]{2,80}$/u.test(name.trim())) {
      return 'Please enter letters only (no numbers or special symbols)';
    }
    return '';
  }

  function validateEmail(email) {
    if (!email || !email.trim()) {
      return 'Please enter your email address';
    }
    const clean = email.trim().toLowerCase();
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
    if (!emailRegex.test(clean)) {
      return 'Please enter a valid email address';
    }
    const domain = clean.split('@')[1];
    if (domain && DISPOSABLE_EMAIL_DOMAINS.includes(domain)) {
      return 'Please enter a valid personal or business email address';
    }
    return '';
  }

  function validatePhone(phone) {
    if (!phone || !phone.trim()) {
      return 'Please enter your phone number';
    }
    const digits = phone.replace(/\D/g, '');
    if (digits.length < 6 || digits.length > 15) {
      return 'Please enter a valid international phone number (6-15 digits)';
    }
    // Check if all digits are the same (e.g. 9999999999)
    if (/^(\d)\1+$/.test(digits)) {
      return 'Please enter a valid, active phone number';
    }
    // Check for sequential ascending or descending (e.g. 123456789 or 987654321)
    if ('0123456789012345'.includes(digits) || '9876543210987654'.includes(digits)) {
      return 'Please enter a valid contact phone number';
    }
    return '';
  }

  function setFieldError(fieldId, errorMsg) {
    const box = document.getElementById(fieldId + 'Box');
    const errEl = document.getElementById(fieldId + 'Error');
    if (box) box.classList.toggle('has-error', !!errorMsg);
    if (errEl) {
      errEl.textContent = errorMsg;
      errEl.classList.toggle('visible', !!errorMsg);
    }
  }

  function clearAllErrors() {
    ['enquiryFullName', 'enquiryEmail', 'enquiryPhone', 'enquiryDev'].forEach(function (id) {
      setFieldError(id, '');
    });
    const globalErr = document.getElementById('enquiryGlobalError');
    if (globalErr) {
      globalErr.textContent = '';
      globalErr.style.display = 'none';
    }
  }

  // Generate Reference ID
  function generateReferenceCode() {
    const chars = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    let code = '';
    for (let i = 0; i < 5; i++) {
      code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return '#SBH-' + code;
  }

  // Public API: Open Modal
  window.openEnquiryModal = function (targetDevelopment, sourceContext, customTitle, customSubtitle) {
    ensureModalExists();
    const modal = document.getElementById('luxuryEnquiryModal');
    if (!modal) return;

    // Reset view
    const body = document.getElementById('enquiryModalBody');
    const successView = document.getElementById('enquirySuccessView');
    if (body) body.style.display = 'block';
    if (successView) successView.style.display = 'none';

    clearAllErrors();

    // Custom or default title & subtitle
    const titleEl = document.getElementById('enquiryModalTitle');
    const subtitleEl = document.getElementById('enquiryModalSubtitle');
    if (titleEl) {
      titleEl.innerHTML = customTitle || 'DISCOVER YOUR NEXT<br><span class="title-gold">RESIDENCE</span>';
    }
    if (subtitleEl) {
      subtitleEl.textContent = customSubtitle || 'Share your details and our property advisors will help you explore exclusive SOBHA developments, pricing, availability, and payment plans.';
    }

    // Preselect development
    const devSelect = document.getElementById('enquiryDevelopment');
    const resolvedDev = resolveDevelopment(targetDevelopment);
    if (devSelect) {
      devSelect.value = resolvedDev;
    }

    // Capture context & render timestamp
    const sourcePageInput = document.getElementById('enquirySourcePage');
    const sourceContextInput = document.getElementById('enquirySourceContext');
    const renderTsInput = document.getElementById('enquiryRenderTs');
    if (sourcePageInput) sourcePageInput.value = window.location.pathname.split('/').pop() || 'index.html';
    if (sourceContextInput) sourceContextInput.value = sourceContext || 'Direct Enquiry CTA';
    if (renderTsInput) renderTsInput.value = Math.floor(Date.now() / 1000);

    // Show modal & prevent background scroll
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    // Focus first input
    setTimeout(function () {
      const nameInput = document.getElementById('enquiryFullName');
      if (nameInput) nameInput.focus();
    }, 150);
  };

  // Public API: Close Modal
  window.closeEnquiryModal = function () {
    const modal = document.getElementById('luxuryEnquiryModal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';

    // Close any country dropdown
    const picker = document.getElementById('countryCodePicker');
    if (picker) {
      picker.classList.remove('open');
      picker.setAttribute('aria-expanded', 'false');
    }
  };

  // Form submission handling
  function handleModalFormSubmit(e) {
    if (e && e.preventDefault) e.preventDefault();

    const nameInput = document.getElementById('enquiryFullName');
    const emailInput = document.getElementById('enquiryEmail');
    const phoneInput = document.getElementById('enquiryPhone');
    const countryCodeInput = document.getElementById('enquiryCountryCode');
    const devSelect = document.getElementById('enquiryDevelopment');
    const submitBtn = document.getElementById('enquirySubmitBtn');
    const sourcePageInput = document.getElementById('enquirySourcePage');
    const sourceContextInput = document.getElementById('enquirySourceContext');
    const honeypotInput = document.getElementById('enquiryHoneypot');
    const renderTsInput = document.getElementById('enquiryRenderTs');

    const fullName = nameInput ? nameInput.value.trim() : '';
    const email = emailInput ? emailInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const countryCode = countryCodeInput ? countryCodeInput.value : '+971';
    const development = devSelect ? devSelect.value : 'Open to All Developments';
    const sourcePage = sourcePageInput ? sourcePageInput.value : window.location.pathname;
    const sourceContext = sourceContextInput ? sourceContextInput.value : '';
    const honeypot = honeypotInput ? honeypotInput.value : '';
    const renderTs = renderTsInput ? renderTsInput.value : Math.floor(Date.now() / 1000);

    // Validate
    clearAllErrors();
    const nameErr = validateFullName(fullName);
    const emailErr = validateEmail(email);
    const phoneErr = validatePhone(phone);

    let hasError = false;
    if (nameErr) { setFieldError('enquiryFullName', nameErr); hasError = true; }
    if (emailErr) { setFieldError('enquiryEmail', emailErr); hasError = true; }
    if (phoneErr) { setFieldError('enquiryPhone', phoneErr); hasError = true; }

    if (hasError) {
      if (nameErr && nameInput) nameInput.focus();
      else if (emailErr && emailInput) emailInput.focus();
      else if (phoneErr && phoneInput) phoneInput.focus();
      return false;
    }

    // Set Loading State
    if (submitBtn) {
      submitBtn.disabled = true;
      const btnText = submitBtn.querySelector('.btn-text');
      const btnArrow = submitBtn.querySelector('.btn-arrow');
      const btnSpinner = submitBtn.querySelector('.btn-spinner');
      if (btnText) btnText.textContent = 'PROCESSING ENQUIRY...';
      if (btnArrow) btnArrow.style.display = 'none';
      if (btnSpinner) btnSpinner.style.display = 'inline-flex';
    }

    // Capture UTM tracking parameters from URL / Session
    const utmParams = getUtmParameters();

    // Prepare Lead Payload for backend API
    const refId = generateReferenceCode();
    const payload = {
      name: fullName,
      email: email,
      phone: phone,
      country_code: countryCode,
      project: development,
      source_button: sourceContext,
      page_url: window.location.href,
      referrer_url: document.referrer || '',
      company_website: honeypot, // Honeypot trap
      form_render_ts: renderTs,
      utm_source: utmParams.utm_source || '',
      utm_medium: utmParams.utm_medium || '',
      utm_campaign: utmParams.utm_campaign || '',
      utm_term: utmParams.utm_term || '',
      utm_content: utmParams.utm_content || ''
    };

    // Client-side local backup record
    const leadBackup = {
      id: refId,
      timestamp: new Date().toISOString(),
      fullName: fullName,
      email: email,
      countryCode: countryCode,
      phoneNumber: phone,
      fullPhone: countryCode + ' ' + phone,
      development: development,
      sourcePage: sourcePage,
      sourceContext: sourceContext
    };

    try {
      const storedLeads = JSON.parse(localStorage.getItem('sobha_luxury_enquiries') || '[]');
      storedLeads.unshift(leadBackup);
      localStorage.setItem('sobha_luxury_enquiries', JSON.stringify(storedLeads));
    } catch (e) {}

    // Send payload to backend API (api/submit-lead.php)
    fetch('api/submit-lead.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify(payload)
    })
    .then(function (res) {
      return res.text().then(function (text) {
        try {
          return JSON.parse(text);
        } catch (e) {
          console.error('Non-JSON response from server:', text);
          throw new Error('Server returned an unexpected response. Please try again.');
        }
      });
    })
    .then(function (data) {
      if (data && data.success === false) {
        const errorMsg = data.error || data.message || 'Verification failed. Please review your details.';
        // Validation rejection from backend
        if (data.field === 'name' || data.field === 'full_name') setFieldError('enquiryFullName', errorMsg);
        else if (data.field === 'email') setFieldError('enquiryEmail', errorMsg);
        else if (data.field === 'phone') setFieldError('enquiryPhone', errorMsg);
        else {
          const globalErr = document.getElementById('enquiryGlobalError');
          if (globalErr) {
            globalErr.textContent = errorMsg;
            globalErr.style.display = 'block';
          }
        }
        restoreSubmitBtn();
        return;
      }

      // Success
      try {
        window.dispatchEvent(new CustomEvent('sobha:enquirySubmitted', { detail: payload }));
      } catch (e) {}

      // Redirect immediately to thank you page
      const redirectTarget = (data && data.redirect_url) ? data.redirect_url : 'thank-you.html';
      window.location.href = redirectTarget;
    })
    .catch(function (err) {
      console.error('Enquiry submission failed:', err);
      const globalErr = document.getElementById('enquiryGlobalError');
      if (globalErr) {
        globalErr.textContent = err.message || 'Connection error. Please try again or contact us directly.';
        globalErr.style.display = 'block';
      }
      restoreSubmitBtn();
    });

    function restoreSubmitBtn() {
      if (submitBtn) {
        submitBtn.disabled = false;
        const btnText = submitBtn.querySelector('.btn-text');
        const btnArrow = submitBtn.querySelector('.btn-arrow');
        const btnSpinner = submitBtn.querySelector('.btn-spinner');
        if (btnText) btnText.textContent = 'SUBMIT ENQUIRY';
        if (btnArrow) btnArrow.style.display = 'inline-flex';
        if (btnSpinner) btnSpinner.style.display = 'none';
      }
    }

    return false;
  }

  // Parse UTM parameters from URL and session
  function getUtmParameters() {
    const params = {};
    try {
      const urlParams = new URLSearchParams(window.location.search);
      ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'].forEach(function (key) {
        const val = urlParams.get(key);
        if (val) {
          params[key] = val;
          try { sessionStorage.setItem('sobha_' + key, val); } catch (e) {}
        } else {
          try {
            const saved = sessionStorage.getItem('sobha_' + key);
            if (saved) params[key] = saved;
          } catch (e) {}
        }
      });
    } catch (e) {}
    return params;
  }

  // Setup Event Listeners for the Modal
  function setupModalEvents() {
    const modal = document.getElementById('luxuryEnquiryModal');
    if (!modal) return;

    const closeBtn = document.getElementById('enquiryModalClose');
    if (closeBtn) closeBtn.addEventListener('click', window.closeEnquiryModal);

    const successCloseBtn = document.getElementById('successCloseBtn');
    if (successCloseBtn) successCloseBtn.addEventListener('click', window.closeEnquiryModal);

    // Backdrop click outside dialog
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        window.closeEnquiryModal();
      }
    });

    // Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        window.closeEnquiryModal();
      }
    });

    // Real-time error clearance
    ['enquiryFullName', 'enquiryEmail', 'enquiryPhone'].forEach(function (id) {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener('input', function () {
          setFieldError(id, '');
        });
      }
    });

    const form = document.getElementById('globalEnquiryForm');
    if (form) {
      form.addEventListener('submit', handleModalFormSubmit);
    }

    initCountryDropdown();
  }

  // Universal Enquiry Button Click Interceptor
  function bindGlobalEnquiryTriggers() {
    document.addEventListener('click', function (e) {
      const target = e.target;
      const btn = target.closest('a, button');
      if (!btn) return;

      // Check if button is explicitly flagged for modal
      const isExplicitModal = btn.hasAttribute('data-enquiry-modal') ||
                             btn.hasAttribute('data-open-enquiry') ||
                             btn.classList.contains('dev-btn-enquire');

      // Check href
      const href = btn.getAttribute('href') || '';
      const isContactLink = href.includes('#contact') || href === '#enquiry';

      // Check text content
      const text = (btn.textContent || '').trim().toLowerCase();
      const isEnquiryText = text.includes('enquire') ||
                            text.includes('request a consultation') ||
                            text.includes('request consultation') ||
                            text.includes('book consultation') ||
                            text.includes('schedule a private consultation') ||
                            text.includes('request digital brochure') ||
                            text.includes('download brochure') ||
                            text.includes('request solis price sheet') ||
                            text.includes('request current villa pricing') ||
                            text.includes('request availability') ||
                            text.includes('request price list') ||
                            text.includes('request villa portfolio') ||
                            text.includes('request specifications') ||
                            text.includes('request available floor plans') ||
                            text.includes('compare villa floor plans') ||
                            text.includes('contact & vip desk');

      // Exclude genuine navigation links, consultation form buttons, and on-page contact section
      if (btn.closest('#consultForm') || btn.closest('.consult-section') || btn.closest('.consult-card')) return;
      if (href.startsWith('tel:') || href.startsWith('mailto:') || href.includes('wa.me')) return;
      if (text.includes('view project') || text.includes('explore projects') || text.includes('explore solis') || text.includes('explore island') || text.includes('explore sanctuary')) return;
      if (btn.classList.contains('dev-btn-view')) return;
      if (btn.classList.contains('menu-toggle') || btn.classList.contains('mobile-close-btn') || btn.classList.contains('cookie-btn')) return;

      // If clicking #contact and the section exists on page, let default smooth scroll handle it
      if (href === '#contact' && document.getElementById('contact')) return;

      if (isExplicitModal || (href === '#enquiry') || (!document.getElementById('contact') && href.includes('#contact')) || (isEnquiryText && href.startsWith('#'))) {
        e.preventDefault();
        e.stopPropagation();

        // Determine project
        let project = btn.getAttribute('data-development') || btn.getAttribute('data-project');
        if (!project && href.includes('project=')) {
          const match = href.match(/project=([^&#]+)/);
          if (match && match[1]) project = match[1];
        }
        if (!project) {
          project = resolveDevelopment(text);
        }

        const context = btn.getAttribute('data-residence') ? (project + ' — ' + btn.getAttribute('data-residence')) : (btn.textContent || '').trim();
        const customTitle = btn.getAttribute('data-modal-title');
        const customSubtitle = btn.getAttribute('data-modal-subtitle');
        window.openEnquiryModal(project, context, customTitle, customSubtitle);
      }
    }, true);
  }

  // Initialize on DOM load
  function init() {
    ensureModalExists();
    setupModalEvents();
    bindGlobalEnquiryTriggers();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
