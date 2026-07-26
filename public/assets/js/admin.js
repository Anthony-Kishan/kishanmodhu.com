/**
 * Admin panel interactions: delete confirmation, repeatable list fields,
 * image previews and drag-to-reorder.
 *
 * No dependencies — everything degrades to a working form without JavaScript,
 * apart from reordering, which needs a pointer anyway.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initConfirmations();
        initRepeaters();
        initMediaPreviews();
        initSortableTables();
    });

    /* ── Destructive-action confirmation ────────────────────────────────── */

    function initConfirmations() {
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.dataset.confirm)) {
                    event.preventDefault();
                }
            });
        });
    }

    /* ── Repeatable text rows (list fields) ─────────────────────────────── */

    function initRepeaters() {
        document.querySelectorAll('[data-repeater]').forEach(function (repeater) {
            var items = repeater.querySelector('.repeater-items');
            var addButton = repeater.querySelector('[data-repeater-add]');
            var fieldName = repeater.dataset.field;

            if (!items || !addButton) {
                return;
            }

            addButton.addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'repeater-row';

                var input = document.createElement('input');
                input.type = 'text';
                input.name = fieldName + '[]';

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn btn-ghost';
                remove.setAttribute('data-repeater-remove', '');
                remove.setAttribute('aria-label', 'Remove');
                remove.textContent = '×';

                row.append(input, remove);
                items.append(row);
                input.focus();
            });

            // Delegated so rows added after load are covered too.
            items.addEventListener('click', function (event) {
                var button = event.target.closest('[data-repeater-remove]');

                if (!button) {
                    return;
                }

                var rows = items.querySelectorAll('.repeater-row');

                // Keep one row so the field can still be filled in.
                if (rows.length === 1) {
                    rows[0].querySelector('input').value = '';
                    return;
                }

                button.closest('.repeater-row').remove();
            });
        });
    }

    /* ── Image field preview ────────────────────────────────────────────── */

    function initMediaPreviews() {
        document.querySelectorAll('[data-media-select]').forEach(function (select) {
            var preview = document.querySelector('[data-preview-for="' + select.id + '"]');

            if (!preview) {
                return;
            }

            select.addEventListener('change', function () {
                if (select.value === '') {
                    preview.hidden = true;
                    preview.removeAttribute('src');
                    return;
                }

                preview.src = '/' + select.value.replace(/^\/+/, '');
                preview.hidden = false;
            });
        });
    }

    /* ── Drag-to-reorder ────────────────────────────────────────────────── */

    function initSortableTables() {
        document.querySelectorAll('table[data-sortable]').forEach(function (table) {
            var body = table.querySelector('tbody');
            var url = table.dataset.reorderUrl;
            var token = table.querySelector('input[name="_token"]');

            if (!body || !url || !token) {
                return;
            }

            var dragged = null;

            body.querySelectorAll('tr').forEach(function (row) {
                var handle = row.querySelector('.drag-handle');

                if (!handle) {
                    return;
                }

                // Only the handle starts a drag, so text stays selectable.
                handle.addEventListener('mousedown', function () {
                    row.draggable = true;
                });

                row.addEventListener('dragstart', function () {
                    dragged = row;
                    row.classList.add('is-dragging');
                });

                row.addEventListener('dragend', function () {
                    row.classList.remove('is-dragging');
                    row.draggable = false;
                    dragged = null;
                    persist(body, url, token.value);
                });

                row.addEventListener('dragover', function (event) {
                    event.preventDefault();

                    if (!dragged || dragged === row) {
                        return;
                    }

                    var bounds = row.getBoundingClientRect();
                    var isAfter = event.clientY > bounds.top + bounds.height / 2;

                    body.insertBefore(dragged, isAfter ? row.nextSibling : row);
                });
            });
        });
    }

    function persist(body, url, token) {
        var order = Array.prototype.map.call(body.querySelectorAll('tr'), function (row) {
            return row.dataset.id;
        });

        var payload = new FormData();
        payload.append('_token', token);
        order.forEach(function (id) {
            payload.append('order[]', id);
        });

        fetch(url, { method: 'POST', body: payload, credentials: 'same-origin' })
            .catch(function () {
                window.alert('The new order could not be saved. Please reload and try again.');
            });
    }
}());
