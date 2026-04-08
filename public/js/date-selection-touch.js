/**
 * MOBILE SUPPORT - Capture les clics tactiles sur le calendrier
 */
function initTouchDateSelection() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    calendarEl.addEventListener('click', function(e) {
        const cell = e.target.closest('[data-date]');
        if (cell) {
            const dateStr = cell.getAttribute('data-date');
            const date = new Date(dateStr);
            
            // On simule une sélection FullCalendar
            const selectInfo = {
                start: date,
                end: new Date(date.getTime() + 86400000), // +1 jour
                startStr: dateStr,
                endStr: new Date(date.getTime() + 86400000).toISOString().split('T')[0],
                allDay: true
            };

            // On appelle manuellement la logique de sélection
            if (typeof window.onCalendarSelect === 'function') {
                window.onCalendarSelect(selectInfo, date);
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', () => setTimeout(initTouchDateSelection, 200));