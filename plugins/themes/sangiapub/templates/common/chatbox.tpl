<div id="wizdam-chat-widget" class="wizdam-chat-closed">
    <div class="wizdam-chat-header" onclick="WizdamChat.toggle()">
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="font-size: 16px;">🤖</span> 
            <span style="font-weight:bold; font-size:14px;">Wizdam Assistant</span>
        </div>
        <span id="wizdam-toggle-icon">▲</span>
        <div id="wizdam-bounce-indicator"></div> 
    </div>

    <div class="wizdam-chat-body" id="wizdam-chat-body">
        </div>

    <div id="wizdam-typing-container" style="display:none;">
        <div class="wizdam-message bot">
            <div class="wizdam-typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    </div>

    <div class="wizdam-chat-input">
        <input type="text" id="wizdam-input" placeholder="Type your message..." autocomplete="off">
        <button onclick="WizdamChat.send()">➤</button>
    </div>
</div>

<style>
{literal}
    #wizdam-chat-widget {
        position: fixed; bottom: 0; right: 20px; width: 340px;
        background: #fff; border-radius: 12px 12px 0 0;
        box-shadow: 0 -5px 25px rgba(0,0,0,0.15); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        z-index: 99999; transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .wizdam-chat-closed { transform: translateY(calc(100% - 48px)); }
    .wizdam-chat-open { transform: translateY(0); }
    
    .wizdam-chat-header {
        position: relative; /* Untuk penempatan bounce indicator */
        background: #004e82; color: #fff; padding: 12px 15px; height: 48px;
        cursor: pointer; display: flex; justify-content: space-between; align-items: center;
        border-radius: 10px 10px 0 0; user-select: none;
    }
    
    .wizdam-chat-body {
        height: 350px; padding: 15px; overflow-y: auto; background: #f9f9f9;
        display: flex; flex-direction: column; gap: 12px; scroll-behavior: smooth;
    }
    
    /* --- FLUID TYPING DOTS CSS BARU --- */
    .chatbot-typing {
        display: none; /* Default disembunyikan oleh JS */
        align-items: center;
        gap: 16px;
        padding: 8px 12px; /* Disesuaikan agar pas di dalam wizdam-message */
        background: white;
        border-radius: 18px;
        border-bottom-left-radius: 6px;
        max-width: 80px;
        height: 32px; /* Memberi ruang untuk bounce */
    }
    
    .wizdam-typing-dots {
        display: flex;
        gap: 5px;
    }

    .typing-dot {
        width: 6px;
        height: 6px;
        background: #999;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typing {
        0%, 60%, 100% { transform: translateY(0); }
        30% { transform: translateY(-9px); }
    }

    /* --- BOUNCE INDICATOR CSS --- */
    #wizdam-bounce-indicator {
        position: absolute;
        top: 5px;
        right: 10px;
        width: 12px;
        height: 12px;
        background-color: #f00;
        border-radius: 50%;
        animation: wizdam-bounce 1s infinite;
        z-index: 100000;
        display: none; 
    }

    @keyframes wizdam-bounce {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }
    
    .wizdam-message {
        padding: 10px 14px; border-radius: 12px; max-width: 85%; line-height: 1.5;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .wizdam-message.bot { background: #fff; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid rgb(197, 224, 244); box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #c5e0f4;background-color: rgb(245, 245, 245);}
    .wizdam-message.user { background: #004e82; color: #fff; align-self: flex-end; border-bottom-right-radius: 2px; }
    
    /* HTML formatting inside chat */
    .wizdam-message h4 { margin: 0 0 5px 0; font-size: 14px; font-weight: 700; }
    .wizdam-message ul { padding-left: 20px; margin: 5px 0; }
    
    .wizdam-chat-input {
        padding: 10px; border-top: 1px solid #eee; display: flex; background: #fff; align-items: center; gap: 10px;
    }
    .wizdam-chat-input input {
        flex: 1; border: 1px solid #ddd; padding: 10px 16px; border-radius: 20px; outline: none; transition: border 0.2s;
    }
    .wizdam-chat-input input:focus { border-color: #004e82; }
    .wizdam-chat-input button {
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: #004e82;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: auto;
    }
{/literal}
</style>

<script>
{literal}
const WizdamChat = (function() {
    const DOM = {
        widget: document.getElementById('wizdam-chat-widget'),
        body: document.getElementById('wizdam-chat-body'),
        input: document.getElementById('wizdam-input'),
        typingContainer: document.getElementById('wizdam-typing-container'),
        icon: document.getElementById('wizdam-toggle-icon'),
        // FIX: DOM element baru untuk bounce
        bounceIndicator: document.getElementById('wizdam-bounce-indicator') 
    };
    let isInitialized = false;
    // FIX: Cek status dari Local Storage
    let isChatEverOpened = localStorage.getItem('wizdamChatOpened') === 'true'; 

    function scrollToBottom() {
        DOM.body.scrollTop = DOM.body.scrollHeight;
    }
    
    // Fungsi untuk mengontrol bounce
    function showBounce(show) {
        if (!isChatEverOpened) {
            DOM.bounceIndicator.style.display = show ? 'block' : 'none';
        } else {
            DOM.bounceIndicator.style.display = 'none';
        }
    }

    // Fungsi showTyping (Typing Dots)
    function showTyping(show) {
        if (show) {
            DOM.body.appendChild(DOM.typingContainer);
            DOM.typingContainer.style.display = 'flex';
        } else {
            if (DOM.typingContainer.parentNode === DOM.body) {
                DOM.typingContainer.style.display = 'none';
                document.body.appendChild(DOM.typingContainer); 
            }
        }
        scrollToBottom();
    }

    function appendUserMessage(html) {
        const div = document.createElement('div');
        div.className = 'wizdam-message user';
        div.innerHTML = html;
        DOM.body.appendChild(div);
        scrollToBottom();
    }
    
    // FUNGSI BARU UNTUK MENAMPILKAN SATU PESAN BOT SEKETIKA
    function appendBotMessage(html) {
        const div = document.createElement('div');
        div.className = 'wizdam-message bot';
        div.innerHTML = html;
        DOM.body.appendChild(div);
        scrollToBottom();
    }
    
    // FUNGSI BARU: Delay antar pesan Bot
    function delay(ms) {
        return new Promise(r => setTimeout(r, ms));
    }
    
    
    // FUNGSI typeMessage (Animasi Ketik Per Karakter) TELAH DIHAPUS.
    // Teks Bot akan muncul instan setelah loading.
    async function fetchReply(query, isInit = false) {
        showTyping(true);
        
        const formData = new FormData();
        formData.append('q', query);
        formData.append('context', window.location.href);

        try {
            // ... (Kode fetch/AJAX tetap sama, targetUrl) ...
            const currentUrl = window.location.href;
            const baseUrl = currentUrl.substring(0, currentUrl.indexOf('/index.php'));
            const siteWideSlug = 'index'; 
            const targetUrl = baseUrl + '/index.php/' + siteWideSlug + '/help/chat';
            
            const response = await fetch(targetUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Network error or 404');

            const data = await response.json();
            
            // Tambahkan delay minimal 800ms untuk latensi server
            await delay(800); 
            showTyping(false); // Matikan Typing Dots
            
            // --- LOOP BARU: Mengirim setiap pesan dari array ---
            if (data.replies && Array.isArray(data.replies)) {
                
                for (let i = 0; i < data.replies.length; i++) {
                    // Tampilkan pesan
                    appendBotMessage(data.replies[i]);
                    
                    // Delay 300ms antar gelembung pesan untuk efek percakapan alami
                    if (i < data.replies.length - 1) {
                         await delay(300);
                    }
                }
            } else {
                // Fallback jika API mengembalikan respons tunggal atau error
                appendBotMessage("Error: Format respons server tidak valid.");
            }

        } catch (error) {
            console.error('Wizdam Chat Error:', error);
            await delay(500); 
            showTyping(false);
            
            const errorMessage = "⚠️ Kesalahan koneksi. Pastikan rute '/index/help/chat' aktif di server, dan tidak ada Fatal Error PHP.";
            appendBotMessage(errorMessage);
        }
    }

    // Public Methods
    return {
        toggle: function() {
            const isClosed = DOM.widget.classList.contains('wizdam-chat-closed');
            
            if (isClosed) {
                DOM.widget.classList.remove('wizdam-chat-closed');
                DOM.widget.classList.add('wizdam-chat-open');
                DOM.icon.innerText = '▼';
                DOM.input.focus();
                
                // Matikan bounce dan simpan status
                if (!isChatEverOpened) {
                    isChatEverOpened = true;
                    localStorage.setItem('wizdamChatOpened', 'true');
                    showBounce(false); 
                }

                if (!isInitialized) {
                    // Panggil fetchReply untuk sapaan awal
                    setTimeout(() => fetchReply('', true), 300);
                    isInitialized = true;
                }
            } else {
                DOM.widget.classList.add('wizdam-chat-closed');
                DOM.widget.classList.remove('wizdam-chat-open');
                DOM.icon.innerText = '▲';
            }
        },

        send: function() {
            const text = DOM.input.value.trim();
            if (!text) return;

            appendUserMessage(text.replace(/</g, "&lt;"));
            DOM.input.value = '';
            
            fetchReply(text);
        },

        init: function() {
            DOM.input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') WizdamChat.send();
            });
            document.body.appendChild(DOM.typingContainer);

            // Awal: Tampilkan bounce jika belum pernah dibuka
            showBounce(true); 
        }
    };
})();

document.addEventListener('DOMContentLoaded', WizdamChat.init);
{/literal}
</script>