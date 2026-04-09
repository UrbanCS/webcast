document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');

    const slugify = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    if (titleInput && slugInput) {
        let manualSlug = slugInput.value.trim() !== '';

        slugInput.addEventListener('input', () => {
            manualSlug = slugInput.value.trim() !== '';
        });

        titleInput.addEventListener('input', () => {
            if (!manualSlug) {
                slugInput.value = slugify(titleInput.value);
            }
        });
    }

    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.getAttribute('data-copy-text') || '';

            try {
                await navigator.clipboard.writeText(value);
                const original = button.textContent;
                button.textContent = 'Copié';
                window.setTimeout(() => {
                    button.textContent = original;
                }, 1600);
            } catch (error) {
                window.prompt('Copiez ce lien :', value);
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Confirmer ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
