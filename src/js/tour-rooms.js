var tourRooms = new Tour({
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
        if (tour.getCurrentStep() != 0) {
            endTour(tour);
        }
    },
    steps: [
        {
            element: "#button-add-room",
            title: "Страница добавления кабинетов!",
            content: "Страница добавления кабинетов аналогична уже изученной странице добавления преподавателей.<br /><br />Добавьте кабинет по аналогии с предыдущими шагами.",
            placement: "right",
            backdrop: false,
            template: "<div class='popover tour'>\n\
                <div class='arrow'></div>\n\
                <h3 class='popover-title'></h3>\n\
                <div class='popover-content'></div>\n\
                <div class='popover-navigation row no-margins'>\n\
                    <button class='btn btn-sm btn-default' data-role='end'>Завершить</button>\n\
                </div>\n\
            </div>",
        },
        {
            element: "#rooms-data-table tbody tr:first",
            title: "Кабинет добавлен!",
            content: "Отлично, кабинет добавлен! Теперь самое время узнать, как составлять учебный план, в системе РасписаниеОнлайн.ру!",
            placement: "top"
        },
        {
            element: "#side-menu",
            title: "Учебные планы",
            content: "Перейдите в раздел \"Учебные планы\", нажав на соответствующий пункт в главном меню.",
            placement: "right",
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
            </div>"
        }
    ]
});

tourRooms.init();
tourRooms.setCurrentStep(__step__);
tourRooms.start(true);

$(document).on('click', '#button-add-room', function (event) {
    tourRooms.end();
}).on('afterReloadDataTable', '#rooms-data-table', function (event) {
    tourRooms.init();
    tourRooms.setCurrentStep(1);
    tourRooms.start(true);
});