// SkillRank Frontend Engine: Animates progress bars, dark theme toggle, and draws responsive accuracy charts

function toggleSkillRankTheme() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const next = isDark ? 'light' : 'dark';
    if (next === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('sr_theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
        localStorage.setItem('sr_theme', 'light');
    }
    updateThemeToggleUI();
    if (typeof window.redrawSkillCharts === 'function') {
        window.redrawSkillCharts();
    }
}

function updateThemeToggleUI() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const icon = document.getElementById('themeToggleIcon');
    const text = document.getElementById('themeToggleText');
    if (icon) icon.textContent = isDark ? '☀️' : '🌙';
    if (text) text.textContent = isDark ? 'Light' : 'Dark';
}

window.toggleSkillRankTheme = toggleSkillRankTheme;

document.addEventListener('DOMContentLoaded', () => {
    updateThemeToggleUI();

    // 1. Animate progress bars smoothly
    document.querySelectorAll('[data-progress]').forEach((bar) => {
        requestAnimationFrame(() => {
            const val = Math.min(100, Math.max(0, parseFloat(bar.dataset.progress) || 0));
            bar.style.width = `${val}%`;
        });
    });

    // 2. Render accuracy trajectory Canvas charts
    function drawCharts() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('[data-chart]').forEach((canvas) => {
            try {
                const raw = canvas.dataset.chart;
                const values = JSON.parse(raw);
                if (!Array.isArray(values) || values.length === 0) return;

                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const clientW = canvas.clientWidth || 300;
                const clientH = canvas.clientHeight || 150;

                const dpr = window.devicePixelRatio || 2;
                canvas.width = clientW * dpr;
                canvas.height = clientH * dpr;
                ctx.scale(dpr, dpr);

                const width = clientW;
                const height = clientH;
                const max = Math.max(...values, 100);

                ctx.clearRect(0, 0, width, height);

                // Grid guideline
                ctx.strokeStyle = isDark ? '#30363d' : '#e2e8f0';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(0, height - 10);
                ctx.lineTo(width, height - 10);
                ctx.stroke();

                // Plot path
                ctx.strokeStyle = isDark ? '#48b0bf' : '#176b78';
                ctx.lineWidth = 3;
                ctx.beginPath();

                const divisor = values.length > 1 ? (values.length - 1) : 1;

                values.forEach((value, index) => {
                    const x = values.length > 1 ? (index * (width / divisor)) : (width / 2);
                    const y = height - ((value / max) * (height - 24)) - 12;
                    if (index === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                });
                ctx.stroke();

                // Draw dots and value labels
                values.forEach((value, index) => {
                    const x = values.length > 1 ? (index * (width / divisor)) : (width / 2);
                    const y = height - ((value / max) * (height - 24)) - 12;

                    ctx.fillStyle = isDark ? '#e6edf3' : '#102d46';
                    ctx.beginPath();
                    ctx.arc(x, y, 5, 0, Math.PI * 2);
                    ctx.fill();

                    ctx.fillStyle = isDark ? '#0d1117' : '#ffffff';
                    ctx.beginPath();
                    ctx.arc(x, y, 2.5, 0, Math.PI * 2);
                    ctx.fill();
                });
            } catch (err) {
                console.warn('Canvas chart render exception:', err);
            }
        });
    }

    window.redrawSkillCharts = drawCharts;
    drawCharts();
    window.addEventListener('resize', drawCharts);
});

