function initTouchDateSelection() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    let isSelecting = false;
    let startDate = null;
    let endDate = null;


    function getDateFromCell(cell) {
        const dateStr = cell.getAttribute('data-date');
        if (!dateStr) return null;
        return new Date(dateStr);
    }

    function getCellsBetween(startCell, endCell) {
        const startDate = getDateFromCell(startCell);
        const endDate = getDateFromCell(endCell);
        if (!startDate || !endDate) return [];

        const cells = [];
        const allCells = calendarEl.querySelectorAll('[data-date]');
        
        allCells.forEach(cell => {
            const cellDate = getDateFromCell(cell);
            if (cellDate >= startDate && cellDate <= endDate) {
                cells.push(cell);
            }
        });
        
        return cells;
    }

    function highlightCells(startCell, endCell) {
        calendarEl.querySelectorAll('[data-date]').forEach(cell => {
            cell.classList.remove('fc-daygrid-day-selected');
        });

        const cellsToHighlight = getCellsBetween(startCell, endCell);
        cellsToHighlight.forEach(cell => {
            cell.classList.add('fc-daygrid-day-selected');
        });
    }

    function handleCellClick(cell) {
        if (!isSelecting) {
            // Premier clic: début de sélection
            isSelecting = true;
            startDate = cell;
            highlightCells(cell, cell);
        } else {
            // Deuxième clic: fin de sélection
            endDate = cell;
            
            // Vérifier que end >= start
            const startCellDate = getDateFromCell(startDate);
            const endCellDate = getDateFromCell(endDate);
            
            if (endCellDate < startCellDate) {
                [startDate, endDate] = [endDate, startDate];
            }
            
            highlightCells(startDate, endDate);
            
            // Appeler le callback de sélection
            triggerDateSelection(startDate, endDate);
            
            // Réinitialiser pour la prochaine sélection
            isSelecting = false;
            startDate = null;
            endDate = null;
        }
    }

    function triggerDateSelection(startCell, endCell) {
        if (typeof window.calendar === 'undefined') return;

        const startDateStr = startCell.getAttribute('data-date');
        const endDateStr = endCell.getAttribute('data-date');
        
        const startDate = new Date(startDateStr + 'T00:00:00');
        let endDate = new Date(endDateStr + 'T23:59:59');
        endDate = new Date(endDate.getTime() + 24 * 60 * 60 * 1000); 

        const selectInfo = {
            start: startDate,
            end: endDate,
            startStr: startDateStr,
            endStr: endDateStr,
            allDay: true
        };

        const selectFunction = window.calendar.getOption('select');
        if (typeof selectFunction === 'function') {
            selectFunction(selectInfo);
        }
    }

    calendarEl.addEventListener('click', function(e) {
        const cell = e.target.closest('[data-date]');
        if (cell) {
            e.preventDefault();
            e.stopPropagation();
            handleCellClick(cell);
        }
    });

    let dragStartCell = null;

    document.addEventListener('dragstart', function(e) {
        const cell = e.target.closest('[data-date]');
        if (cell && calendarEl.contains(cell)) {
            dragStartCell = cell;
        }
    });

    document.addEventListener('dragend', function(e) {
        dragStartCell = null;
    });
}


document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initTouchDateSelection, 100);
});


const observer = new MutationObserver(function() {

});

const config = { childList: true, subtree: true };
const calendarEl = document.getElementById('calendar');
if (calendarEl) {
    observer.observe(calendarEl, config);
}
