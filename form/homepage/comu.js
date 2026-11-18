// Load posts when page loads

document.addEventListener('DOMContentLoaded', function () {
  loadPosts();
  setupTabs();
  loadLatestListings();
});

// Submit new post to backend
function submitPost() {
  const text = document.getElementById('postText').value.trim();
  const fileInput = document.getElementById('imageUpload');
  const file = fileInput.files[0];

  if (!text && !file) {
    alert("Please write something or upload an image!");
    return;
  }

  const formData = new FormData();
  formData.append('content', text);
  if (file) {
    formData.append('image', file);
  }

  fetch('php/insert_post.php', {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        loadPosts();
        document.getElementById('postText').value = '';
        document.getElementById('imageUpload').value = '';
      } else {
        alert("Post failed: " + (data.message || "Unknown error"));
      }
    })
    .catch(error => {
      console.error("Post error:", error);
      alert("Post failed.");
    });
}

// Load posts from backend
function loadPosts() {
  fetch('php/fetch_posts.php')
    .then(res => res.json())
    .then(posts => {
      const postFeed = document.getElementById('postFeed');
      postFeed.innerHTML = '';

      posts.forEach(post => {
        const postCard = document.createElement('div');
        postCard.className = 'post-card';

        let imageHTML = '';
        if (post.image_url) {
          imageHTML = `<img src="${post.image_url}" style="max-width:100%; border-radius:8px; margin-top:10px;">`;
        }
        

        let postedBy = (post.author_id == CURRENT_USER_ID) ? "You" : post.author;

        postCard.innerHTML = `
          <p>${post.content}</p>
          ${imageHTML}
          <div class="post-footer">
            <span class="author">Posted by: ${postedBy}</span>
            <span class="timestamp">${new Date(post.created_at).toLocaleString()}</span>
          </div>
          <div class="reply-section">
            <textarea class="reply-input" placeholder="Write a reply..."></textarea>
            <button onclick="submitReply(${post.id}, this)">Reply</button>
            <div class="replies" id="replies-${post.id}"></div>
          </div>
        `;

        postFeed.prepend(postCard);
        loadReplies(post.id);
      });
    });
}

// Submit reply
function submitReply(postId, button) {
  const replyInput = button.previousElementSibling;
  const replyText = replyInput.value.trim();
  if (!replyText) return;

  // Store in localStorage (or a temp object) instead of database
  const storedReplies = JSON.parse(localStorage.getItem(`replies-${postId}`)) || [];
  storedReplies.push({ username: "You", reply_content: replyText });
  localStorage.setItem(`replies-${postId}`, JSON.stringify(storedReplies));

  replyInput.value = '';
  loadReplies(postId);
}

// Load replies per post
function loadReplies(postId) {
  const replies = JSON.parse(localStorage.getItem(`replies-${postId}`)) || [];
  const repliesDiv = document.getElementById(`replies-${postId}`);
  repliesDiv.innerHTML = '';

  replies.forEach(reply => {
    const div = document.createElement('div');
    div.className = 'reply-item';
    div.innerHTML = `
      <div class="reply-content">
        <strong>${reply.username}</strong>
        <p>${reply.reply_content}</p>
      </div>
    `;
    repliesDiv.appendChild(div);
  });
}

// Tab switching (Latest / Updates)
function setupTabs() {
  const tabs = document.querySelectorAll('.right-panel .tab');
  const miniFeed = document.querySelector('.mini-feed');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      if (this.textContent.trim() === 'Latest') {
        loadLatestListings();
      } else {
        miniFeed.innerHTML = `
          <div class="mini-post"><p><strong>System:</strong> New feature added! 🎉</p></div>
          <div class="mini-post"><p><strong>Update:</strong> Bug fixes applied 🛠️</p></div>
        `;
      }
    });
  });
}

function loadLatestListings() {
  fetch('php/fetch_latest_items.php')
    .then(res => res.json())
    .then(items => {
      const miniFeed = document.querySelector('.mini-feed');
      miniFeed.innerHTML = '';

      if (items.length === 0) {
        miniFeed.innerHTML = '<div class="mini-post">No recent listings.</div>';
        return;
      }

      items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'listing-card';

        const logoPath = 'pics/hirabayLogo.png';
        const itemImg = item.image ? `uploads/${item.image}` : 'pics/hirabayLogo.png';
        const title = item.title || 'Untitled';
        const category = item.category || 'Uncategorized';
        const price = item.price ? `₱${parseFloat(item.price).toFixed(2)}` : 'N/A';
        const seller = item.seller || 'Unknown Seller';
        const itemId = item.id;

        div.innerHTML = `
          <a href="product-description.php?id=${itemId}" style="text-decoration: none; color: inherit;">
            <div class="listing-header">
              <img src="${logoPath}" class="listing-avatar" alt="Logo">
              <span class="seller-name">${seller}</span>
            </div>
            <div class="listing-body">
              <img src="${itemImg}" class="listing-image" alt="Item Image">
              <div class="listing-content">
                <h4>${title}</h4>
                <p><strong>Category:</strong> ${category}</p>
                <p><strong>Price:</strong> ${price}</p>
              </div>
            </div>
          </a>
        `;

        miniFeed.appendChild(div);
      });
    })
    .catch(err => {
      console.error("Error fetching listings:", err);
      document.querySelector('.mini-feed').innerHTML = `
        <div class="mini-post">⚠️ Failed to load latest listings.</div>
      `;
    });
}

