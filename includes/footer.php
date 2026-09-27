<!-- Shared closing markup, AI Mentor Widget and JavaScript entry point. -->
</main>

<?php if (isset($user) && $user && $user['role'] === 'student'): ?>
<!-- Floating AI Mentor Widget Button -->
<div id="ai-mentor-trigger" class="ai-fab no-print" onclick="toggleAiDrawer()">
<span style="font-size:1.2rem">💬</span>
<strong>AI Mentor</strong>
<span class="ai-fab-pulse"></span>
</div>

<!-- Slide-out AI Mentor Drawer -->
<div id="ai-drawer" class="ai-drawer no-print">
<div class="ai-drawer-header">
<div>
<div class="eyebrow" style="color:var(--lime);font-size:0.68rem">Campus Learning Assistant</div>
<strong style="color:#fff;font-size:1.1rem;display:flex;align-items:center;gap:6px">
✦ SkillRank AI Mentor
</strong>
</div>
<button class="ai-drawer-close" onclick="toggleAiDrawer()">✕</button>
</div>

<!-- Suggested Quick Questions -->
<div class="ai-chips">
<button class="ai-chip" onclick="askAiChip('Explain 3NF Normalization with an example')">📐 DBMS Normalization</button>
<button class="ai-chip" onclick="askAiChip('How do Promises and async/await work in JavaScript?')">⚡ JS Promises</button>
<button class="ai-chip" onclick="askAiChip('What are my biggest skill gaps and how do I fix them?')">🎯 My Skill Gaps</button>
<button class="ai-chip" onclick="askAiChip('How do I prevent SQL injection in PHP with prepared statements?')">🛡️ PHP Security</button>
</div>

<!-- Chat Message Stream -->
<div id="ai-chat-body" class="ai-chat-body">
<div class="ai-msg ai-bot">
Hello <?= e(explode(' ', $user['name'])[0]) ?>! I'm your technical AI Mentor. Ask me any concept from PHP, JavaScript, DBMS, Web Architecture, or request tips to raise your skill level.
</div>
</div>

<!-- Chat Input Box -->
<form id="ai-chat-form" class="ai-drawer-footer" onsubmit="sendAiMessage(event)">
<input type="text" id="ai-input" placeholder="Ask a technical concept or question..." autocomplete="off">
<button type="submit" id="ai-send-btn" class="button button-primary" style="padding:10px 14px">Send ↗</button>
</form>
</div>

<style>
.ai-fab {
    position: fixed;
    right: 25px;
    bottom: 25px;
    background: var(--ink);
    color: #fff;
    padding: 12px 20px;
    border-radius: 30px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(16, 45, 70, 0.25);
    z-index: 999;
    border: 2px solid var(--lime);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.ai-fab:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(16, 45, 70, 0.35);
}
.ai-fab-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--lime);
    box-shadow: 0 0 0 4px rgba(185, 231, 242, 0.4);
    animation: aiPulse 2s infinite;
}
@keyframes aiPulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(185, 231, 242, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(185, 231, 242, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(185, 231, 242, 0); }
}
.ai-drawer {
    position: fixed;
    right: -420px;
    bottom: 0;
    width: 400px;
    height: 560px;
    max-height: 85vh;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px 16px 0 0;
    box-shadow: -10px 0 35px rgba(16, 45, 70, 0.18);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    transition: right 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}
.ai-drawer.open {
    right: 25px;
}
.ai-drawer-header {
    background: var(--navy);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.ai-drawer-close {
    background: transparent;
    border: 0;
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 4px;
}
.ai-chips {
    padding: 10px 14px;
    background: #f8fafc;
    border-bottom: 1px solid var(--line);
    display: flex;
    gap: 6px;
    overflow-x: auto;
    white-space: nowrap;
}
.ai-chip {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 20px;
    padding: 5px 10px;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--teal);
    cursor: pointer;
    flex-shrink: 0;
}
.ai-chip:hover {
    background: var(--lime);
    color: var(--ink);
}
.ai-chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: #fdfdfd;
}
.ai-msg {
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 0.85rem;
    line-height: 1.5;
    max-width: 85%;
    white-space: pre-wrap;
    word-break: break-word;
}
.ai-bot {
    background: #f1f5f9;
    color: var(--ink);
    border-bottom-left-radius: 2px;
    align-self: flex-start;
}
.ai-user {
    background: var(--teal);
    color: #fff;
    border-bottom-right-radius: 2px;
    align-self: flex-end;
}
.ai-drawer-footer {
    padding: 12px;
    border-top: 1px solid var(--line);
    display: flex;
    gap: 8px;
    background: #fff;
}
.ai-drawer-footer input {
    margin: 0;
    padding: 10px 12px;
    font-size: 0.85rem;
}
@media (max-width: 600px) {
    .ai-drawer {
        width: 100%;
        height: 75vh;
        border-radius: 16px 16px 0 0;
    }
    .ai-drawer.open {
        right: 0;
    }
}
</style>

<script>
function toggleAiDrawer() {
    const d = document.getElementById('ai-drawer');
    d.classList.toggle('open');
    if (d.classList.contains('open')) {
        document.getElementById('ai-input').focus();
    }
}

function askAiChip(text) {
    document.getElementById('ai-input').value = text;
    sendAiMessage(new Event('submit'));
}

async function sendAiMessage(e) {
    e.preventDefault();
    const input = document.getElementById('ai-input');
    const msg = input.value.trim();
    if (!msg) return;

    const chatBody = document.getElementById('ai-chat-body');

    // Append user message
    const userDiv = document.createElement('div');
    userDiv.className = 'ai-msg ai-user';
    userDiv.textContent = msg;
    chatBody.appendChild(userDiv);
    input.value = '';

    // Append loading placeholder
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'ai-msg ai-bot';
    loadingDiv.textContent = 'Thinking... ✦';
    chatBody.appendChild(loadingDiv);
    chatBody.scrollTop = chatBody.scrollHeight;

    try {
        const formData = new FormData();
        formData.append('message', msg);
        const res = await fetch('ai_chat.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        loadingDiv.textContent = data.reply || 'Sorry, I could not generate a response right now.';
    } catch (err) {
        loadingDiv.textContent = 'Could not reach AI mentor. Please check server connection.';
    }
    chatBody.scrollTop = chatBody.scrollHeight;
}
</script>
<?php endif; ?>

<script src="assets/app.js"></script>
</body>
</html>
