// ============================================================
// DriveEase — js/main.js
// Client-side JavaScript for interactivity
// ============================================================
// This file handles:
//   1. Responsive navbar hamburger menu (for mobile)
//   2. Live car search filtering (no page reload)
//   3. Booking date price calculator
// ============================================================

// Wait until the page is fully loaded before running JavaScript
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. NAVBAR HAMBURGER MENU ──────────────────────────
    // On mobile devices, a hamburger icon (☰) appears.
    // Clicking it shows/hides the navigation links.
    var toggle   = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');

    if (toggle && navLinks) {
        toggle.addEventListener('click', function () {
            // toggle = add if missing, remove if present
            navLinks.classList.toggle('open');
        });
    }

    // ── 2. LIVE CAR SEARCH ────────────────────────────────
    // When the user types in the search box, we hide/show
    // car cards that match the typed text. No page reload needed.
    var searchInput = document.getElementById('carSearch');
    var carCards    = document.querySelectorAll('.car-card');
    var noResults   = document.getElementById('noResults');

    function filterCars() {
        // If there's no search input on the page, do nothing
        if (!searchInput) return;

        // Get what the user typed, in lowercase
        var query   = searchInput.value.toLowerCase().trim();
        var visible = 0; // count how many cards are visible

        // Go through each car card
        carCards.forEach(function (card) {
            var name  = (card.dataset.name  || '').toLowerCase();
            var brand = (card.dataset.brand || '').toLowerCase();

            // Check if the car name or brand contains the search text
            var matches = !query || name.includes(query) || brand.includes(query);

            if (matches) {
                card.style.display = ''; // show the card
                visible++;
            } else {
                card.style.display = 'none'; // hide the card
            }
        });

        // Show "No results" message if nothing matches
        if (noResults) {
            noResults.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    // Run the filter function every time the user types
    if (searchInput) {
        searchInput.addEventListener('input', filterCars);
    }

    // ── 3. BOOKING PRICE CALCULATOR ──────────────────────
    // On the booking page: automatically calculates the total price
    // when the user picks a pickup date and return date.
    var pickupInput  = document.getElementById('pickup_date');
    var returnInput  = document.getElementById('return_date');
    var pricePerDay  = parseFloat(document.getElementById('price_per_day')?.value || 0);
    var totalDisplay = document.getElementById('totalDisplay');
    var totalInput   = document.getElementById('total_price');
    var daysDisplay  = document.getElementById('daysDisplay');

    function calculateTotal() {
        // If we're not on the booking page, do nothing
        if (!pickupInput || !returnInput || !totalDisplay) return;

        var pickup = new Date(pickupInput.value);
        var ret    = new Date(returnInput.value);

        // Only calculate if return date is after pickup date
        if (pickup && ret && ret > pickup) {
            // Calculate how many days between the two dates
            var diffMs   = ret - pickup;
            var days     = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
            var total    = days * pricePerDay;

            // Update what the user sees on screen
            if (daysDisplay) daysDisplay.textContent = days + ' day' + (days > 1 ? 's' : '');
            totalDisplay.textContent = total.toLocaleString() + ' DZD';
            if (totalInput) totalInput.value = total.toFixed(2);

            // Update the subtotal row if present
            var carTotalEl = document.getElementById('carTotalDisplay');
            if (carTotalEl) carTotalEl.textContent = total.toLocaleString() + ' DZD';
        }
    }

    // Re-calculate when dates change
    if (pickupInput) pickupInput.addEventListener('change', calculateTotal);
    if (returnInput) returnInput.addEventListener('change', calculateTotal);

    // Set minimum date to today (users can't book in the past)
    var today = new Date().toISOString().split('T')[0];
    if (pickupInput) pickupInput.min = today;
    if (returnInput) returnInput.min = today;

    // When pickup date changes, return date must be after it
    if (pickupInput && returnInput) {
        pickupInput.addEventListener('change', function () {
            returnInput.min = this.value;
            if (returnInput.value && returnInput.value <= this.value) {
                returnInput.value = ''; // clear invalid return date
            }
        });
    }

    // ── 4. FORM VALIDATION ───────────────────────────────
    // Highlight empty required fields when a form is submitted
    var forms = document.querySelectorAll('form.validated');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var valid = true;
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
                var err = form.querySelector('.alert-error');
                if (err) err.style.display = 'block';
            }
        });
    });

});
