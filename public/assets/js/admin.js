document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');
    const draftForm = document.querySelector('[data-draft-form]');

    const slugify = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    const draftSessionKey = 'lsb_last_submitted_draft_key';

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

    document.querySelectorAll('[data-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => {
            const form = field.closest('form');

            if (form) {
                form.submit();
            }
        });
    });

    if (draftForm) {
        const draftKey = draftForm.getAttribute('data-draft-key');
        const storageKey = draftKey ? `lsb_draft_${draftKey}` : null;
        const successAlert = document.querySelector('.alert-success');
        const draftFields = Array.from(
            draftForm.querySelectorAll('input[name], textarea[name], select[name]')
        ).filter((field) => {
            const type = (field.getAttribute('type') || '').toLowerCase();
            return type !== 'hidden' && type !== 'file' && type !== 'submit' && type !== 'button' && type !== 'reset';
        });

        const readDraft = () => {
            if (!storageKey) {
                return {};
            }

            try {
                const raw = window.localStorage.getItem(storageKey);
                return raw ? JSON.parse(raw) : {};
            } catch (error) {
                return {};
            }
        };

        const writeDraft = () => {
            if (!storageKey) {
                return;
            }

            const payload = {};
            draftFields.forEach((field) => {
                const type = (field.getAttribute('type') || '').toLowerCase();

                if (type === 'checkbox') {
                    payload[field.name] = field.checked;
                    return;
                }

                if (type === 'radio') {
                    if (field.checked) {
                        payload[field.name] = field.value;
                    }
                    return;
                }

                payload[field.name] = field.value;
            });

            try {
                window.localStorage.setItem(storageKey, JSON.stringify(payload));
            } catch (error) {
                // Ignore storage quota or privacy-mode failures.
            }
        };

        const clearDraft = (key) => {
            if (!key) {
                return;
            }

            try {
                window.localStorage.removeItem(`lsb_draft_${key}`);
            } catch (error) {
                // Ignore storage failures.
            }
        };

        const submittedDraftKey = window.sessionStorage.getItem(draftSessionKey);
        if (successAlert && submittedDraftKey) {
            clearDraft(submittedDraftKey);
            window.sessionStorage.removeItem(draftSessionKey);
        }

        const restoreDraft = () => {
            const payload = readDraft();

            draftFields.forEach((field) => {
                if (!Object.prototype.hasOwnProperty.call(payload, field.name)) {
                    return;
                }

                const type = (field.getAttribute('type') || '').toLowerCase();
                const value = payload[field.name];

                if (type === 'checkbox') {
                    field.checked = Boolean(value);
                    return;
                }

                if (type === 'radio') {
                    field.checked = field.value === value;
                    return;
                }

                field.value = typeof value === 'string' ? value : '';
            });
        };

        if (!successAlert) {
            restoreDraft();

            if (slugInput) {
                slugInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }

        let draftTimer = null;
        const queueDraftWrite = () => {
            window.clearTimeout(draftTimer);
            draftTimer = window.setTimeout(writeDraft, 180);
        };

        draftFields.forEach((field) => {
            field.addEventListener('input', queueDraftWrite);
            field.addEventListener('change', queueDraftWrite);
        });

        draftForm.addEventListener('submit', () => {
            if (draftKey) {
                window.sessionStorage.setItem(draftSessionKey, draftKey);
            }
        });
    }
});
