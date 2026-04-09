document.addEventListener('DOMContentLoaded', () => {
    const countdownNodes = document.querySelectorAll('[data-countdown-target]');

    if (countdownNodes.length === 0) {
        return;
    }

    const format = (remaining) => {
        if (remaining <= 0) {
            return '00:00:00';
        }

        const totalSeconds = Math.floor(remaining / 1000);
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        const base = [hours, minutes, seconds].map((value) => String(value).padStart(2, '0')).join(':');

        return days > 0 ? `${days}j ${base}` : base;
    };

    const refresh = () => {
        const now = Date.now();

        countdownNodes.forEach((node) => {
            const target = new Date(node.getAttribute('data-countdown-target') || '').getTime();

            if (Number.isNaN(target)) {
                node.textContent = '--:--:--';
                return;
            }

            node.textContent = format(target - now);
        });
    };

    refresh();
    window.setInterval(refresh, 1000);
});
