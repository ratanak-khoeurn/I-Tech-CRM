/* Shared drag-and-drop stage board used by the student pipeline and outreach campaign boards. */
(function () {
    'use strict';

    /**
     * Cards move optimistically and are put back if the server rejects the move.
     * The board element opts into dragging with data-can-manage="1"; each card
     * provides data-url (PATCH endpoint) and data-name, each column a
     * .kb-body[data-stage] plus a .kb-count[data-count-for="<stage>"] badge.
     */
    function init(boardId) {
        const board = document.getElementById(boardId);
        if (!board || board.dataset.canManage !== '1') {
            return;
        }

        const field = board.dataset.field || 'stage';
        const emptyText = board.dataset.emptyText || 'Nothing here yet.';
        const metaCsrf = document.querySelector('meta[name="csrf-token"]');

        // The XSRF cookie is rewritten on every response this origin receives, so it
        // tracks a re-login from another tab; the meta tag is frozen at page render.
        const xsrfCookie = () => {
            const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
            return match ? decodeURIComponent(match[1]) : null;
        };

        function failureMessage(status) {
            if (status === 419) return 'This board is out of date. Reload it and try again.';
            if (status === 401 || status === 403) return 'Your session no longer allows moves here. Sign in again, then reload.';
            return 'The server refused the move.';
        }

        let dragging = null;

        const bodyOf = (column) => column.querySelector('.kb-body');

        function setPlaceholder(body) {
            if (body.querySelector('.kb-empty')) return;
            const box = document.createElement('div');
            box.className = 'kb-empty';
            box.textContent = emptyText;
            body.prepend(box);
        }

        function clearPlaceholder(body) {
            const box = body.querySelector('.kb-empty');
            if (box) box.remove();
        }

        function syncCount(body, delta) {
            const badge = body.parentElement.querySelector('[data-count-for="' + body.dataset.stage + '"]');
            if (badge) badge.textContent = Math.max(0, parseInt(badge.textContent, 10) + delta);
        }

        function applyCounts(counts) {
            board.querySelectorAll('.kb-body').forEach((body) => {
                const badge = body.parentElement.querySelector('[data-count-for="' + body.dataset.stage + '"]');
                if (badge && counts[body.dataset.stage] !== undefined) {
                    badge.textContent = counts[body.dataset.stage];
                }
            });
        }

        board.addEventListener('dragstart', (event) => {
            const card = event.target.closest('.kb-card');
            if (!card) return;
            dragging = card;
            card.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.cardId);
        });

        board.addEventListener('dragend', () => {
            if (dragging) dragging.classList.remove('dragging');
            board.querySelectorAll('.kb-col').forEach((col) => col.classList.remove('drag-over'));
            dragging = null;
        });

        board.addEventListener('dragover', (event) => {
            const column = event.target.closest('.kb-col');
            if (!column || !dragging) return;
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            column.classList.add('drag-over');
        });

        board.addEventListener('dragleave', (event) => {
            const column = event.target.closest('.kb-col');
            if (column && !column.contains(event.relatedTarget)) {
                column.classList.remove('drag-over');
            }
        });

        board.addEventListener('drop', (event) => {
            const column = event.target.closest('.kb-col');
            if (!column || !dragging) return;
            event.preventDefault();
            column.classList.remove('drag-over');

            const card = dragging;
            const fromBody = card.closest('.kb-body');
            const toBody = bodyOf(column);
            if (fromBody === toBody) return;

            const moveBack = () => {
                clearPlaceholder(toBody);
                fromBody.insertBefore(card, fromBody.querySelector('.kb-empty'));
                card.dataset.stage = fromBody.dataset.stage;
                syncCount(toBody, -1);
                syncCount(fromBody, 1);
                setPlaceholder(toBody);
                setPlaceholder(fromBody);
            };

            const limitNote = toBody.querySelector('.kb-limit');
            clearPlaceholder(toBody);
            toBody.insertBefore(card, limitNote);
            card.dataset.stage = toBody.dataset.stage;
            setPlaceholder(fromBody);
            syncCount(fromBody, -1);
            syncCount(toBody, 1);

            const xsrf = xsrfCookie();
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            };
            if (xsrf) {
                headers['X-XSRF-TOKEN'] = xsrf;
            } else if (metaCsrf) {
                headers['X-CSRF-TOKEN'] = metaCsrf.content;
            }

            fetch(card.dataset.url, { method: 'PATCH', headers, body: JSON.stringify({ [field]: toBody.dataset.stage }) })
                .then((response) => response.text().then((text) => {
                    let data = {};
                    try { data = JSON.parse(text); } catch (e) { /* an HTML error/redirect page is not JSON */ }
                    return { accepted: response.ok, status: response.status, data };
                }))
                .then(({ accepted, status, data }) => {
                    if (!accepted || data.ok !== true) {
                        throw new Error(data.message || failureMessage(status));
                    }
                    if (data.counts) applyCounts(data.counts);
                })
                .catch((error) => {
                    moveBack();
                    window.crmToast('Could not move "' + card.dataset.name + '": ' + error.message, 'danger');
                });
        });
    }

    window.KanbanBoard = { init };
})();
