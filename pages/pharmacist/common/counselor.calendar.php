<?php $calendarCounselorId = (int) ($currentCounselor['counselorId'] ?? 0); ?>
<div class="calendar-container" id="counselorCalendar">
    <div class="calendar">
        <header>
            <span class="nav" id="calPrevMonth">&lt;</span>
            <h2 id="calMonthYear">January 2026</h2>
            <span class="nav" id="calNextMonth">&gt;</span>
        </header>
        <table>
            <thead>
                <tr>
                    <th>S</th>
                    <th>M</th>
                    <th>T</th>
                    <th>W</th>
                    <th>T</th>
                    <th>F</th>
                    <th>S</th>
                </tr>
            </thead>
            <tbody id="calendarBody"></tbody>
        </table>
    </div>

    <div class="day-detail-panel" id="dayDetailPanel" style="display: none;">
        <h4 id="selectedDateTitle">Sessions on January 20</h4>
        <div class="session-list" id="daySessionList"></div>
    </div>
</div>

<script>
(() => {
    const counselorId = <?= json_encode($calendarCounselorId) ?>;
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    let sessionsData = [];

    const months = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    document.addEventListener("DOMContentLoaded", () => {
        renderCalendar();
        fetchSessions();
        document.getElementById("calPrevMonth")?.addEventListener("click", () => changeMonth(-1));
        document.getElementById("calNextMonth")?.addEventListener("click", () => changeMonth(1));
    });

    function changeMonth(delta) {
        currentMonth += delta;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear -= 1;
        }
        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear += 1;
        }
        renderCalendar();
        fetchSessions();
    }

    function renderCalendar() {
        const monthYear = document.getElementById("calMonthYear");
        const tbody = document.getElementById("calendarBody");
        if (!monthYear || !tbody) return;

        monthYear.textContent = `${months[currentMonth]} ${currentYear}`;

        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const today = new Date();

        let html = "";
        let day = 1;

        for (let row = 0; row < 6; row += 1) {
            html += "<tr>";
            for (let col = 0; col < 7; col += 1) {
                if ((row === 0 && col < firstDay) || day > daysInMonth) {
                    html += '<td class="empty"></td>';
                    continue;
                }

                const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
                const classes = [];
                if (
                    today.getDate() === day &&
                    today.getMonth() === currentMonth &&
                    today.getFullYear() === currentYear
                ) {
                    classes.push("today");
                }

                html += `<td data-date="${dateStr}" class="${classes.join(" ")}">${day}</td>`;
                day += 1;
            }
            html += "</tr>";
            if (day > daysInMonth) break;
        }

        tbody.innerHTML = html;
        tbody.querySelectorAll("td[data-date]").forEach((td) => {
            td.addEventListener("click", () => selectDate(td.dataset.date));
        });
        highlightBookedDays();
    }

    function fetchSessions() {
        const startDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-01`;
        const endDate = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(new Date(currentYear, currentMonth + 1, 0).getDate()).padStart(2, "0")}`;

        fetch(`/api/sessions/counselor?counselorId=${counselorId}&startDate=${startDate}&endDate=${endDate}`)
            .then((response) => response.ok ? response.json() : Promise.reject())
            .then((data) => {
                sessionsData = Array.isArray(data.sessions) ? data.sessions : [];
                highlightBookedDays();
            })
            .catch(() => {
                sessionsData = [];
                highlightBookedDays();
            });
    }

    function highlightBookedDays() {
        const bookedDates = [...new Set(sessionsData.map((session) => session.date))];
        document.querySelectorAll("#calendarBody td[data-date]").forEach((td) => {
            td.classList.toggle("has-sessions", bookedDates.includes(td.dataset.date));
        });
    }

    function selectDate(dateStr) {
        document.querySelectorAll("#calendarBody td.selected").forEach((td) => td.classList.remove("selected"));
        const td = document.querySelector(`#calendarBody td[data-date="${dateStr}"]`);
        if (td) td.classList.add("selected");
        showDayDetails(dateStr);
    }

    function showDayDetails(dateStr) {
        const panel = document.getElementById("dayDetailPanel");
        const title = document.getElementById("selectedDateTitle");
        const list = document.getElementById("daySessionList");
        if (!panel || !title || !list) return;

        const date = new Date(`${dateStr}T00:00:00`);
        title.textContent = `Sessions on ${months[date.getMonth()]} ${date.getDate()}`;

        const daySessions = sessionsData.filter((session) => session.date === dateStr);
        if (!daySessions.length) {
            list.innerHTML = '<div class="no-sessions">No sessions scheduled</div>';
        } else {
            list.innerHTML = daySessions.map((session) => `
                <div class="session-item ${session.type}">
                    <div class="session-time">${session.time}</div>
                    <div class="session-client">${session.clientName}</div>
                    <div class="session-type">${String(session.type).replace("_", " ")}</div>
                </div>
            `).join("");
        }

        panel.style.display = "block";
    }
})();
</script>
