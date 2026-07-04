// ============================================
// Seat selection interactivity
// ============================================
document.addEventListener('DOMContentLoaded', function () {
  const seatEls = document.querySelectorAll('.seat:not(.booked)');
  const seatListEl = document.getElementById('seatList');
  const totalPriceEl = document.getElementById('totalPrice');
  const confirmBtn = document.getElementById('confirmBtn');
  const hiddenInput = document.getElementById('selectedSeatsInput');

  let selected = {}; // seat_id -> price

  function refreshSummary() {
    const ids = Object.keys(selected);
    if (ids.length === 0) {
      seatListEl.textContent = 'None';
      totalPriceEl.textContent = 'Rs. 0.00';
      confirmBtn.disabled = true;
    } else {
      const labels = [];
      let total = 0;
      ids.forEach(function (id) {
        const el = document.querySelector('.seat[data-seat-id="' + id + '"]');
        const row = el.closest('.seat-row').querySelector('.seat-row-label').textContent;
        labels.push(row + el.textContent.trim());
        total += parseFloat(selected[id]);
      });
      seatListEl.textContent = labels.join(', ');
      totalPriceEl.textContent = 'Rs. ' + total.toFixed(2);
      confirmBtn.disabled = false;
    }
    hiddenInput.value = ids.join(',');
  }

  seatEls.forEach(function (seat) {
    seat.addEventListener('click', function () {
      const id = seat.getAttribute('data-seat-id');
      const price = seat.getAttribute('data-price');
      if (selected[id]) {
        delete selected[id];
        seat.classList.remove('selected');
      } else {
        // Limit to 8 seats per booking for sanity
        if (Object.keys(selected).length >= 8) {
          alert('You can select up to 8 seats per booking.');
          return;
        }
        selected[id] = price;
        seat.classList.add('selected');
      }
      refreshSummary();
    });
  });

  refreshSummary();
});
