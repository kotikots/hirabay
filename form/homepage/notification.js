function toggleNotifications() {
  const dropdown = document.getElementById("notificationDropdown");
  dropdown.classList.toggle("hidden");
  fetch('php/mark_notifications_read.php'); // backend PHP to mark as read
}
document.addEventListener("click", function (e) {
  const notifIcon = document.querySelector(".notification-icon");
  const dropdown = document.getElementById("notificationDropdown");
  if (!notifIcon.contains(e.target) && !dropdown.contains(e.target)) {
    dropdown.classList.add("hidden");
  }
});
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", function () {
      const notifId = this.dataset.id;

      fetch("php/delete_borrow_request.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `id=${notifId}`
      })
      .then(res => res.text())
      .then(response => {
        if (response.trim() === "deleted") {
          // Remove the notification from the DOM
          this.closest(".notif-item").remove();

          // Update notification count
          const countSpan = document.querySelector(".notification-count");
          let count = parseInt(countSpan.innerText);
          count = Math.max(count - 1, 0);
          if (count === 0) {
            countSpan.remove();
          } else {
            countSpan.innerText = count;
          }
        }
      });
    });
  });
});

document.addEventListener('click', function (e) {
  if (e.target.classList.contains('confirm-btn')) {
    const requestId = e.target.dataset.id;
    fetch('php/confirm_borrow_request.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `id=${requestId}`
    })
    .then(res => res.text())
    .then(data => {
      if (data.trim() === 'confirmed') {
        e.target.closest('.notif-item').remove(); // removes the card from dropdown
        const notifCount = document.querySelector('.notification-count');
        let current = parseInt(notifCount.textContent);
        notifCount.textContent = current - 1;
      } else {
        alert('Failed to confirm: ' + data);
      }
    });
  }
});



document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.confirm-return-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const borrowId = btn.dataset.id;
      if (confirm("Confirm this item has been returned?")) {
        fetch('php/mark_returned.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `borrow_id=${borrowId}`
        })
        .then(res => res.text())
        .then(data => {
          if (data.trim() === "success") {
            alert("Item marked as returned.");
            location.reload();
          } else {
            alert("Failed: " + data);
          }
        });
      }
    });
  });
});
