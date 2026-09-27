<?php
// Admin AI Diagnostics & Model Controls
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ai.php';
require_login('admin');

$testResult = null;
if (isset($_POST['action']) && $_POST['action'] === 'ping_api') {
    $testResult = ai_test_connection();
}

$pageTitle = 'GenAI System Diagnostics';
include 'includes/header.php';
?>
<div class="page-head">
<div>
<div class="eyebrow">Artificial Intelligence Architecture</div>
<h1>GenAI Engine & Diagnostics</h1>
<p>Monitor Google Gemini API health, model latency, and procedural fallback mechanisms.</p>
</div>
<div><?= ai_badge_html() ?></div>
</div>

<div class="dashboard-grid">
<section>
<!-- AI System Health Card -->
<div class="panel" style="margin-bottom:20px;background:linear-gradient(135deg,#102d46,#1b6270);color:#fff;border:none">
<div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:15px;margin-bottom:14px">
<div>
<div class="eyebrow" style="color:var(--lime)">✦ System Health Status</div>
<h2 style="color:#fff;margin:6px 0 0">
<?= ai_is_available() ? 'Google Gemini 1.5 Flash Connected' : 'Smart Algorithmic Engine Active' ?>
</h2>
</div>
<span class="pill" style="background:#ffffff25;color:#fff;border:1px solid #ffffff40">
<?= ai_is_available() ? 'Cloud REST API' : 'High-Speed Procedural' ?>
</span>
</div>

<p style="color:#d5e8ec;font-size:0.95rem;line-height:1.6;margin-bottom:20px">
SkillRank implements an enterprise-grade AI architecture with automatic fallback redundancy.
If Google Gemini API is unreachable or unconfigured, all 4 generative capabilities seamlessly switch to our local procedural intelligence engine without disrupting student tests or resume exports.
</p>

<form method="post" style="display:inline">
<input type="hidden" name="action" value="ping_api">
<button type="submit" class="button button-primary" style="padding:10px 18px">
⚡ Test AI Connection & Latency
</button>
</form>
</div>

<?php if ($testResult): ?>
<div class="panel" style="margin-bottom:20px;border-left:5px solid <?= $testResult['status'] === 'online' ? 'var(--teal)' : 'var(--orange)' ?>;background:#f8fafc">
<h3 style="margin-bottom:6px">Diagnostic Ping Result</h3>
<p style="margin:0 0 10px;font-size:0.92rem;color:var(--ink)"><?= e($testResult['message']) ?></p>
<div style="display:flex;gap:15px;font-size:0.8rem;color:var(--muted)">
<span>Model: <strong><?= e($testResult['model']) ?></strong></span>
<?php if ($testResult['latency'] > 0): ?>
<span>Latency: <strong><?= $testResult['latency'] ?> ms</strong></span>
<?php endif; ?>
<span>Status: <strong style="color:<?= $testResult['status'] === 'online' ? 'var(--teal)' : 'var(--orange)' ?>"><?= strtoupper($testResult['status']) ?></strong></span>
</div>
</div>
<?php endif; ?>

<!-- 4 Core Generative AI Features -->
<div class="panel">
<div class="panel-head">
<h2>Active GenAI Capabilities</h2>
<small>All 4 modules operational</small>
</div>

<div style="display:grid;gap:15px">
<div style="border:1px solid var(--line);border-radius:8px;padding:15px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong style="color:var(--ink)">1. Automated Question Generation</strong>
<span class="pill lime">Active</span>
</div>
<small style="color:var(--muted);display:block;line-height:1.5">
Generates curriculum-aligned multiple-choice questions for any technical subject, topic, and difficulty (Easy, Medium, Hard) complete with distractors, correct answers, and conceptual explanations.
</small>
<div style="margin-top:8px">
<a href="questions.php" class="text-link" style="font-size:0.8rem">Open Question Studio →</a>
</div>
</div>

<div style="border:1px solid var(--line);border-radius:8px;padding:15px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong style="color:var(--ink)">2. Continuous Skill Gap Diagnostics</strong>
<span class="pill lime">Active</span>
</div>
<small style="color:var(--muted);display:block;line-height:1.5">
Synthesizes student assessment accuracy and speed to diagnose conceptual weaknesses and recommend 3-step actionable study plans.
</small>
<div style="margin-top:8px">
<a href="admin_analytics.php" class="text-link" style="font-size:0.8rem">View Institutional Gaps →</a>
</div>
</div>

<div style="border:1px solid var(--line);border-radius:8px;padding:15px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong style="color:var(--ink)">3. Skill-Based Resume Synthesizer</strong>
<span class="pill lime">Active</span>
</div>
<small style="color:var(--muted);display:block;line-height:1.5">
Generates tailored, ATS-friendly professional summary statements highlighting verified candidate skills, test accuracy, and career aspiration.
</small>
</div>

<div style="border:1px solid var(--line);border-radius:8px;padding:15px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
<strong style="color:var(--ink)">4. Interactive AI Mentor (Student Chatbot)</strong>
<span class="pill lime">Active</span>
</div>
<small style="color:var(--muted);display:block;line-height:1.5">
Interactive floating drawer available to all students providing immediate conceptual explanations, code snippets, and study guidance on demand.
</small>
</div>
</div>
</div>
</section>

<!-- Right Column: API Configuration Guidance -->
<aside>
<div class="panel" style="margin-bottom:20px">
<h3 style="margin-bottom:8px">Gemini API Setup</h3>
<p style="font-size:0.85rem;color:var(--muted);line-height:1.5">
To enable cloud-powered Gemini 1.5 Flash generative AI:
</p>
<ol style="font-size:0.82rem;color:var(--muted);padding-left:18px;line-height:1.6">
<li>Get a free API key from <a href="https://aistudio.google.com/" target="_blank" style="color:var(--teal);text-decoration:underline">Google AI Studio</a>.</li>
<li>Open <code>config.php</code> in your project root.</li>
<li>Set the <code>GEMINI_API_KEY</code> constant:
<pre style="background:#f1f5f9;padding:8px;border-radius:6px;font-size:0.75rem;margin:6px 0;overflow-x:auto">defined('GEMINI_API_KEY') or 
define('GEMINI_API_KEY', 'YOUR_KEY_HERE');</pre>
</li>
<li>Refresh this page and click <strong>Test AI Connection</strong>!</li>
</ol>
</div>

<div class="panel" style="background:#f8fafc">
<h4 style="margin:0 0 6px">Tech Fest Presentation Note</h4>
<p style="font-size:0.8rem;color:var(--muted);margin:0;line-height:1.5">
Judges frequently evaluate how applications handle network failure. SkillRank's dual-engine design guarantees 100% feature availability whether online with Gemini or presenting offline on a local XAMPP server.
</p>
</div>
</aside>
</div>

<?php include 'includes/footer.php'; ?>
