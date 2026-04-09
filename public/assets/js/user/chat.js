document.addEventListener('DOMContentLoaded', function() {
    const chatToggleBtn = document.getElementById('chatToggleBtn');
    const chatPopup = document.getElementById('chatPopup');
    const chatCloseBtn = document.getElementById('chatCloseBtn');
    const chatTabs = document.querySelectorAll('.chat-tab');
    const chatTabContents = document.querySelectorAll('.chat-tab-content');

    // Toggle chat popup
    if (chatToggleBtn) {
        chatToggleBtn.addEventListener('click', function() {
            if (chatPopup) {
                chatPopup.classList.toggle('active');
            }
        });
    }

    // Close chat popup
    if (chatCloseBtn) {
        chatCloseBtn.addEventListener('click', function() {
            if (chatPopup) {
                chatPopup.classList.remove('active');
            }
        });
    }

    // Close popup when clicking outside
    document.addEventListener('click', function(event) {
        if (chatPopup && chatToggleBtn && 
            !chatPopup.contains(event.target) && 
            !chatToggleBtn.contains(event.target)) {
            chatPopup.classList.remove('active');
        }
    });

    // Tab functionality
    chatTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
            chatTabs.forEach(t => t.classList.remove('chat-tab--active'));
            chatTabContents.forEach(content => content.classList.remove('chat-tab-content--active'));
            
            // Add active class to clicked tab
            this.classList.add('chat-tab--active');
            
            // Show corresponding content
            if (targetTab === 'direct') {
                const directTab = document.getElementById('directTab');
                if (directTab) directTab.classList.add('chat-tab-content--active');
            } else if (targetTab === 'support') {
                const supportTab = document.getElementById('supportTab');
                if (supportTab) supportTab.classList.add('chat-tab-content--active');
            }
        });
    });

    // Chat item click functionality
    const chatItems = document.querySelectorAll('.chat-item');
    chatItems.forEach(item => {
        item.addEventListener('click', function() {
            // Add clicked effect
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
            
            // Here you can add functionality to open individual chat
            const chatName = this.querySelector('.chat-name');
            if (chatName) {
                console.log('Opening chat with:', chatName.textContent);
            }
        });
    });
});