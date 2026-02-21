import './stimulus_bootstrap.js';
import './styles/app.css';

// ── Restaurer la position de scroll après notation ──
const savedScroll = sessionStorage.getItem('restoreScroll');
if (savedScroll) {
    sessionStorage.removeItem('restoreScroll');
    window.scrollTo({ top: parseInt(savedScroll), behavior: 'instant' });
}

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

// ── Star rating: highlight on hover, restore selection on leave ──
document.querySelectorAll('.star-group').forEach(group => {
    const inputs = [...group.querySelectorAll('.star-input')];
    const labels = [...group.querySelectorAll('.star-label')].reverse(); // index 0 = étoile 1

    function getCheckedIndex() {
        const checked = inputs.find(i => i.checked);
        if (!checked) return -1;
        return inputs.length - 1 - inputs.indexOf(checked); // converti en index "labels"
    }

    function restoreSelection() {
        const sel = getCheckedIndex();
        labels.forEach((l, j) => {
            l.style.color = j <= sel ? 'var(--color-gold)' : '';
        });
    }

    labels.forEach((label, i) => {
        label.addEventListener('mouseenter', () => {
            labels.forEach((l, j) => {
                l.style.color = j <= i ? 'var(--color-gold)' : '';
            });
        });
        label.addEventListener('mouseleave', restoreSelection);
    });

    restoreSelection();
});
