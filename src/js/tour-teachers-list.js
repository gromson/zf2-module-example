var tourTeachers = new Tour({
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
        endTour(tour);
    },
    steps: [
        {
            element: "#teachers-data-table-wrapper",
            title: "Преподаватели",
            content: "Вы перешли на страницу списка преподавателей.",
            placement: "top"
        },
        {
            element: "#create-teacher-button",
            title: "Добавьте преподавателя",
            content: "<span class='text-success font-bold'>Нажмите на кнопку, чтобы добавить преподавателя.</span>",
            placement: "bottom",
            template: "<div class='popover tour'>\n\
                <div class='arrow'></div>\n\
                <h3 class='popover-title'></h3>\n\
                <div class='popover-content'></div>\n\
                <div class='popover-navigation'>\n\
                    <div class='btn-group'>\n\
                        <button class='btn btn-sm btn-default' data-role='prev'>« Назад</button>\n\
                    </div>\n\
                    <button class='btn btn-sm btn-default' data-role='end'>Завершить</button>\n\
                </div>\n\
            </div>",
        },
        {
            element: "#teachers-data-table tbody tr:first",
            title: "Преподаватели",
            content: "Добавленные преподаватели отображаются в этой таблице",
            placement: "top"
        },
        {
            path: '/?tour_step=5'
        }
    ]
});

tourTeachers.init();
tourTeachers.setCurrentStep(__step__);
tourTeachers.start(true);