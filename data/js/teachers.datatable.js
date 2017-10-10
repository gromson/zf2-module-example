$(document).ready(function () {
    var TeacherService = function () {
    };

    TeacherService.prototype.dismiss = function (id) {
        $.ajax({
            url: '/teachers/dismiss',
            data: {id: id, add_vacancy: 0},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                let queryParams = $('#teachers-data-table').find('tr.filter').find('input, select').serialize();
                $('#teachers-data-table').dtw('update', '/teachers?' + queryParams, 'tbody');

                if (data.status != true) {
                    swal('Ошибка!', 'Во время операции увольнения произошла ошибка! Попробуйте повторить позже!', 'error');
                }
            }
        });
    };

    TeacherService.prototype.dismissWithVacancy = function (id) {
        $.ajax({
            url: '/teachers/dismiss',
            data: {id: id, add_vacancy: 1},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                let queryParams = $('#teachers-data-table').find('tr.filter').find('input, select').serialize();
                $('#teachers-data-table').dtw('update', '/teachers?' + queryParams, 'tbody');

                if (data.status != true) {
                    swal('Ошибка!', 'Во время операции увольнения произошла ошибка! Попробуйте повторить позже!', 'error');
                }
            }
        });
    };

    $('#teachers-data-table').dt({updateUrl: '/teachers', deleteUrl: '/teachers/delete', columns: 5});

    $(document).on('click', 'button[data-action="teacher-edit"]', function (event) {
        window.location.href = '/teachers/update/' + $(this).data('id');
    }).on('click', 'button[data-action="teacher-delete"]', function (event) {
        var id = $(this).data('id');

        var $tr = $('tr[data-id="' + id + '"]'),
            prevIndex = $tr.prev('tr').index();

        $('#teachers-data-table').dt('showDeleteConfirmationRow', id, prevIndex, null);
    }).on('click', 'button[data-action="teacher-dismiss"]', function (event) {
        let id = $(this).data('id');

        $(this).parents('tr').dtw(
            'confirm',
            {
                message: 'Уволить преподавателя?',
                options: [
                    {
                        text: 'Уволить и создать вакансию',
                        default: true,
                        action: function (event, $tr, $originTr) {
                            let teacherService = new TeacherService();
                            teacherService.dismissWithVacancy($originTr.data('id'));
                        }
                    },
                    {
                        text: 'Уволить, не создавая вакансию',
                        default: false,
                        action: function (event, $tr, $originTr) {
                            let teacherService = new TeacherService();
                            teacherService.dismiss($originTr.data('id'));
                        }
                    },
                    {
                        text: 'Отменить',
                        default: false,
                        action: function (event, $tr, $originTr) {
                            $tr.remove();
                            $originTr.show();
                        }
                    }
                ]
            }
        );
        /*
         swal({
         type: 'warning',
         title: 'Уволить преподавателя?',
         showCancelButton: true,
         cancelButtonText: 'Нет',
         closeOnConfirm: false
         }, function () {
         swal({
         type: 'warning',
         title: 'Создать вакансию?',
         text: 'Создать вакансию и заменить ею преподавателя в учебном плане.',
         showCancelButton: true,
         cancelButtonText: 'Нет, просто уволить',
         }, function (isConfirmed) {
         var teacherService = new TeacherService();

         if (isConfirmed) {
         teacherService.dismissWithVacancy();
         } else {
         teacherService.dismiss();
         }
         });
         });
         */
    });
});