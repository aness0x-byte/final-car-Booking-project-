// ============================================================
// DriveEase — js/main.js (OPTIMIZED)
// Client-side JavaScript for interactivity
// ============================================================
// ✅ OPTIMIZATION #2: Debounce input events
// ✅ OPTIMIZATION #3: Cache DOM queries
// ============================================================

// ✅ OPTIMIZATION #3: Cache all DOM queries at module scope (once, not repeatedly)
const domCache = {
    navToggle: document.getElementById('navToggle'),
    navLinks: document.getElementById('navLinks'),
    searchInput: document.getElementById('carSearch'),
    carCards: document.querySelectorAll('.car-card'),
    noResults: document.getElementById('noResults'),
    pickupInput: document.getElementById('pickup_date'),
    returnInput: document.getElementById('return_date'),
    pricePerDay: parseFloat(document.getElementById('price_per_day')?.value || 0),
    totalDisplay: document.getElementById('totalDisplay'),
    totalInput: document.getElementById('total_price'),
    daysDisplay: document.getElementById('daysDisplay'),
    forms: document.querySelectorAll('form.validated')
};

// ✅ OPTIMIZATION #2: Debounce helper function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Wait until the page is fully loaded
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. NAVBAR HAMBURGER MENU ──────────────────────────
    if (domCache.navToggle && domCache.navLinks) {
        domCache.navToggle.addEventListener('click', function () {
            domCache.navLinks.classList.toggle('open');
        });
    }

    // ── 2. LIVE CAR SEARCH (with debouncing) ───────────────
    function filterCars() {
        if (!domCache.searchInput) return;

        const query = domCache.searchInput.value.toLowerCase().trim();
        let visible = 0;

        // ✅ OPTIMIZATION #3: Use cached carCards reference
        domCache.carCards.forEach(function (card) {
            const name = (card.dataset.name || '').toLowerCase();
            const brand = (card.dataset.brand || '').toLowerCase();
            const matches = !query || name.includes(query) || brand.includes(query);

            if (matches) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        if (domCache.noResults) {
            domCache.noResults.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    // ✅ OPTIMIZATION #2: Debounce search with 150ms delay
    if (domCache.searchInput) {
        const debouncedFilter = debounce(filterCars, 150);
        domCache.searchInput.addEventListener('input', debouncedFilter);
    }

    // ── 3. BOOKING PRICE CALCULATOR ────────────────────────
    function calculateTotal() {
        if (!domCache.pickupInput || !domCache.returnInput || !domCache.totalDisplay) return;

        const pickup = new Date(domCache.pickupInput.value);
        const ret = new Date(domCache.returnInput.value);

        if (pickup && ret && ret > pickup) {
            const diffMs = ret - pickup;
            const days = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            const total = days * domCache.pricePerDay;

            if (domCache.daysDisplay) domCache.daysDisplay.textContent = days + ' day' + (days > 1 ? 's' : '');
            domCache.totalDisplay.textContent = total.toLocaleString() + ' DZD';
            if (domCache.totalInput) domCache.totalInput.value = total.toFixed(2);

            const carTotalEl = document.getElementById('carTotalDisplay');
            if (carTotalEl) carTotalEl.textContent = total.toLocaleString() + ' DZD';
        }
    }

    if (domCache.pickupInput) domCache.pickupInput.addEventListener('change', calculateTotal);
    if (domCache.returnInput) domCache.returnInput.addEventListener('change', calculateTotal);

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    if (domCache.pickupInput) domCache.pickupInput.min = today;
    if (domCache.returnInput) domCache.returnInput.min = today;

    // When pickup date changes, return date must be after it
    if (domCache.pickupInput && domCache.returnInput) {
        domCache.pickupInput.addEventListener('change', function () {
            domCache.returnInput.min = this.value;
            if (domCache.returnInput.value && domCache.returnInput.value <= this.value) {
                domCache.returnInput.value = '';
            }
        });
    }

    // ── 4. FORM VALIDATION ────────────────────────────────
    domCache.forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            form.querySelectorAll('[required]').forEach(function (input) {
                if (!input.value.trim()) {
                    input.style.borderColor = 'var(--danger)';
                    valid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            if (!valid) {
                e.preventDefault();
                const err = form.querySelector('.alert-error');
                if (err) err.style.display = 'block';
            }
        });
    });

});
