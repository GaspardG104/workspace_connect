let calendar;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        selectable: true,
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        // Sécurité week-end
        selectAllow: function (info) {
            let checkDate = new Date(info.start);
            while (checkDate < info.end) {
                if (checkDate.getDay() === 0 || checkDate.getDay() === 6) return false;
                checkDate.setDate(checkDate.getDate() + 1);
            }
            return true;
        },
        select: function (info) {
            let actualEnd = new Date(info.end);
            if (info.allDay) actualEnd.setDate(actualEnd.getDate() - 1);

            if (typeof window.onCalendarSelect === 'function') {
                window.onCalendarSelect(info, actualEnd);
            }
            calendar.unselect();
        },
        events: '/workspace_connect/reservation/getEvents'
    });

    calendar.render();
});

window.refreshCalendarResource = function(resourceId) {
    if (!calendar) return;
    calendar.setOption('events', `/workspace_connect/reservation/getEvents?id_resource=${resourceId}`);
    calendar.refetchEvents();
};