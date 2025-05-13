class CalendarSchedule {
    constructor() {
        (this.body = document.body),
            (this.modal = new bootstrap.Modal(
                document.getElementById("event-modal"),
                {
                    backdrop: "static",
                }
            )),
            (this.calendar = document.getElementById("calendar")),
            (this.formEvent = document.getElementById("forms-event")),
            (this.btnNewEvent = document.getElementById("btn-new-event")),
            (this.btnDeleteEvent = document.getElementById("btn-delete-event")),
            (this.btnSaveEvent = document.getElementById("btn-save-event")),
            (this.modalTitle = document.getElementById("modal-title")),
            (this.calendarObj = null),
            (this.selectedEvent = null),
            (this.newEventData = null);
    }
    onEventClick(e) {
        this.formEvent?.reset(),
            this.formEvent.classList.remove("was-validated"),
            (this.newEventData = null),
            (this.btnDeleteEvent.style.display = "block"),
            (this.modalTitle.text = "Edit Event"),
            this.modal.show(),
            (this.selectedEvent = e.event),
            (document.getElementById("event-title").value =
                this.selectedEvent.title),
            (document.getElementById("event-category").value =
                this.selectedEvent.classNames[0]);
    }
    onSelect(e) {
        this.formEvent?.reset(),
            this.formEvent?.classList.remove("was-validated"),
            (this.selectedEvent = null),
            (this.newEventData = e),
            (this.btnDeleteEvent.style.display = "none"),
            (this.modalTitle.text = "Add New Event"),
            this.modal.show(),
            this.calendarObj.unselect();
    }
    init() {
        var e = new Date();
        const a = this;
        var t = document.getElementById("external-events");

        // Initialize draggable
        new FullCalendar.Draggable(t, {
            itemSelector: ".external-event",
            eventData: function (e) {
                return {
                    title: e.innerText,
                    classNames: e.getAttribute("data-class"),
                };
            },
        });

        // Fetch events from API
        let initialEvents = [];

        // Function to fetch calendar events from PHP API
        const fetchEvents = () => {
            fetch("../../assets/php/API/calendar.php")
                .then((response) => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then((data) => {
                    initialEvents = data;
                    // If calendar is already initialized, remove all events and add new ones
                    if (a.calendarObj) {
                        a.calendarObj.removeAllEvents();
                        initialEvents.forEach((event) => {
                            a.calendarObj.addEvent(event);
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error fetching calendar events:", error);
                    // Fallback to default events if API fails
                    initialEvents = [
                        {
                            title: "Interview - Backend Engineer",
                            start: e,
                            end: e,
                            className: "bg-primary",
                        },
                        {
                            title: "Meeting with CT Team",
                            start: new Date(Date.now() + 13e6),
                            end: e,
                            className: "bg-warning",
                        },
                    ];

                    // Apply fallback events if calendar is initialized
                    if (a.calendarObj) {
                        a.calendarObj.removeAllEvents();
                        initialEvents.forEach((event) => {
                            a.calendarObj.addEvent(event);
                        });
                    }
                });
        };

        // Initial fetch
        fetchEvents();

        // Set t to initialEvents for calendar initialization
        var t = initialEvents;
        // Initialize calendar with empty events first
        (a.calendarObj = new FullCalendar.Calendar(a.calendar, {
            plugins: [],
            slotDuration: "00:30:00",
            slotMinTime: "07:00:00",
            slotMaxTime: "19:00:00",
            themeSystem: "bootstrap",
            bootstrapFontAwesome: !1,
            buttonText: {
                today: "Today",
                month: "Month",
                week: "Week",
                day: "Day",
                list: "List",
                prev: "Prev",
                next: "Next",
            },
            initialView: "dayGridMonth",
            handleWindowResize: !0,
            height: window.innerHeight - 200,
            headerToolbar: {
                left: "prev,next today refresh",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
            },
            customButtons: {
                refresh: {
                    text: "Refresh",
                    click: function () {
                        fetchEvents();
                    },
                },
            },
            editable: !0,
            droppable: !0,
            selectable: !0,
            dateClick: function (e) {
                a.onSelect(e);
            },
            eventClick: function (e) {
                a.onEventClick(e);
            },
        })),
            a.calendarObj.render(),
            // Fetch events after calendar is rendered
            fetchEvents(),
            a.btnNewEvent.addEventListener("click", function (e) {
                a.onSelect({
                    date: new Date(),
                    allDay: !0,
                });
            }),
            a.formEvent?.addEventListener("submit", function (e) {
                e.preventDefault();
                const t = a.formEvent;
                var n;
                t.checkValidity()
                    ? (a.selectedEvent
                          ? (a.selectedEvent.setProp(
                                "title",
                                document.getElementById("event-title").value
                            ),
                            a.selectedEvent.setProp(
                                "classNames",
                                document.getElementById("event-category").value
                            ))
                          : ((n = {
                                title: document.getElementById("event-title")
                                    .value,
                                start: a.newEventData.date,
                                allDay: a.newEventData.allDay,
                                className:
                                    document.getElementById("event-category")
                                        .value,
                            }),
                            a.calendarObj.addEvent(n)),
                      a.modal.hide())
                    : (e.stopPropagation(), t.classList.add("was-validated"));
            }),
            a.btnDeleteEvent.addEventListener("click", function (e) {
                a.selectedEvent &&
                    (a.selectedEvent.remove(),
                    (a.selectedEvent = null),
                    a.modal.hide());
            });
    }
}
document.addEventListener("DOMContentLoaded", function (e) {
    new CalendarSchedule().init();
});
