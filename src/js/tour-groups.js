var tourGroups = new Tour({
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
        if (tour.getCurrentStep() != 3) {
            endTour(tour);
        }
    },
    steps: [
        {
            element: ".tabs-container",
            title: "Добавление классов",
            content: "На данной странице расположена форма для добавления классов.",
            placement: "top"
        },
        {
            element: ".nav-tabs",
            title: "Категории",
            content: "Переходя между вкладками, вы можете добавлять младшие, средние и старшие классы. Сделаем это позже. Для начала рассмотрим форму.",
            placement: "bottom"
        },
        {
            element: "#add-class-1",
            title: "Добавить класс",
            content: "<span class='text-success font-bold'>Нажмие на кнопку \"Добавить класс\", чтобы добавить класс.</span>",
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
            element: "#groups-table tbody tr:eq(1) td:eq(1)", //".group-letter:first",
            title: "Данные о классе",
            content: "В появившемся столбце заполните данные о классе. Обязательным является только поле \"Литера\". Если вы передумали добавлять класс, то поставьте галочку в строке \"Удалить\".<br /><strong class='text-success'>Теперь добавьте несколько классов и нажмите \"Сохранить\".</strong>",
            placement: "right",
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
            </div>",
        },
        {
            element: "#groups-table",
            title: "Классы созданы!",
            content: "Отлично! Теперь у Вас есть классы, осталось совсем немного, продолжайте!",
            placement: "top",
            template: "<div class='popover tour'>\n\
                <div class='arrow'></div>\n\
                <h3 class='popover-title'></h3>\n\
                <div class='popover-content'></div>\n\
                <div class='popover-navigation'>\n\
                    <div class='btn-group'>\n\
                        <button class='btn btn-sm btn-default' data-role='next'>Далее »</button>\n\
                    </div>\n\
                    <button class='btn btn-sm btn-default' data-role='end'>Завершить</button>\n\
                </div>\n\
            </div>",
        },
        {
            path: '/?tour_step=6'
        }
    ]
});

tourGroups.init();
tourGroups.setCurrentStep(__step__);
tourGroups.start(true);

$(document).on('click', '[data-action="add-group"]', function (event) {
    tourGroups.next();
});
