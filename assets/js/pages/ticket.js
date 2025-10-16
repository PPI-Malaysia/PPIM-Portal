/**
 * Ticket Management JavaScript
 * Handles all ticket-related functionality including:
 * - DataTable initialization
 * - Form validation
 * - Date/time picker initialization
 * - Dynamic ticket type management
 */
document.addEventListener("DOMContentLoaded", function () {
	// Initialize DataTable with export buttons
	var ticketsTable = $("#tickets-datatable").DataTable({
		responsive: true,
		language: {
			paginate: {
				previous: "<i class='ti ti-chevron-left'>",
				next: "<i class='ti ti-chevron-right'>",
			},
		},
		dom: "Bfrtip",
		buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
		drawCallback: function () {
			$(".dataTables_paginate > .pagination").addClass("pagination-rounded");
		},
	});

	// Initialize the buttons
	ticketsTable
		.buttons()
		.container()
		.appendTo("#tickets-datatable_wrapper .col-md-6:eq(0)");

	// Initialize flatpickr for date and time inputs
	if (document.getElementById("eventDate")) {
		const eventDateField = document.getElementById("eventDate");
		// Ensure data attributes are properly set
		eventDateField.setAttribute("data-provider", "flatpickr");
		eventDateField.setAttribute("data-date-format", "Y-m-d");

		flatpickr(eventDateField, {
			dateFormat: "Y-m-d",
			minDate: "today",
			disableMobile: "true",
		});
	}

	if (document.getElementById("eventTime")) {
		const eventTimeField = document.getElementById("eventTime");
		// Ensure data attributes are properly set
		eventTimeField.setAttribute("data-provider", "flatpickr");
		eventTimeField.setAttribute("data-enable-time", "true");
		eventTimeField.setAttribute("data-no-calendar", "true");
		eventTimeField.setAttribute("data-time-format", "H:i");

		flatpickr(eventTimeField, {
			enableTime: true,
			noCalendar: true,
			dateFormat: "H:i",
			time_24hr: true,
			disableMobile: "true",
		});
	}

	// Initialize flatpickr for ticket end dates
	document
		.querySelectorAll('input[name="ticketEndDate[]"]')
		.forEach(function (elem) {
			// Ensure data attributes are properly set
			elem.setAttribute("data-provider", "flatpickr");
			elem.setAttribute("data-date-format", "Y-m-d");

			flatpickr(elem, {
				dateFormat: "Y-m-d",
				minDate: "today",
				disableMobile: "true",
			});
		});

	// Add Ticket Type Button
	if (document.getElementById("addTicketType")) {
		document
			.getElementById("addTicketType")
			.addEventListener("click", function () {
				const ticketTypeTemplate = `
                <div class="ticket-type border rounded p-3 mb-3">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-sm btn-danger remove-ticket-type">
                            <i class="ti ti-trash"></i> Remove
                        </button>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Ticket Type</label>
                            <input type="text" class="form-control" name="ticketType[]" placeholder="e.g. VIP, Regular, Student" required>
                            <div class="invalid-feedback">
                                Please provide a ticket type.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Price (RM)</label>
                            <input type="number" class="form-control" name="ticketPrice[]" min="0" step="0.01" required>
                            <div class="invalid-feedback">
                                Please provide a valid price.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Quantity Available</label>
                            <input type="number" class="form-control" name="ticketQuantity[]" min="1" required>
                            <div class="invalid-feedback">
                                Please provide a valid quantity (minimum 1).
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sale End Date</label>
                            <input type="text" class="form-control" name="ticketEndDate[]" data-provider="flatpickr" data-date-format="Y-m-d" required>
                            <div class="invalid-feedback">
                                Please select a sale end date.
                            </div>
                        </div>
                    </div>
                </div>
            `;

				const ticketTypesContainer = document.getElementById("ticketTypes");
				const tempDiv = document.createElement("div");
				tempDiv.innerHTML = ticketTypeTemplate;

				// Append the new ticket type
				ticketTypesContainer.appendChild(tempDiv.firstElementChild);

				// Initialize flatpickr for the new date field
				const newDateField = ticketTypesContainer.querySelector(
					'.ticket-type:last-child [name="ticketEndDate[]"]'
				);

				// Make sure the data-provider attribute is properly set
				newDateField.setAttribute("data-provider", "flatpickr");
				newDateField.setAttribute("data-date-format", "Y-m-d");

				flatpickr(newDateField, {
					dateFormat: "Y-m-d",
					minDate: "today",
					disableMobile: "true",
				});

				// Add event listener to the remove button
				ticketTypesContainer
					.querySelector(".ticket-type:last-child .remove-ticket-type")
					.addEventListener("click", function () {
						this.closest(".ticket-type").remove();
					});
			});
	}

	// Form validation
	const form = document.getElementById("createTicketForm");

	// Save Ticket Button
	if (document.getElementById("saveTicket")) {
		document
			.getElementById("saveTicket")
			.addEventListener("click", function () {
				if (!form.checkValidity()) {
					event.preventDefault();
					event.stopPropagation();
					form.classList.add("was-validated");
					return;
				}

				// Form is valid, collect the data
				const formData = {
					eventName: document.getElementById("eventName").value,
					eventDate: document.getElementById("eventDate").value,
					eventLocation: document.getElementById("eventLocation").value,
					eventTime: document.getElementById("eventTime").value,
					eventDescription: document.getElementById("eventDescription").value,
					ticketTypes: [],
				};

				// Collect ticket types data
				const ticketTypes = document.querySelectorAll(".ticket-type");
				ticketTypes.forEach(function (ticketType) {
					formData.ticketTypes.push({
						type: ticketType.querySelector('[name="ticketType[]"]').value,
						price: ticketType.querySelector('[name="ticketPrice[]"]').value,
						quantity: ticketType.querySelector('[name="ticketQuantity[]"]')
							.value,
						endDate: ticketType.querySelector('[name="ticketEndDate[]"]').value,
					});
				});

				// Submit the form data to the server via AJAX
				fetch("assets/php/API/ticket_api.php?action=create", {
					method: "POST",
					headers: {
						"Content-Type": "application/json",
					},
					body: JSON.stringify(formData),
				})
					.then((response) => response.json())
					.then((data) => {
						if (data.status === "success") {
							// Show success message
							Toastify({
								text: "Ticket created successfully!",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "right",
								backgroundColor: "#10b981",
							}).showToast();

							// Close the modal and reset the form
							$("#createTicketModal").modal("hide");
							form.classList.remove("was-validated");
							form.reset();

							// Reload the page to refresh the table
							setTimeout(() => {
								window.location.reload();
							}, 1000);
						} else {
							// Show error message
							Toastify({
								text: data.message || "Failed to create ticket",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "right",
								backgroundColor: "#ef4444",
							}).showToast();
						}
					})
					.catch((error) => {
						console.error("Error:", error);
						Toastify({
							text: "An error occurred while creating the ticket",
							duration: 3000,
							close: true,
							gravity: "top",
							position: "right",
							backgroundColor: "#ef4444",
						}).showToast();
					});
			});
	}

	// Load tickets data
	function loadTickets() {
		fetch("assets/php/API/ticket_api.php?action=get_all")
			.then((response) => response.json())
			.then((data) => {
				if (data.status === "success" && data.tickets) {
					// Clear existing table data
					ticketsTable.clear();

					// Add new data
					data.tickets.forEach((ticket) => {
						const statusBadge = getStatusBadge(ticket.status);
						const actions = `
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical font-size-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#" data-id="${ticket.id}" data-bs-toggle="modal" data-bs-target="#viewTicketModal">View Details</a>
                                    <a class="dropdown-item" href="#" data-id="${ticket.id}">Edit</a>
                                    <a class="dropdown-item text-danger delete-ticket" href="#" data-id="${ticket.id}">Delete</a>
                                </div>
                            </div>
                        `;

						ticketsTable.row
							.add([
								ticket.id,
								ticket.event_name,
								ticket.ticket_type,
								"RM " + parseFloat(ticket.price).toFixed(2),
								ticket.quantity,
								ticket.sold,
								formatDate(ticket.event_date),
								statusBadge,
								actions,
							])
							.draw(false);
					});

					// Add event listeners for actions
					addActionEventListeners();
				}
			})
			.catch((error) => {
				console.error("Error:", error);
			});
	}

	// Helper function to format date
	function formatDate(dateString) {
		const options = { year: "numeric", month: "long", day: "numeric" };
		return new Date(dateString).toLocaleDateString(undefined, options);
	}

	// Helper function to get status badge HTML
	function getStatusBadge(status) {
		let badgeClass = "";
		switch (status) {
			case "Available":
				badgeClass = "bg-success";
				break;
			case "Sold Out":
				badgeClass = "bg-danger";
				break;
			case "Expired":
				badgeClass = "bg-secondary";
				break;
			default:
				badgeClass = "bg-info";
		}

		return `<span class="badge ${badgeClass}">${status}</span>`;
	}

	// Add event listeners for action buttons
	function addActionEventListeners() {
		// Delete ticket
		document.querySelectorAll(".delete-ticket").forEach((button) => {
			button.addEventListener("click", function (e) {
				e.preventDefault();
				const ticketId = this.getAttribute("data-id");

				if (confirm("Are you sure you want to delete this ticket?")) {
					fetch(`assets/php/API/ticket_api.php?action=delete&id=${ticketId}`, {
						method: "DELETE",
					})
						.then((response) => response.json())
						.then((data) => {
							if (data.status === "success") {
								Toastify({
									text: "Ticket deleted successfully!",
									duration: 3000,
									close: true,
									gravity: "top",
									position: "right",
									backgroundColor: "#10b981",
								}).showToast();

								// Reload tickets
								loadTickets();
							} else {
								Toastify({
									text: data.message || "Failed to delete ticket",
									duration: 3000,
									close: true,
									gravity: "top",
									position: "right",
									backgroundColor: "#ef4444",
								}).showToast();
							}
						})
						.catch((error) => {
							console.error("Error:", error);
						});
				}
			});
		});
	}

	// Load ticket statistics
	function loadTicketStats() {
		fetch("assets/php/API/ticket_api.php?action=get_stats")
			.then((response) => response.json())
			.then((data) => {
				if (data.status === "success" && data.stats) {
					// Update statistics in the UI
					document.querySelector(".col-xl-3:nth-child(1) h3").textContent =
						data.stats.total_tickets;
					document.querySelector(".col-xl-3:nth-child(2) h3").textContent =
						data.stats.active_events;
					document.querySelector(".col-xl-3:nth-child(3) h3").textContent =
						data.stats.tickets_sold;
					document.querySelector(".col-xl-3:nth-child(4) h3").textContent =
						"RM " + parseFloat(data.stats.revenue).toFixed(2);
				}
			})
			.catch((error) => {
				console.error("Error:", error);
			});
	}

	// Initial load
	loadTickets();
	loadTicketStats();
});
