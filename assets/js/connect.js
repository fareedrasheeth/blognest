// assets/js/connect.js - BlogNest Connect Live Messaging with Framer Motion & Audio Effects

document.addEventListener('DOMContentLoaded', () => {
    // Inject BlogNest Connect Chat Drawer & Floating Trigger into page
    injectConnectUI();

    const connectFab     = document.getElementById('connectFab');
    const connectDrawer  = document.getElementById('connectDrawer');
    const closeDrawerBtn = document.getElementById('closeConnectDrawer');
    const soundToggleBtn = document.getElementById('soundToggleBtn');
    const messageForm    = document.getElementById('connectMessageForm');
    const messageInput   = document.getElementById('connectMessageInput');
    const chatContainer  = document.getElementById('connectChatContainer');
    const unreadBadge    = document.getElementById('connectUnreadBadge');

    let isSoundMuted = localStorage.getItem('blognest_sound_muted') === 'true';
    let lastMessageId = 0;
    let isDrawerOpen  = false;
    let pollInterval  = null;
    let audioCtx      = null;

    // Update sound toggle UI state
    updateSoundButtonUI();

    // Sound toggle listener
    if (soundToggleBtn) {
        soundToggleBtn.addEventListener('click', () => {
            isSoundMuted = !isSoundMuted;
            localStorage.setItem('blognest_sound_muted', isSoundMuted);
            updateSoundButtonUI();
            if (!isSoundMuted) {
                playSendSound(); // Play test tone on unmute
            }
        });
    }

    function updateSoundButtonUI() {
        if (soundToggleBtn) {
            soundToggleBtn.innerHTML = isSoundMuted ? '🔇 Muted' : '🔊 Sound On';
            soundToggleBtn.classList.toggle('muted', isSoundMuted);
        }
    }

    // Lazy initialization of Web Audio Context
    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) {
                audioCtx = new AudioContextClass();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    /**
     * Synthesize Message Sent Sound (Soft Pop / Whoosh)
     */
    function playSendSound() {
        if (isSoundMuted) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            const now = ctx.currentTime;
            
            // Frequency sweep up (pop effect)
            osc.frequency.setValueAtTime(400, now);
            osc.frequency.exponentialRampToValueAtTime(800, now + 0.08);

            // Fast decay gain envelope
            gain.gain.setValueAtTime(0.3, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.09);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now);
            osc.stop(now + 0.1);
        } catch (e) {
            console.warn('Audio playback error:', e);
        }
    }

    /**
     * Synthesize Message Received Sound (Gentle Glass Chime / Ding)
     */
    function playReceiveSound() {
        if (isSoundMuted) return;
        try {
            const ctx = getAudioContext();
            if (!ctx) return;

            const now = ctx.currentTime;

            // Two-tone glass chime (880Hz -> 1320Hz)
            [880, 1320].forEach((freq, index) => {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = 'sine';
                const startTime = now + (index * 0.06);

                osc.frequency.setValueAtTime(freq, startTime);
                gain.gain.setValueAtTime(0.25, startTime);
                gain.gain.exponentialRampToValueAtTime(0.001, startTime + 0.25);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(startTime);
                osc.stop(startTime + 0.26);
            });
        } catch (e) {
            console.warn('Audio playback error:', e);
        }
    }

    // Toggle Chat Drawer with Framer Motion spring physics
    if (connectFab) {
        connectFab.addEventListener('click', () => {
            isDrawerOpen = !isDrawerOpen;
            if (isDrawerOpen) {
                connectDrawer.classList.add('open');
                unreadBadge.style.display = 'none';
                unreadBadge.textContent = '0';
                getAudioContext(); // Enable audio context on gesture
                fetchMessages();
            } else {
                connectDrawer.classList.remove('open');
            }
        });
    }

    if (closeDrawerBtn) {
        closeDrawerBtn.addEventListener('click', () => {
            isDrawerOpen = false;
            connectDrawer.classList.remove('open');
        });
    }

    // Submit Message Handler
    if (messageForm) {
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (!text) return;

            messageInput.value = '';

            // Play instant Framer Motion send sound
            playSendSound();

            try {
                const response = await fetch('api/messages/index.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: text })
                });

                const data = await response.json();
                if (data.success && data.data?.message) {
                    appendMessage(data.data.message, true);
                    lastMessageId = Math.max(lastMessageId, data.data.message.id);
                } else {
                    if (data.message && data.message.includes('Unauthorized')) {
                        alert('Please log in to participate in BlogNest Connect chat.');
                        window.location.href = 'login.php';
                    }
                }
            } catch (err) {
                console.error('Failed to send message:', err);
            }
        });
    }

    /**
     * Fetch recent messages from API
     */
    async function fetchMessages() {
        try {
            const url = lastMessageId > 0 
                ? `api/messages/index.php?since_id=${lastMessageId}` 
                : 'api/messages/index.php';

            const response = await fetch(url);
            const data     = await response.json();

            if (data.success && Array.isArray(data.data.messages)) {
                let hasNewIncoming = false;

                data.data.messages.forEach(msg => {
                    if (msg.id > lastMessageId) {
                        appendMessage(msg, false);
                        lastMessageId = msg.id;

                        if (!msg.is_self) {
                            hasNewIncoming = true;
                        }
                    }
                });

                if (hasNewIncoming) {
                    playReceiveSound();

                    if (!isDrawerOpen) {
                        unreadBadge.style.display = 'flex';
                        const count = parseInt(unreadBadge.textContent || '0') + 1;
                        unreadBadge.textContent = count > 9 ? '9+' : count;
                        unreadBadge.classList.add('pulse-anim');
                    }
                }
            }
        } catch (err) {
            console.error('Error fetching messages:', err);
        }
    }

    /**
     * Append message item to container with Framer Motion spring entrance
     */
    function appendMessage(msg, isNewSelfSend) {
        if (!chatContainer) return;

        // Check if message already rendered
        if (document.getElementById(`msg-${msg.id}`)) return;

        const msgDiv = document.createElement('div');
        msgDiv.id = `msg-${msg.id}`;
        msgDiv.className = `chat-bubble-wrap ${msg.is_self ? 'self' : 'other'}`;

        // Framer Motion entrance animation class
        msgDiv.classList.add(msg.is_self ? 'motion-spring-right' : 'motion-spring-left');

        msgDiv.innerHTML = `
            <div class="chat-meta">${msg.is_self ? 'You' : msg.sender_name} • ${msg.created_at_formatted}</div>
            <div class="chat-bubble">${escapeHtml(msg.content)}</div>
        `;

        chatContainer.appendChild(msgDiv);

        // Smooth scroll to bottom
        chatContainer.scrollTo({
            top: chatContainer.scrollHeight,
            behavior: 'smooth'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Start background polling every 3 seconds for real-time receive notifications
    pollInterval = setInterval(fetchMessages, 3000);
});

/**
 * Inject BlogNest Connect Chat Component DOM into page
 */
function injectConnectUI() {
    if (document.getElementById('connectDrawer')) return;

    const html = `
        <!-- Floating Connect Trigger Button -->
        <button id="connectFab" class="connect-fab" title="BlogNest Connect Community Chat">
            <span class="fab-icon">💬</span>
            <span class="fab-text">Connect</span>
            <span id="connectUnreadBadge" class="unread-badge" style="display: none;">0</span>
        </button>

        <!-- Framer Motion Animated Chat Drawer -->
        <div id="connectDrawer" class="connect-drawer">
            <div class="connect-header">
                <div class="connect-title">
                    <span>💬 BlogNest Connect</span>
                    <span class="connect-online-dot" title="Live Community Chat"></span>
                </div>
                <div class="connect-header-actions">
                    <button type="button" id="soundToggleBtn" class="sound-toggle-btn">🔊 Sound On</button>
                    <button type="button" id="closeConnectDrawer" class="close-drawer-btn" title="Close Chat">✕</button>
                </div>
            </div>

            <div id="connectChatContainer" class="connect-body">
                <div class="connect-welcome-hint">
                    ✨ Welcome to <strong>BlogNest Connect</strong>! Share quick thoughts, discuss articles, or chat with community writers in real-time.
                </div>
            </div>

            <form id="connectMessageForm" class="connect-footer">
                <input type="text" id="connectMessageInput" class="connect-input" placeholder="Type a message..." maxlength="1000" autocomplete="off" required>
                <button type="submit" class="btn btn-primary btn-sm send-btn" title="Send Message">
                    <span>Send</span>
                    <span class="send-icon">🚀</span>
                </button>
            </form>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', html);
}
