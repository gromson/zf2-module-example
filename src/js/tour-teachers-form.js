var tourTeachersForm = new Tour({
    backdrop: false,
    backdropContainer: '#wrapper',
    template: "<div class='popover tour'>\n\
        <div class='arrow'></div>\n\
        <h3 class='popover-title'></h3>\n\
        <div class='popover-content'></div>\n\
        <div class='popover-navigation'>\n\
            <div class='btn-group'>\n\
                <button class='btn btn-sm btn-default' data-role='prev'>« Назад</button>\n\
                <button class='btn btn-sm btn-default' data-role='next'>Далее »</button>\n\
            </div>\n\
            <button class='btn btn-sm btn-default' data-role='end'>Завершить</button>\n\
        </div>\n\
    </div>",
    onShown: function (tour) {
        $('body').addClass('tour-open')
    },
    onHidden: function (tour) {
        $('body').removeClass('tour-close')
    },
    onEnd: function (tour) {
        $('.onoffswitch-label').click();
        $('#teacher-lastname').focus();
    },
    onEnd: function (tour) {
        if (tour.getCurrentStep() != 4) {
            endTour(tour);
        }
    },
    steps: [
        {
            element: "#teacher-form-wrapper",
            title: "Форма редактирования",
            content: "Форма для добавления нового преподавателя.",
            placement: "top"
        },
        {
            element: "#teacher_subjects_id_chosen",
            title: "Предмет",
            content: "Первым мы создадим учителя английского языка для использования его в дальнейших примерах. Для этого установите курсор в поле и выбирете \"Английский язык\" в выпадающем списке, затем нажмите \"Далее »\".",
            placement: "top"
        },
        {
            element: ".onoffswitch-label",
            title: "Особенности",
            content: "Вы можете дать доступ в программу преподавателю.",
            placement: "right",
            onNext: function (tour) {
                $('.onoffswitch-label').click();
            }
        },
        {
            element: "#teacher-users-id",
            title: "Пользователь",
            content: "При этом преподаватель будет привязан к пользователю. Вы можете выбрать уже имеющегося пользователя, либо будет создан новый, на основе данных, указанных в форме.",
            placement: "top"
        },
        {
            element: "#user-role-code",
            title: "Роль",
            content: "В зависимости от роли пользователя ему будут даны различные права для работы в системе.",
            placement: "top",
            template: "<div class='popover tour'>\n\
                <div class='arrow'></div>\n\
                <h3 class='popover-title'></h3>\n\
                <div class='popover-content'></div>\n\
                <div class='popover-navigation'>\n\
                    <div class='btn-group'>\n\
                        <button class='btn btn-sm btn-default' data-role='prev'>« Назад</button>\n\
                        <button class='btn btn-sm btn-default' data-role='end'>Приступить »</button>\n\
                    </div>\n\
                </div>\n\
            </div>"
        }
    ]
});

tourTeachersForm.init();
tourTeachersForm.setCurrentStep(__step__);
tourTeachersForm.start(true);