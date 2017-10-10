$(document).ready(function () {
    $('#subjects-data-table').dt({updateUrl: '/subjects', deleteUrl: '/subjects/delete', columns: 5})

    $(document).on('click', 'button[data-action="subject-edit"]', function (event) {
        window.location.href = '/subjects/update/' + $(this).data('id');
    }).on('click', 'button[data-action="subject-delete"]', function (event) {
        var id = $(this).data('id');

        var $tr       = $('tr[data-id="' + id + '"]'),
            prevIndex = $tr.prev('tr').index();

        $('#subjects-data-table')
            .dt('showDeleteConfirmationRow', id, prevIndex, null/*, { deleteUrl: '/subjects/delete', columns: 5 }*/);
    }).on('click', 'button[data-action="subject-deactivate"]', function (event) {
        var id = $(this).data('id');

        var $tr       = $('tr[data-id="' + id + '"]'),
            prevIndex = $tr.prev('tr').index();

        $('#subjects-data-table').dt('showDeleteConfirmationRow', id, prevIndex, {
            deleteUrl              : '/subjects/deactivate',
            countdownMessage       : 'Предмет будет деактивирован через',
            deleteLinkText         : 'Деактивировать',
            deletingProcessMessage: 'Деактивация...'
        });
    });
});