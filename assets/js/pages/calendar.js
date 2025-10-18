/**
 * CalendarSchedule - A class for managing calendar functionality
 * Enhanced with proper timezone handling and improved event management
 */
class CalendarSchedule {
    /**
     * Initialize the calendar application
     */
    constructor() {
        // UI Elements
        this.body = document.body;
        this.calendar = document.getElementById("calendar");
        this.modal = document.getElementById("event-modal")
            ? new bootstrap.Modal(document.getElementById("event-modal"))
            : null;
        this.formEvent = document.getElementById("forms-event");
        this.btnNewEvent = document.getElementById("btn-new-event");
        this.btnDeleteEvent = document.getElementById("btn-delete-event");
        this.btnSaveEvent = document.getElementById("btn-save-event");
        this.modalTitle = document.getElementById("modal-title");

        // Data elements
        this.calendarObj = null;
        this.selectedEvent = null;
        this.newEventData = null;
        this.currentUserID =
            document.getElementById("current-user-id")?.value || null;
        this.currentUserName =
            document.getElementById("current-user-name")?.value || "User";

        // Configuration
        this.apiPath = "/assets/php/API/calendar.php";

        // Bind methods to this instance
        this.onEventClick = this.onEventClick.bind(this);
        this.onSelect = this.onSelect.bind(this);
        this.handleEventDrop = this.handleEventDrop.bind(this);
        this.handleEventResize = this.handleEventResize.bind(this);
        this.handleExternalDrop = this.handleExternalDrop.bind(this);
    }

    /**
     * Display a toast notification
     * @param {string} message - Message to display
     * @param {string} type - Message type (success/error)
     */
    showToast(message, type = "success") {
        if (typeof Toastify !== "undefined") {
            Toastify({
                text: message,
                duration: 3000,
                gravity: "top",
                position: "right",
                className: type,
            }).showToast();
        } else {
            // Fallback to alert
            alert(message);
        }
    }

    /**
     * Get the class name from an event object
     * @param {Object} event - FullCalendar event object
     * @returns {string} CSS class name
     */
    getEventClassName(event) {
        // Map the category values to class names
        const classMap = {
            online: "bg-success-subtle text-success",
            offline: "bg-info-subtle text-info",
            meeting: "bg-warning-subtle text-warning",
            important: "bg-danger-subtle text-danger",
            other: "bg-dark-subtle text-dark",
        };

        // Get the category from the event
        const category = event.extendedProps?.category || "other";

        // Return the mapped class or default
        return classMap[category] || classMap["other"];
    }

    /**
     * Handle event click - display event details
     * @param {Object} e - FullCalendar eventClick event object
     */
    onEventClick(e) {
        console.log("Event clicked:", e.event);

        if (!this.formEvent) {
            console.error("Form element not found");
            return;
        }

        this.formEvent.reset();
        this.formEvent.classList.remove("was-validated");
        this.newEventData = null;
        this.selectedEvent = e.event;

        // Check if current user is the owner of this event
        const eventUserID = this.selectedEvent.extendedProps?.user_id;
        const isOwner = eventUserID == this.currentUserID;

        console.log("Event owner check:", {
            eventUserID,
            currentUserID: this.currentUserID,
            isOwner,
        });

        // Set common fields regardless of ownership
        document.getElementById("event-title").value = this.selectedEvent.title;
        document.getElementById("event-description").value =
            this.selectedEvent.extendedProps.description || "-";

        // Get the event class name
        const eventClass = this.getEventClassName(this.selectedEvent);
        const categoryMap = {
            "bg-success-subtle text-success": "online",
            "bg-info-subtle text-info": "offline",
            "bg-warning-subtle text-warning": "meeting",
            "bg-danger-subtle text-danger": "important",
            "bg-dark-subtle text-dark": "other",
        };

        // Find the corresponding category value
        const category =
            this.selectedEvent.extendedProps?.category ||
            Object.entries(categoryMap).find(([className]) =>
                eventClass.includes(className)
            )?.[1] ||
            "other";
        console.log("Event class value:", eventClass);

        // Set the category dropdown value
        document.getElementById("event-category").value = category;

        // If direct match fails, try to find the closest option
        if (document.getElementById("event-category").selectedIndex === -1) {
            const select = document.getElementById("event-category");
            // Find an option that contains the class value
            for (let i = 0; i < select.options.length; i++) {
                if (
                    eventClass.includes(select.options[i].value) ||
                    select.options[i].value.includes(eventClass)
                ) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }

        // Set date/time fields
        const startDateField = document.getElementById("event-start-date");
        const endDateField = document.getElementById("event-end-date");

        if (startDateField && endDateField) {
            // Format dates for datetime-local input
            const start = new Date(this.selectedEvent.start);
            startDateField.value = this.formatDateTimeForInput(start);

            if (this.selectedEvent.end) {
                const end = new Date(this.selectedEvent.end);
                // Adjust end date for all-day events
                if (this.isAllDayEvent(this.selectedEvent)) {
                    end.setDate(end.getDate() - 1);
                }
                endDateField.value = this.formatDateTimeForInput(end);
            } else {
                // If no end date, set it same as start date
                endDateField.value = this.formatDateTimeForInput(start);
            }
        }

        // Set field states based on ownership
        if (isOwner) {
            this.setupOwnerView();
        } else if (hasEditAccess) {
            this.setupEditorView();
        } else {
            this.setupNonOwnerView();
        }

        this.modal.show();
    }

    /**
     * Set up the modal view for event owners
     */
    setupOwnerView() {
        this.btnDeleteEvent.style.display = "block";
        this.modalTitle.textContent = "Edit Event";
        document.getElementById("event-title").disabled = false;
        document.getElementById("event-description").disabled = false;
        document.getElementById("event-category").disabled = false;
        document.getElementById("btn-save-event").disabled = false;
        document.getElementById("event-start-date").disabled = false;
        document.getElementById("event-end-date").disabled = false;

        // Hide creator info if visible
        const creatorInfo = document.getElementById("event-creator");
        if (creatorInfo) {
            creatorInfo.style.display = "none";
        }
    }

    /**
     * Set up the modal view for users who can edit other's events
     */
    setupEditorView() {
        this.btnDeleteEvent.style.display = hasDeleteAccess ? "block" : "none";
        this.modalTitle.textContent = "Edit Event";
        document.getElementById("event-title").disabled = false;
        document.getElementById("event-description").disabled = false;
        document.getElementById("event-category").disabled = false;
        document.getElementById("btn-save-event").disabled = false;
        document.getElementById("event-start-date").disabled = false;
        document.getElementById("event-end-date").disabled = false;

        // Show creator info
        const creatorInfo = document.getElementById("event-creator");
        if (creatorInfo) {
            creatorInfo.textContent =
                "Created by: " +
                (this.selectedEvent.extendedProps?.creator_name || "Unknown");
            creatorInfo.style.display = "block";
        }
    }

    /**
     * Set up the modal view for non-owners (view only)
     */
    setupNonOwnerView() {
        this.btnDeleteEvent.style.display = "none";
        this.modalTitle.textContent = "View Event";
        document.getElementById("event-title").disabled = true;
        document.getElementById("event-description").disabled = true;
        document.getElementById("event-category").disabled = true;
        document.getElementById("btn-save-event").disabled = true;
        document.getElementById("event-start-date").disabled = true;
        document.getElementById("event-end-date").disabled = true;

        // Show creator info
        const creatorInfo = document.getElementById("event-creator");
        if (creatorInfo) {
            creatorInfo.textContent =
                "Created by: " +
                (this.selectedEvent.extendedProps?.creator_name || "Unknown");
            creatorInfo.style.display = "block";
        }
    }

    /**
     * Handle calendar date selection
     * @param {Object} info - FullCalendar select event object
     */
    onSelect(info) {
        console.log("Date/range selected:", info);

        if (!this.formEvent) {
            console.error("Form element not found");
            return;
        }

        this.formEvent.reset();
        this.formEvent.classList.remove("was-validated");
        this.selectedEvent = null;
        this.newEventData = info;

        // Hide delete button for new events
        if (this.btnDeleteEvent) {
            this.btnDeleteEvent.style.display = "none";
        }

        // Set modal title with date range
        if (this.modalTitle) {
            if (info.end && !this.isSameDay(info.start, info.end)) {
                const startDate = this.formatDate(info.start);
                // Subtract one day from end date (exclusive end date)
                const endDay = new Date(info.end);
                endDay.setDate(endDay.getDate() - 1);
                const endDate = this.formatDate(endDay);
                this.modalTitle.textContent = `Add Event: ${startDate} - ${endDate}`;
            } else {
                const startDate = this.formatDate(info.start);
                this.modalTitle.textContent = `Add Event: ${startDate}`;
            }
        }

        // Set start and end date/time fields
        this.setDateTimeFields(info.start, info.end);

        // Enable form fields for new event
        this.enableFormFields();

        // Show modal
        if (this.modal) {
            this.modal.show();
        } else {
            console.error("Modal not initialized");
        }

        // Unselect the calendar date range
        if (this.calendarObj) {
            this.calendarObj.unselect();
        }
    }

    /**
     * Set the date/time fields in the form
     * @param {Date} startDate - Event start date
     * @param {Date} endDate - Event end date
     */
    setDateTimeFields(startDate, endDate) {
        const startDateField = document.getElementById("event-start-date");
        const endDateField = document.getElementById("event-end-date");

        if (startDateField && endDateField) {
            // Set start date
            const start = new Date(startDate);
            startDateField.value = this.formatDateTimeForInput(start);
            startDateField.disabled = false;

            // Set end date
            if (endDate) {
                const end = new Date(endDate);
                // Subtract 1 day from exclusive end date
                end.setDate(end.getDate() - 1);
                endDateField.value = this.formatDateTimeForInput(end);
            } else {
                // If no end date, set it same as start date
                endDateField.value = this.formatDateTimeForInput(start);
            }
            endDateField.disabled = false;
        }
    }

    /**
     * Enable form fields for new event creation
     */
    enableFormFields() {
        const titleField = document.getElementById("event-title");
        const descriptionField = document.getElementById("event-description");
        const categoryField = document.getElementById("event-category");
        const saveButton = document.getElementById("btn-save-event");

        if (titleField) titleField.disabled = false;
        if (descriptionField) descriptionField.disabled = false;
        if (categoryField) categoryField.disabled = false;
        if (saveButton) saveButton.disabled = false;

        // Hide creator info if visible
        const creatorInfo = document.getElementById("event-creator");
        if (creatorInfo) {
            creatorInfo.style.display = "none";
        }
    }

    /**
     * Check if two dates are the same day
     * @param {Date} date1 - First date to compare
     * @param {Date} date2 - Second date to compare
     * @returns {boolean} True if same day
     */
    isSameDay(date1, date2) {
        if (!date1 || !date2) return false;

        const d1 = new Date(date1);
        const d2 = new Date(date2);

        return (
            d1.getFullYear() === d2.getFullYear() &&
            d1.getMonth() === d2.getMonth() &&
            d1.getDate() === d2.getDate()
        );
    }

    /**
     * Format date for display
     * @param {Date} date - Date to format
     * @returns {string} Formatted date string
     */
    formatDate(date) {
        if (!date) return "";

        const d = new Date(date);
        return d.toLocaleDateString(undefined, {
            year: "numeric",
            month: "short",
            day: "numeric",
        });
    }

    /**
     * Format datetime for input fields
     * @param {Date} date - Date to format
     * @returns {string} Formatted datetime string (YYYY-MM-DDThh:mm)
     */
    formatDateTimeForInput(date) {
        if (!date) return "";

        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const day = String(d.getDate()).padStart(2, "0");
        const hours = String(d.getHours()).padStart(2, "0");
        const minutes = String(d.getMinutes()).padStart(2, "0");

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    /**
     * Check if an event is an all-day event
     * @param {Object} event - FullCalendar event object
     * @returns {boolean} True if all-day event
     */
    isAllDayEvent(event) {
        return (
            event.allDay ||
            (event.start &&
                !event.start.getHours() &&
                !event.start.getMinutes() &&
                event.end &&
                !event.end.getHours() &&
                !event.end.getMinutes())
        );
    }

    /**
     * Handle event drag and drop
     * @param {Object} info - FullCalendar eventDrop event object
     */
    handleEventDrop(info) {
        console.log("Event dragged:", info);

        const eventId = info.event.id;
        const start = info.event.start;
        let end = info.event.end;

        // For all-day events, ensure end date is handled consistently
        if (end && this.isAllDayEvent(info.event)) {
            // Create a copy of the end date
            const adjustedEnd = new Date(end);
            end = adjustedEnd;
        }

        // Check if the event is an external event (dragged from external-events list)
        const isNewEvent = !eventId || eventId === "";

        if (isNewEvent) {
            this.createExternalEvent(info);
            return;
        }

        // Update event dates via API
        this.updateEventDates(eventId, start, end, info);
    }

    /**
     * Create a new event from external drag
     * @param {Object} info - FullCalendar eventDrop event object
     */
    createExternalEvent(info) {
        console.log("Creating new event from external drag");

        const title = info.event.title;
        const className = info.event.classNames[0];
        const description = info.event.description || "-";
        const start = info.event.start;
        const end = info.event.end;

        // Create event via API
        fetch(this.apiPath, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: title,
                description: description,
                start: start.toISOString(),
                className: className,
                end: end ? end.toISOString() : null,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    info.revert();
                    this.showToast(data.error, "error");
                    console.error("Create event error:", data.error);
                } else {
                    // Refresh to get the proper event with ID
                    this.fetchEvents();
                    this.showToast("Event added successfully");
                    console.log("External event added successfully");
                }
            })
            .catch((error) => {
                info.revert();
                this.showToast(
                    "Failed to add event: " + error.message,
                    "error"
                );
                console.error("External event creation error:", error);
            });
    }

    /**
     * Update event dates
     * @param {string} eventId - Event ID
     * @param {Date} start - New start date
     * @param {Date} end - New end date
     * @param {Object} info - FullCalendar event object (for revert if needed)
     */
    updateEventDates(eventId, start, end, info) {
        fetch(this.apiPath, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: eventId,
                start: start.toISOString(),
                end: end ? end.toISOString() : null,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    if (info) info.revert();
                    this.showToast(data.error, "error");
                } else {
                    this.showToast("Event updated successfully");
                }
            })
            .catch((error) => {
                if (info) info.revert();
                this.showToast(
                    "Failed to update event: " + error.message,
                    "error"
                );
            });
    }

    /**
     * Handle event resize
     * @param {Object} info - FullCalendar eventResize event object
     */
    handleEventResize(info) {
        console.log("Event resized:", info);

        const eventId = info.event.id;
        const start = info.event.start;
        const end = info.event.end;

        // Update event dates via API
        this.updateEventDates(eventId, start, end, info);
    }

    /**
     * Handle external event drop
     * @param {Object} info - FullCalendar drop event object
     */
    handleExternalDrop(info) {
        console.log("External event dropped:", info);

        // Create new event from dropped external item
        const dropDate = info.date.toISOString();
        const title = info.draggedEl.innerText.trim();
        const category = info.draggedEl.getAttribute("data-class");

        // Map the category to the proper class name
        const categoryToClass = {
            online: "bg-success-subtle text-success",
            offline: "bg-info-subtle text-info",
            meeting: "bg-warning-subtle text-warning",
            important: "bg-danger-subtle text-danger",
            other: "bg-dark-subtle text-dark",
        };

        const className = categoryToClass[category] || categoryToClass["other"];

        // Create event via API
        fetch(this.apiPath, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: title,
                description: "-",
                start: dropDate,
                className: className, // Send the mapped class name
                category: category, // Also send the original category
                end: null,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    this.showToast(data.error, "error");
                    console.error("External drop error:", data.error);
                    // Remove the event from calendar as it's not saved
                    this.calendarObj.getEvents().forEach((event) => {
                        if (!event.id) event.remove();
                    });
                } else {
                    // Refresh calendar to get proper event with ID
                    this.fetchEvents();
                    this.showToast("Event added successfully");
                    console.log("External event added successfully");
                }
            })
            .catch((error) => {
                this.showToast(
                    "Failed to add event: " + error.message,
                    "error"
                );
                console.error("External drop error:", error);
                // Remove the event from calendar as it's not saved
                this.calendarObj.getEvents().forEach((event) => {
                    if (!event.id) event.remove();
                });
            });
    }

    /**
     * Fetch events from the API
     */
    fetchEvents() {
        console.log("Fetching events from:", this.apiPath);

        fetch(this.apiPath)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(
                        "Network response was not ok: " + response.status
                    );
                }
                return response.json();
            })
            .then((data) => {
                // If there's an error message in the response
                if (data.error) {
                    console.error("API Error:", data.error);
                    this.showToast(data.error, "error");
                    return;
                }

                console.log("Events loaded:", data.length, "events");

                // If calendar is already initialized, remove all events and add new ones
                if (this.calendarObj) {
                    this.calendarObj.removeAllEvents();
                    data.forEach((event) => {
                        const isOwner = event.extendedProps && event.extendedProps.user_id == this.currentUserID;
                        event.editable = isOwner || hasEditAccess;
                        this.calendarObj.addEvent(event);
                    });
                }
            })
            .catch((error) => {
                console.error("Error fetching calendar events:", error);
                this.showToast(
                    "Failed to load calendar events: " + error.message,
                    "error"
                );
            });
    }

    /**
     * Save event (create or update)
     * @param {Event} e - Form submission event
     */
    saveEvent(e) {
        e.preventDefault();
        console.log("Form submitted");

        const form = this.formEvent;

        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add("was-validated");
            console.log("Form validation failed");
            return;
        }

        const title = document.getElementById("event-title").value;
        const className = document.getElementById("event-category").value;
        const description = document.getElementById("event-description").value;

        // Get start and end dates from the datetime pickers
        const startDateStr = document.getElementById("event-start-date").value;
        const endDateStr = document.getElementById("event-end-date").value;

        // Convert string dates to Date objects
        const startDate = startDateStr ? new Date(startDateStr) : null;
        const endDate = endDateStr ? new Date(endDateStr) : null;

        // Adjust end date if needed
        const adjustedEndDate = this.adjustEndDate(startDate, endDate);

        if (this.selectedEvent) {
            this.updateExistingEvent(
                title,
                description,
                className,
                startDate,
                adjustedEndDate
            );
        } else {
            this.createNewEvent(
                title,
                description,
                className,
                startDate,
                adjustedEndDate
            );
        }
    }

    /**
     * Adjust end date to ensure it's valid
     * @param {Date} startDate - Event start date
     * @param {Date} endDate - Event end date
     * @returns {Date} Adjusted end date
     */
    adjustEndDate(startDate, endDate) {
        if (!endDate) return null;

        // Copy the end date to avoid modifying the original
        const adjustedEndDate = new Date(endDate);

        // Add 1 day for FullCalendar's exclusive end date if end date equals start date
        // and this is a full-day event (no time specified)
        if (
            startDate &&
            (adjustedEndDate.getTime() === startDate.getTime() ||
                (adjustedEndDate.getHours() === 0 &&
                    adjustedEndDate.getMinutes() === 0 &&
                    adjustedEndDate.getTime() < startDate.getTime()))
        ) {
            adjustedEndDate.setDate(adjustedEndDate.getDate() + 1);
        }
        // If end date is before start date, set it to start date + 1 day
        else if (adjustedEndDate < startDate) {
            adjustedEndDate.setDate(startDate.getDate() + 1);
        }

        return adjustedEndDate;
    }

    /**
     * Update existing event
     * @param {string} title - Event title
     * @param {string} className - Event class name
     * @param {Date} startDate - Event start date
     * @param {Date} endDate - Event end date
     */
    updateExistingEvent(title, description, className, startDate, endDate) {
        console.log("Updating existing event:", this.selectedEvent.id);

        // Check if current user is the owner
        if (this.selectedEvent.extendedProps.user_id != this.currentUserID && !hasEditAccess) {
            this.showToast("You can only modify your own events", "error");
            return;
        }

        console.log("Updating event with new data:", {
            id: this.selectedEvent.id,
            title: title,
            description: description,
            className: className,
            start: startDate ? startDate.toISOString() : null,
            end: endDate ? endDate.toISOString() : null,
        });

        // Update event via API
        fetch(this.apiPath, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: this.selectedEvent.id,
                title: title,
                description: description,
                className: className,
                start: startDate ? startDate.toISOString() : null,
                end: endDate ? endDate.toISOString() : null,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    this.showToast(data.error, "error");
                    console.error("Update error:", data.error);
                } else {
                    // Refresh calendar instead of just updating the view
                    this.fetchEvents();
                    this.modal.hide();
                    this.showToast("Event updated successfully");
                    console.log("Event updated successfully with new dates");
                }
            })
            .catch((error) => {
                this.showToast(
                    "Failed to update event: " + error.message,
                    "error"
                );
                console.error("Update error:", error);
            });
    }

    /**
     * Create new event
     * @param {string} title - Event title
     * @param {string} className - Event class name
     * @param {Date} startDate - Event start date
     * @param {Date} endDate - Event end date
     */
    createNewEvent(title, description, className, startDate, endDate) {
        console.log("Creating new event with data:", {
            title: title,
            start: startDate ? startDate.toISOString() : null,
            end: endDate ? endDate.toISOString() : null,
            className: className,
        });

        // Create event via API
        fetch(this.apiPath, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                title: title,
                description: description,
                start: startDate ? startDate.toISOString() : null,
                className: className,
                end: endDate ? endDate.toISOString() : null,
                allDay: this.newEventData?.allDay || false,
            }),
        })
            .then((response) => {
                console.log("API response status:", response.status);
                return response.json();
            })
            .then((data) => {
                console.log("API response data:", data);

                if (data.error) {
                    this.showToast(data.error, "error");
                    console.error("Create event error:", data.error);
                } else {
                    // Refresh calendar to show the new event
                    this.fetchEvents();
                    this.modal.hide();
                    this.showToast("Event added successfully");
                    console.log(
                        "Event added successfully with explicit date range"
                    );
                }
            })
            .catch((error) => {
                this.showToast(
                    "Failed to add event: " + error.message,
                    "error"
                );
                console.error("Create event error:", error);
            });
    }

    /**
     * Initialize draggable external events
     */
    initDraggable() {
        const externalEvents = document.getElementById("external-events");

        if (externalEvents && FullCalendar.Draggable) {
            try {
                // Define the mapping for event types to classes
                const categoryToClass = {
                    online: "bg-success-subtle text-success",
                    offline: "bg-info-subtle text-info",
                    meeting: "bg-warning-subtle text-warning",
                    important: "bg-danger-subtle text-danger",
                    other: "bg-dark-subtle text-dark",
                };

                new FullCalendar.Draggable(externalEvents, {
                    itemSelector: ".external-event",
                    eventData: (el) => {
                        const category = el.getAttribute("data-class");
                        console.log("External event dragged:", el);
                        return {
                            title: el.innerText.replace(/^\s*\S+\s+/, ""), // Remove the icon text
                            classNames:
                                categoryToClass[category] ||
                                categoryToClass["other"],
                            extendedProps: {
                                category: category,
                                user_id: this.currentUserID,
                                creator_name: this.currentUserName,
                            },
                        };
                    },
                });
                console.log("Draggable initialized");
            } catch (error) {
                console.error("Failed to initialize draggable:", error);
            }
        }
    }

    /**
     * Handle delete event button click
     */
    deleteEvent() {
        console.log("Delete event button clicked");

        if (!this.selectedEvent) return;

        // Check if current user is the owner
        if (this.selectedEvent.extendedProps.user_id != this.currentUserID && !hasDeleteAccess) {
            this.showToast("You can only delete your own events", "error");
            return;
        }

        // Delete event via API
        fetch(this.apiPath, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: this.selectedEvent.id,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    this.showToast(data.error, "error");
                    console.error("Delete error:", data.error);
                } else {
                    // Remove event from calendar
                    this.selectedEvent.remove();
                    this.selectedEvent = null;
                    this.modal.hide();
                    this.showToast("Event deleted successfully");
                    console.log("Event deleted successfully");
                }
            })
            .catch((error) => {
                this.showToast(
                    "Failed to delete event: " + error.message,
                    "error"
                );
                console.error("Delete error:", error);
            });
    }

    /**
     * Initialize the calendar
     */
    init() {
        console.log("Calendar initialization started");

        if (!this.calendar) {
            console.error("Calendar element not found");
            return;
        }

        // Check if FullCalendar is available
        if (typeof FullCalendar === "undefined") {
            console.error("FullCalendar is not defined");
            return;
        }

        try {
            // Initialize draggable external events
            this.initDraggable();

            // Initialize calendar
            console.log("Creating calendar object");

            this.calendarObj = new FullCalendar.Calendar(this.calendar, {
                initialView: "dayGridMonth",
                headerToolbar: {
                    left: "prev,next today",
                    center: "title",
                    right: "dayGridMonth,timeGridWeek,timeGridDay,listMonth",
                },
                editable: true,
                droppable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                themeSystem: "bootstrap",
                longPressDelay: 300,
                events: [],
                select: this.onSelect,
                eventClick: this.onEventClick,
                eventDrop: this.handleEventDrop,
                eventResize: this.handleEventResize,
                drop: this.handleExternalDrop,
                eventContent: function (arg) {
                    let creatorName = arg.event.extendedProps.creator_name || '';
                    let title = arg.event.title;
                    if (creatorName) {
                        return { html: `(${creatorName}) ${title}` };
                    } else {
                        return { html: title };
                    }
                },
                eventClassNames: function (arg) {
                    // Check if event has className property
                    if (
                        arg.event.extendedProps &&
                        arg.event.extendedProps.category
                    ) {
                        const categoryToClass = {
                            online: "bg-success-subtle text-success",
                            offline: "bg-info-subtle text-info",
                            meeting: "bg-warning-subtle text-warning",
                            important: "bg-danger-subtle text-danger",
                            other: "bg-dark-subtle text-dark",
                        };
                        return (
                            categoryToClass[arg.event.extendedProps.category] ||
                            categoryToClass["other"]
                        );
                    }
                    // Return the existing classNames if present
                    return arg.event.classNames || [];
                },
                eventDidMount: function (info) {
                    // Log the applied classes for debugging
                    console.log(
                        "Event mounted:",
                        info.event.title,
                        "Classes:",
                        info.el.className
                    );
                },
            });

            console.log("Calendar object created, rendering...");
            this.calendarObj.render();
            console.log("Calendar rendered successfully");

            // Fetch events after calendar is rendered
            this.fetchEvents();

            // Setup event handlers
            this.setupEventHandlers();

            console.log("Calendar initialization completed successfully");
        } catch (error) {
            console.error("Calendar initialization failed:", error);
        }
    }

    /**
     * Set up event handlers for buttons and form
     */
    setupEventHandlers() {
        // New Event button
        if (this.btnNewEvent) {
            console.log("Setting up New Event button handler");

            this.btnNewEvent.addEventListener("click", (e) => {
                console.log("New Event button clicked");
                const now = new Date();
                this.onSelect({ start: now, end: null, allDay: true });
            });
        }

        // Form submission
        if (this.formEvent) {
            console.log("Setting up form submission handler");
            this.formEvent.addEventListener(
                "submit",
                this.saveEvent.bind(this)
            );
        }

        // Delete Event button
        if (this.btnDeleteEvent) {
            console.log("Setting up Delete Event button handler");
            this.btnDeleteEvent.addEventListener(
                "click",
                this.deleteEvent.bind(this)
            );
        }
    }
}

// Initialize calendar when the DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing calendar");
    setTimeout(function () {
        try {
            const calendarApp = new CalendarSchedule();
            calendarApp.init();
        } catch (error) {
            console.error("Error initializing calendar:", error);
        }
    }, 100); // Small delay to ensure all elements are loaded
});