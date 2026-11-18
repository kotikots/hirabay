// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
}

// Settings menu toggle
function toggleSettingsMenu() {
    const menu = document.getElementById('settingsMenu');
    menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}

// Hide settings menu when clicking outside
window.addEventListener('click', function (e) {
    const settings = document.querySelector('.settings-wrapper');
    const menu = document.getElementById('settingsMenu');
    if (!settings.contains(e.target)) {
        menu.style.display = 'none';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const chatBtn = document.querySelector(".chatBtn button");
    const chatPanel = document.querySelector(".chat.chat-panel");
    const chatOverlay = document.querySelector(".chat-overlay");
    const closeBtn = document.querySelector(".users .close");

    // Ensure chat panel is hidden on load
    chatPanel.classList.add("hidden");

    // Toggle chat panel when chatBtn is clicked
    chatBtn.addEventListener("click", () => {
        chatPanel.classList.toggle("hidden");
        chatOverlay.classList.add("hidden"); // Always hide overlay when opening panel
    });

    // Hide chat panel when close button is clicked
    closeBtn.addEventListener("click", () => {
        chatPanel.classList.add("hidden");
    });

    // When user clicks on a user in users-list
    document.addEventListener("click", function (e) {
        const user = e.target.closest(".user-link");
        if (user && user.closest(".users-list")) {
            e.preventDefault();
            const userId = user.getAttribute("data-id");
            const userName = user.getAttribute("data-name");

            document.querySelector(".chat-overlay .details span").textContent = userName;
            document.querySelector(".typing-area input[name='incoming_id']").value = userId;

            chatOverlay.classList.remove("hidden");
            chatPanel.classList.add("hidden");
        }
    });

    // Back button hides overlay
    setTimeout(() => {
        const backIcon = document.querySelector(".back-icon");
        if (backIcon) {
            backIcon.addEventListener("click", function (e) {
                e.preventDefault();
                chatOverlay.classList.add("hidden");     // Hide the overlay
                chatPanel.classList.remove("hidden");    // Show the chat panel again
            });
        }
    }, 0);
});

// User search
const searchBar = document.querySelector(".users .search input"),
      searchBtn = document.querySelector(".users .search button"),
      usersList = document.querySelector(".users .users-list");

searchBtn.onclick = () => {
    searchBar.classList.toggle("active");
    searchBar.focus();
    searchBtn.classList.toggle("active");
    searchBar.value = "";
};

searchBar.onkeyup = () => {
    let searchTerm = searchBar.value;

    searchBar.classList.toggle("active", searchTerm !== "");

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/hirabay/form/homepage/php/search.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            usersList.innerHTML = xhr.response;
        }
    };
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("searchTerm=" + encodeURIComponent(searchTerm));
};

// Refresh users list
setInterval(() => {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/hirabay/form/homepage/php/chat.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200 && !searchBar.classList.contains("active")) {
            usersList.innerHTML = xhr.response;
        }
    };
    xhr.send();
}, 500);

// Chat message handling
const form = document.querySelector(".typing-area"),
      inputField = form.querySelector(".input-field"),
      sendBtn = form.querySelector("button"),
      chatBox = document.querySelector(".chat-box");

form.onsubmit = (e) => e.preventDefault();

sendBtn.onclick = () => {
    let formData = new FormData(form);
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/hirabay/form/homepage/php/insert-chat.php", true);
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            inputField.value = "";
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    };
    xhr.send(formData);
};

chatBox.onmouseenter = () => chatBox.classList.add("active");
chatBox.onmouseleave = () => chatBox.classList.remove("active");

// Refresh chat messages
setInterval(() => {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/hirabay/form/homepage/php/get-chat.php", true); // Correct
    xhr.onload = () => {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            chatBox.innerHTML = xhr.response;
            if (!chatBox.classList.contains("active")) scrollToBottom();
        }
    };
    let formData = new FormData(form);
    xhr.send(formData);
}, 500);

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}