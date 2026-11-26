// Global CRM scripts
document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    if (toggle && sidebar && backdrop) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            backdrop.classList.toggle('show', sidebar.classList.contains('open'));
        });
        backdrop.addEventListener('click', () => {
            sidebar.classList.remove('open');
            backdrop.classList.remove('show');
        });
    }

    // Filter bars: the × inside a search field clears it and re-runs the query.
    document.querySelectorAll('.kb-search').forEach((box) => {
        const input = box.querySelector('input');
        const clear = box.querySelector('.js-clear-filter');
        if (!input || !clear) return;
        const sync = () => clear.classList.toggle('show', input.value !== '');
        input.addEventListener('input', sync);
        clear.addEventListener('click', () => {
            input.value = '';
            sync();
            if (input.form) input.form.submit();
        });
        sync();
    });

    // Auto-hide alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            const instance = bootstrap.Alert.getOrCreateInstance(alert);
            instance.close();
        }, 6000);
    });

    // Loading overlay for normal form submits (skip GET search forms)
    const overlay = document.getElementById('loadingOverlay');
    document.querySelectorAll('form[data-loading]').forEach(form => {
        form.addEventListener('submit', () => overlay && overlay.classList.add('show'));
    });

    // Reusable popup confirmation for destructive forms and data-confirm actions.
    const confirmModalElement = document.getElementById('globalConfirmModal');
    const confirmMessage = document.getElementById('globalConfirmModalMessage');
    const confirmSubmit = document.getElementById('globalConfirmModalSubmit');
    const confirmTitleText = document.getElementById('globalConfirmModalTitleText');
    const confirmIcon = document.getElementById('globalConfirmModalIcon');
    let pendingConfirmation = null;

    if (confirmModalElement && confirmMessage && confirmSubmit) {
        const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalElement);

        // Extract the message from a form's inline onsubmit="return confirm('...')"
        // so each module keeps showing its own wording instead of one generic text.
        const extractOnSubmitMessage = form => {
            const handler = form.getAttribute('onsubmit') || '';
            const match = handler.match(/confirm\(\s*(['"])((?:\\.|(?!\1).)*)\1\s*\)/i);
            if (!match) return null;
            return match[2].replace(/\\(['"])/g, '$1').trim() || null;
        };

        const openConfirmation = (message, form, submitter, href = null) => {
            pendingConfirmation = { form, submitter, href };
            const isDelete = Boolean(form?.querySelector('input[name="_method"][value="DELETE"]'))
                || form?.method?.toUpperCase() === 'DELETE';

            // Escape HTML then turn **bold** markers into <strong>, so each message can
            // highlight the record type being acted on while interpolated names stay safe.
            const renderMessage = raw => String(raw ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');

            confirmMessage.innerHTML = renderMessage(message
                || (isDelete
                    ? 'You are about to delete this **item**. This action cannot be undone. Would you like to proceed?'
                    : 'You are about to continue with this action. Would you like to proceed?'));

            if (confirmTitleText) {
                const customTitle = form?.dataset.confirmTitle || submitter?.dataset?.confirmTitle;
                confirmTitleText.textContent = customTitle || (isDelete ? 'Confirm Deletion' : 'Confirm Action');
            }
            if (confirmIcon) {
                confirmIcon.className = isDelete
                    ? 'bi bi-exclamation-triangle text-danger me-2'
                    : 'bi bi-exclamation-circle text-warning me-2';
            }

            confirmSubmit.textContent = isDelete ? 'Delete' : 'Continue';
            confirmSubmit.classList.toggle('btn-danger', isDelete);
            confirmSubmit.classList.toggle('btn-primary', !isDelete);
            confirmModal.show();
        };

        document.addEventListener('submit', event => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-confirm-skip')) return;

            const isDelete = form.method.toUpperCase() === 'DELETE'
                || Boolean(form.querySelector('input[name="_method"][value="DELETE"]'));
            if (!isDelete) return;

            event.preventDefault();
            event.stopImmediatePropagation();
            const submitter = event.submitter;
            openConfirmation(
                form.dataset.confirm || submitter?.dataset.confirm || extractOnSubmitMessage(form),
                form,
                submitter,
            );
        }, true);

        document.addEventListener('click', event => {
            const trigger = event.target.closest('[data-confirm]');
            if (!trigger || trigger.closest('#globalConfirmModal')) return;

            const form = trigger.closest('form');
            event.preventDefault();
            event.stopImmediatePropagation();
            openConfirmation(trigger.dataset.confirm, form, trigger, form ? null : trigger.href);
        }, true);

        confirmSubmit.addEventListener('click', () => {
            if (!pendingConfirmation) return;

            const { form, submitter, href } = pendingConfirmation;
            pendingConfirmation = null;
            confirmModal.hide();

            if (form) {
                const originalOnsubmit = form.getAttribute('onsubmit');
                form.removeAttribute('onsubmit');
                form.setAttribute('data-confirm-skip', '');
                if (submitter instanceof HTMLElement && submitter.form === form) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
                form.removeAttribute('data-confirm-skip');
                if (originalOnsubmit !== null) form.setAttribute('onsubmit', originalOnsubmit);
            } else if (href) {
                window.location.assign(href);
            } else if (submitter) {
                submitter.click();
            }
        });

        confirmModalElement.addEventListener('hidden.bs.modal', () => {
            pendingConfirmation = null;
        });
    }

    // Inline toast instead of a blocking browser alert (see kanban-board.js).
    window.crmToast = function (message, variant) {
        const container = document.getElementById('crmToasts');
        if (!container) return;

        const el = document.createElement('div');
        el.className = 'toast align-items-center border-0 text-bg-' + (variant || 'warning');
        el.setAttribute('role', 'status');
        el.innerHTML = '<div class="d-flex"><div class="toast-body"></div>'
            + '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        el.querySelector('.toast-body').textContent = message;
        container.appendChild(el);

        const toast = new bootstrap.Toast(el, { delay: 7000 });
        toast.show();
        el.addEventListener('hidden.bs.toast', () => el.remove());
    };
});
