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

let reportingIssue = null;
let waitingForConfirmation = false;
let readyToRedirect = false;

let reportStage = 0;
let reportData = {
    id: '',
    name: '',
    description: ''
};

document.addEventListener('DOMContentLoaded', function () {
    addMessage("👋 Welcome! If you have any questions, just type the word 'help'.", 'bot');
});

function sendMessage() {
    const userInputField = document.getElementById('userInput');
    const userMessage = userInputField.value.trim();
    if (userMessage === "") return;

    addMessage(userMessage, 'user');
    userInputField.value = '';

    const chatMessages = document.getElementById('chatMessages');
    const typingIndicator = document.createElement('div');
    typingIndicator.className = 'bot-message';
    typingIndicator.id = 'typing-indicator';
    typingIndicator.innerHTML = `
        <div class="message-bubble typing">
            <span class="dot"></span><span class="dot"></span><span class="dot"></span>
        </div>`;
    chatMessages.appendChild(typingIndicator);
    chatMessages.scrollTop = chatMessages.scrollHeight;

    setTimeout(() => {
        const botReply = generateBotReply(userMessage);
        const typingDiv = document.getElementById('typing-indicator');
        if (typingDiv) typingDiv.remove();
        addMessage(botReply, 'bot');

        if (readyToRedirect) {
            readyToRedirect = false;
            setTimeout(() => {
                window.location.href = 'listing1 real.html';
            }, 1000);
        }
    }, 1200);
}

function addMessage(message, sender) {
    const chatMessages = document.getElementById('chatMessages');
    const messageWrapper = document.createElement('div');
    messageWrapper.className = sender === 'user' ? 'user-message' : 'bot-message';

    const messageDiv = document.createElement('div');
    messageDiv.className = 'message-bubble';
    messageDiv.innerHTML = sender === 'bot' ? message : escapeHTML(message);

    messageWrapper.appendChild(messageDiv);
    chatMessages.appendChild(messageWrapper);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function escapeHTML(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function generateBotReply(userMessage) {
    const lower = userMessage.toLowerCase();

    if (reportStage > 0) {
        switch (reportStage) {
            case 1:
                reportData.id = userMessage;
                reportStage = 2;
                return "Please provide the name of the seller/buyer. 🧑";

            case 2:
                reportData.name = userMessage;
                reportStage = 3;
                return "Now, please describe the issue you experienced. ✍️";

            case 3:
                reportData.description = userMessage;

                fetch('php/save_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                      userType: reportingIssue,
                      userId: reportData.id,
                      userName: reportData.name,
                      issue: reportData.description
                    })
                  })
                  .then(res => res.json())
                  .then(response => console.log("Server Response:", response))
                  .catch(error => console.error("Fetch Error:", error));
                  

                const fullReport = `✅ Thank you for reporting!<br><br>` +
                    `<b>${reportingIssue === 'buyer' ? 'Buyer' : 'Seller'} ID:</b> ${reportData.id}<br>` +
                    `<b>Name:</b> ${reportData.name}<br>` +
                    `<b>Issue:</b> ${reportData.description}<br><br>` +
                    `Our team will review this shortly. 🙌`;

                reportingIssue = null;
                reportStage = 0;
                reportData = { id: '', name: '', description: '' };
                return fullReport;
        }
    }

    if (waitingForConfirmation) {
        if (lower.includes('yes')) {
            waitingForConfirmation = false;
            readyToRedirect = true;
            return "Redirecting you to create a listing... ⏳";
        } else if (lower.includes('no')) {
            waitingForConfirmation = false;
            return "Alright! Let me know if you change your mind. 😊";
        } else {
            return "Please reply with 'yes' or 'no'.";
        }
    }

    switch (true) {
        case lower.includes('report seller'):
            reportingIssue = 'seller';
            reportStage = 1;
            return "Please provide the ID number of the seller. 📋";

        case lower.includes('report buyer'):
            reportingIssue = 'buyer';
            reportStage = 1;
            return "Please provide the ID number of the buyer. 📋";

        case lower.includes('create listing'):
            waitingForConfirmation = true;
            return "Do you want to create a new listing? (yes/no)";

        case lower.includes('hello'):
        case lower.includes('hi'):
            return "Hello there! 👋 How can I assist you today?";

        case lower.includes('buy'):
            return "Sure! You can browse items in the Buy section 🛒.";

        case lower.includes('sell'):
            return "You can sell an item by clicking on 'Create New'. ➕";

        case lower.includes('payment'):
            return "For payment issues, please ensure your account is verified. 💳";

        case lower.includes('delivery'):
            return "Delivery typically takes 3-5 business days depending on location. 🚚";

        case lower.includes('refund'):
            return "Refund requests are processed within 5-7 business days. 🔄";

        case lower.includes('account'):
            return "Account problems? Try resetting your password first. 👤";

        case lower.includes('help'):
            return "Here are the things I can assist you with:<br><br>" +
                   "- <b>'create listing'</b> 📦<br>" +
                   "- <b>'buy'</b> 🛒<br>" +
                   "- <b>'sell'</b> ➕<br>" +
                   "- <b>'payment issues'</b> 💳<br>" +
                   "- <b>'delivery questions'</b> 🚚<br>" +
                   "- <b>'refund'</b> 🔄<br>" +
                   "- <b>'account problem'</b> 👤<br>" +
                   "- <b>'report seller'</b> 🚩<br>" +
                   "- <b>'report buyer'</b> 🚩";

        default:
            return "I'm here to assist! Type 'help' to see what I can do. 😊";
    }
}
