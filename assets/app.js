import './stimulus_bootstrap.js';
import './styles/app.css';

// ── Sidebar toggle ──
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('overlay');
const toggleBtn = document.getElementById('toggle-sidebar');

if (toggleBtn && sidebar && overlay) {
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
}

// ── Star rating: highlight on hover ──
document.querySelectorAll('.star-group').forEach(group => {
    const labels = [...group.querySelectorAll('.star-label')].reverse();
    labels.forEach((label, i) => {
        label.addEventListener('mouseenter', () => {
            labels.forEach((l, j) => {
                l.style.color = j <= i ? 'var(--color-gold)' : '';
            });
        });
        label.addEventListener('mouseleave', () => {
            labels.forEach(l => l.style.color = '');
        });
    });
});
