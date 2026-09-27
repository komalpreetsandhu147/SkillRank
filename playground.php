<?php
require_once __DIR__ . '/auth.php';
require_login('student');

$pageTitle = 'Interactive Code Sandbox';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Hands-On Development Lab</div>
<h1>Interactive Code Sandbox</h1>
<p>Experiment with live frontend components, test algorithmic logic, and verify code snippets in real-time.</p>
</div>
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
<button type="button" class="button button-quiet" onclick="loadTemplate('counter')">Template: State Counter</button>
<button type="button" class="button button-quiet" onclick="loadTemplate('card')">Template: Card Component</button>
<button type="button" class="button button-primary" onclick="runSandboxCode()">▶ Run Code</button>
</div>
</div>

<!-- Quick Template Selector & Controls -->
<div class="panel" style="margin-bottom:20px;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
<span style="font-weight:700;font-size:0.85rem">Presets:</span>
<button type="button" class="pill" onclick="loadTemplate('counter')">JS DOM Counter</button>
<button type="button" class="pill" onclick="loadTemplate('card')">CSS Glassmorphism</button>
<button type="button" class="pill" onclick="loadTemplate('sql')">SQL Query Mock</button>
<button type="button" class="pill" onclick="loadTemplate('algo')">Array Reduce / Filter</button>
</div>

<div style="display:flex;align-items:center;gap:10px">
<button type="button" class="button button-quiet" onclick="clearEditor()" style="padding:6px 12px;font-size:0.8rem">Clear</button>
<button type="button" class="button button-quiet" onclick="askAiToReview()" style="padding:6px 14px;font-size:0.8rem;border-color:var(--teal)">✦ AI Code Review</button>
</div>
</div>

<!-- Two-Column Sandbox: Editor + Live Preview -->
<div class="sandbox-grid">
<!-- Left: Code Editor -->
<div class="panel" style="display:flex;flex-direction:column;padding:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="display:flex;align-items:center;gap:8px">
<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e06c75"></span>
<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e5c07b"></span>
<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#98c379"></span>
<strong style="margin-left:8px;font-size:0.85rem">index.html (HTML + CSS + JS)</strong>
</div>
<small class="muted">Live sandbox</small>
</div>

<textarea id="codeEditor" class="code-editor-area" spellcheck="false" placeholder="Write HTML, CSS, and JS here..."></textarea>

<div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center">
<small class="muted">Press <strong>Run Code</strong> or edit directly to render.</small>
<button type="button" class="button button-primary" onclick="runSandboxCode()" style="padding:8px 18px;font-size:0.85rem">
▶ Execute Preview
</button>
</div>
</div>

<!-- Right: Live Output & Virtual Console -->
<div class="panel" style="display:flex;flex-direction:column;padding:16px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="display:flex;gap:6px">
<button type="button" id="tabPreviewBtn" class="pill lime" onclick="switchRightTab('preview')" style="cursor:pointer">Live Preview Frame</button>
<button type="button" id="tabConsoleBtn" class="pill" onclick="switchRightTab('console')" style="cursor:pointer">Console Log (<span id="consoleCount">0</span>)</button>
</div>
<small class="muted" id="renderStatus">Idle</small>
</div>

<!-- Preview Frame Container -->
<div id="previewContainer" style="flex:1">
<iframe id="previewIframe" class="preview-iframe" sandbox="allow-scripts allow-modals"></iframe>
</div>

<!-- Console Log Container -->
<div id="consoleContainer" style="display:none;flex:1;background:#0d1117;border:1px solid #30363d;border-radius:10px;padding:14px;color:#e6edf3;font-family:'Consolas',monospace;font-size:0.85rem;overflow-y:auto;height:400px">
<div id="consoleOutput" style="display:flex;flex-direction:column;gap:6px">
<div style="color:#8b949e">// Console output stream will appear here...</div>
</div>
</div>

<div style="margin-top:12px;display:flex;justify-content:space-between;align-items:center">
<small class="muted">Isolated browser sandbox environment</small>
<button type="button" class="button button-quiet" onclick="clearConsole()" style="padding:6px 12px;font-size:0.8rem">Clear Console</button>
</div>
</div>
</div>

<script>
const templates = {
    counter: `<!DOCTYPE html>
<html>
<head>
<style>
  body { font-family: system-ui, sans-serif; padding: 24px; text-align: center; background: #f8fafc; }
  .counter-box { background: #fff; padding: 30px; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); max-width: 280px; margin: 0 auto; }
  .display { font-size: 3rem; font-weight: bold; color: #1e293b; margin: 15px 0; }
  button { padding: 10px 18px; font-weight: bold; border-radius: 8px; border: none; cursor: pointer; margin: 4px; }
  .btn-inc { background: #176b78; color: #fff; }
  .btn-dec { background: #e2e8f0; color: #334155; }
</style>
</head>
<body>
  <div class="counter-box">
    <h3>Live Counter</h3>
    <div class="display" id="count">0</div>
    <button class="btn-dec" onclick="adjust(-1)">-1</button>
    <button class="btn-inc" onclick="adjust(1)">+1</button>
  </div>
  <script>
    let n = 0;
    function adjust(delta) {
      n += delta;
      document.getElementById('count').textContent = n;
      console.log('Counter updated to: ' + n);
    }
  <\\/script>
</body>
</html>`,

    card: `<!DOCTYPE html>
<html>
<head>
<style>
  body { 
    margin: 0; 
    min-height: 100vh; 
    display: grid; 
    place-items: center; 
    background: linear-gradient(135deg, #102d46, #176b78); 
    font-family: system-ui, sans-serif; 
  }
  .glass-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 18px;
    padding: 32px;
    color: #fff;
    max-width: 320px;
    box-shadow: 0 16px 36px rgba(0,0,0,0.2);
  }
  .tag { background: #b9e7f2; color: #102d46; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; }
  h2 { margin: 14px 0 8px; font-size: 1.4rem; }
  p { font-size: 0.9rem; line-height: 1.5; color: #e2e8f0; }
</style>
</head>
<body>
  <div class="glass-card">
    <span class="tag">TECH FEST 2026</span>
    <h2>SkillRank Candidate</h2>
    <p>Demonstrating modern CSS glassmorphism, responsive grid architecture, and high UI polish.</p>
  </div>
</body>
</html>`,

    sql: `<!DOCTYPE html>
<html>
<head>
<style>
  body { font-family: system-ui, sans-serif; padding: 20px; background: #fff; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.85rem; }
  th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
  th { background: #f1f5f9; color: #334155; }
  .badge { background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
</style>
</head>
<body>
  <h4>SQL Result Mock: <code>SELECT * FROM students WHERE accuracy >= 80</code></h4>
  <table>
    <thead><tr><th>ID</th><th>Student</th><th>Course</th><th>Accuracy</th><th>Level</th></tr></thead>
    <tbody id="rows">
      <tr><td>1</td><td>Aarav Sharma</td><td>BCA Computer Science</td><td><span class="badge">92%</span></td><td>Expert</td></tr>
      <tr><td>2</td><td>Meera Patel</td><td>B.Tech IT</td><td><span class="badge">85%</span></td><td>Advanced</td></tr>
    </tbody>
  </table>
  <script>
    console.log('Query executed: 2 matching records returned.');
  <\\/script>
</body>
</html>`,

    algo: `<!DOCTYPE html>
<html>
<head>
<style>
  body { font-family: monospace; padding: 20px; background: #0f172a; color: #38bdf8; font-size: 0.9rem; }
  .box { background: #1e293b; padding: 15px; border-radius: 8px; color: #f8fafc; margin-top: 10px; }
</style>
</head>
<body>
  <h3>JavaScript Array Algorithm Demonstration</h3>
  <div class="box" id="output">Calculating metrics...</div>
  <script>
    const assessments = [
      { subject: 'PHP', score: 85 },
      { subject: 'JavaScript', score: 95 },
      { subject: 'DBMS', score: 78 },
      { subject: 'HTML & CSS', score: 90 }
    ];

    const mean = assessments.reduce((acc, curr) => acc + curr.score, 0) / assessments.length;
    const topTracks = assessments.filter(a => a.score >= 90).map(a => a.subject);

    const log = 'Calculated Mean: ' + mean.toFixed(1) + '% | Top Tracks: ' + topTracks.join(', ');
    document.getElementById('output').innerHTML = '<strong>' + log + '</strong>';
    console.log(log);
  <\\/script>
</body>
</html>`
};

let consoleLogs = [];

function loadTemplate(key) {
    if (templates[key]) {
        document.getElementById('codeEditor').value = templates[key];
        runSandboxCode();
    }
}

function clearEditor() {
    document.getElementById('codeEditor').value = '';
    runSandboxCode();
}

function runSandboxCode() {
    const rawCode = document.getElementById('codeEditor').value;
    const iframe = document.getElementById('previewIframe');
    const status = document.getElementById('renderStatus');

    // Intercept console.log from iframe to display in Sandbox console
    const interceptScript = `
    <script>
      (function(){
        var oldLog = console.log;
        console.log = function(...args) {
          window.parent.postMessage({ type: 'sr_sandbox_log', message: args.join(' ') }, '*');
          oldLog.apply(console, args);
        };
      })();
    <\\/script>
    `;

    const codeToInject = rawCode.replace('<head>', '<head>' + interceptScript);

    iframe.srcdoc = codeToInject || '<html><body style="font-family:sans-serif;padding:30px;color:#888;text-align:center">Output preview will appear here...</body></html>';
    status.textContent = 'Rendered ' + new Date().toLocaleTimeString();
}

window.addEventListener('message', (e) => {
    if (e.data && e.data.type === 'sr_sandbox_log') {
        addConsoleLog(e.data.message);
    }
});

function addConsoleLog(msg) {
    consoleLogs.push({ time: new Date().toLocaleTimeString(), msg });
    document.getElementById('consoleCount').textContent = consoleLogs.length;

    const out = document.getElementById('consoleOutput');
    const row = document.createElement('div');
    row.style.borderBottom = '1px solid #1f2937';
    row.style.paddingBottom = '4px';
    row.innerHTML = `<span style="color:#64748b">[${new Date().toLocaleTimeString()}]</span> <span style="color:#a5f3fc">${escapeHtml(msg)}</span>`;
    out.appendChild(row);
    out.scrollTop = out.scrollHeight;
}

function clearConsole() {
    consoleLogs = [];
    document.getElementById('consoleCount').textContent = '0';
    document.getElementById('consoleOutput').innerHTML = '<div style="color:#8b949e">// Console cleared.</div>';
}

function switchRightTab(tab) {
    const prevCont = document.getElementById('previewContainer');
    const consCont = document.getElementById('consoleContainer');
    const prevBtn = document.getElementById('tabPreviewBtn');
    const consBtn = document.getElementById('tabConsoleBtn');

    if (tab === 'preview') {
        prevCont.style.display = 'block';
        consCont.style.display = 'none';
        prevBtn.classList.add('lime');
        consBtn.classList.remove('lime');
    } else {
        prevCont.style.display = 'none';
        consCont.style.display = 'flex';
        consBtn.classList.add('lime');
        prevBtn.classList.remove('lime');
    }
}

function askAiToReview() {
    const code = document.getElementById('codeEditor').value.trim();
    if (!code) {
        alert('Please enter or load some code into the sandbox first.');
        return;
    }
    // Open AI Chat Drawer if present and populate prompt
    if (typeof openAiChatWithPrompt === 'function') {
        openAiChatWithPrompt("Can you review this code snippet and suggest best practices or optimizations?\\n\\n" + code.substring(0, 300));
    } else {
        alert("AI Assistant ready. You can ask questions in the bottom-right AI Mentor drawer!");
    }
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Initial template load
loadTemplate('counter');
</script>

<?php include 'includes/footer.php'; ?>
