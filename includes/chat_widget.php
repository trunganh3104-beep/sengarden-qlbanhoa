<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user'])) return; 
?>
<style>
.chat-widget-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #fff;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(244, 63, 94, 0.38);
    z-index: 999999;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.chat-widget-btn:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 10px 26px rgba(244, 63, 94, 0.48);
}
.chat-box-container {
    position: fixed;
    bottom: 90px;
    right: 25px;
    width: 340px;
    height: 450px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 16px 36px rgba(0,0,0,0.15);
    display: none;
    flex-direction: column;
    z-index: 999999;
    overflow: hidden;
    border: 1px solid rgba(254, 205, 211, 0.6);
}
.chat-header {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #fff;
    padding: 14px 18px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.chat-messages {
    flex: 1;
    padding: 14px;
    overflow-y: auto;
    background: #fafbfc;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.message {
    max-width: 80%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13.5px;
    line-height: 1.4;
}
.message.khach {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #fff;
    align-self: flex-end;
    border-bottom-right-radius: 2px;
}
.message.admin {
    background: #f1f5f9;
    color: #334155;
    align-self: flex-start;
    border-bottom-left-radius: 2px;
}
.chat-input-area {
    display: flex;
    padding: 10px 14px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
}
.chat-input-area input {
    flex: 1;
    padding: 9px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    outline: none;
    font-size: 13px;
}
.chat-input-area button {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    color: #fff;
    border: none;
    padding: 0 16px;
    margin-left: 8px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s ease;
}
</style>

<div class="chat-widget-btn" id="chatToggleBtn" title="Hỗ trợ trực tuyến">
    💬
</div>

<div class="chat-box-container" id="chatBox">
    <div class="chat-header">
        <span>Hỗ trợ khách hàng Sen Garden</span>
        <span id="chatCloseBtn" style="cursor:pointer; font-size: 18px;">&times;</span>
    </div>
    <div class="chat-messages" id="chatMessages">
        <div class="message admin">Chào bạn! Sen Garden có thể hỗ trợ gì cho đơn hàng hoặc sản phẩm của bạn?</div>
    </div>
    <form class="chat-input-area" id="chatForm">
        <input type="text" id="chatInputContent" placeholder="Nhập tin nhắn..." autocomplete="off" required>
        <button type="submit">Gửi</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('chatToggleBtn');
    const chatBox = document.getElementById('chatBox');
    const closeBtn = document.getElementById('chatCloseBtn');
    const chatForm = document.getElementById('chatForm');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInputContent');

    toggleBtn.addEventListener('click', () => {
        chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
        scrollToBottom();
        loadMessages();
    });

    closeBtn.addEventListener('click', () => {
        chatBox.style.display = 'none';
    });

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function loadMessages() {
        fetch('ajax_cskh.php?action=get_messages')
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    chatMessages.innerHTML = data.map(m => `
                        <div class="message ${m.nguoi_gui}">${escapeHtml(m.noi_dung)}</div>
                    `).join('');
                    scrollToBottom();
                }
            });
    }

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (!text) return;

        const formData = new FormData();
        formData.append('noi_dung', text);

        fetch('ajax_cskh.php?action=send_message', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                chatInput.value = '';
                loadMessages();
            }
        });
    });

    setInterval(() => {
        if (chatBox.style.display === 'flex') {
            loadMessages();
        }
    }, 4000);

    function escapeHtml(text) {
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }
});
</script>