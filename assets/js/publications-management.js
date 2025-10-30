// Publications Management JavaScript

// Initialize Quill editor
let quill;

document.addEventListener("DOMContentLoaded", function () {
	// Initialize Quill editor
	if (document.getElementById("editor")) {
		quill = new Quill("#editor", {
			theme: "snow",
			modules: {
				toolbar: [
					[{ header: [1, 2, 3, false] }],
					["bold", "italic", "underline"],
					["link", "image"],
					[{ list: "ordered" }, { list: "bullet" }],
					["clean"],
				],
			},
		});
	}

	// Form submission
	const form = document.getElementById("publicationForm");
	if (form) {
		form.addEventListener("submit", handleFormSubmit);
	}
	
	// Image preview handlers
	const featuredImageFile = document.getElementById("featuredImageFile");
	if (featuredImageFile) {
		featuredImageFile.addEventListener("change", function(e) {
			previewImage(e.target, "featuredImagePreview");
		});
	}
	
	const bannerFile = document.getElementById("bannerFile");
	if (bannerFile) {
		bannerFile.addEventListener("change", function(e) {
			previewImage(e.target, "bannerPreview");
		});
	}
});

function previewImage(input, previewId) {
	const preview = document.getElementById(previewId);
	if (!preview) return;
	
	if (input.files && input.files[0]) {
		const reader = new FileReader();
		reader.onload = function(e) {
			preview.innerHTML = `<img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">`;
		};
		reader.readAsDataURL(input.files[0]);
	} else {
		preview.innerHTML = '';
	}
}

function handleFormSubmit(e) {
	e.preventDefault();

	// Get content from Quill editor
	if (quill) {
		document.getElementById("content").value = quill.root.innerHTML;
	}

	const formData = new FormData(e.target);
	const publicationId = formData.get("id");

	// Add action
	formData.append("action", publicationId ? "update" : "create");
	
	// Debug: Log what we're sending
	console.log("Submitting publication:");
	for (let [key, value] of formData.entries()) {
		if (value instanceof File) {
			console.log(`  ${key}: [File] ${value.name}`);
		} else {
			console.log(`  ${key}: ${value}`);
		}
	}

	// Use absolute path to avoid issues with dev server
	const apiUrl = window.location.origin + "/assets/php/page/publication_operations.php";
	console.log("Posting to:", apiUrl);

	// Send request (multipart, includes files)
	fetch(apiUrl, {
		method: "POST",
		body: formData,
		credentials: 'same-origin' // Include session cookies
	})
		.then((response) => {
			console.log("Response status:", response.status);
			return response.json();
		})
		.then((data) => {
			console.log("Response data:", data);
			if (data.success) {
				showToast(data.message, "success");
				// Close modal
				const modal = bootstrap.Modal.getInstance(
					document.getElementById("addPublicationModal")
				);
				if (modal) modal.hide();
				// Reload page after short delay
				setTimeout(() => location.reload(), 1500);
			} else {
				const details = data.error
					? typeof data.error === "string"
						? data.error
						: data.error.message || JSON.stringify(data.error)
					: "";
				const msg =
					(data.message || "Operation failed") +
					(details ? ": " + details : "");
				showToast(msg, "error");
				console.error("Publication error:", data);
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showToast("An error occurred", "error");
		});
}

function viewPublication(id) {
	// Fetch publication details
	fetch("assets/php/page/publication_operations.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: "action=get&id=" + id,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				displayPublicationDetails(data.data);
			} else {
				showToast("Failed to load publication", "error");
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showToast("An error occurred", "error");
		});
}

function editPublication(id) {
	// Fetch publication details
	fetch("assets/php/page/publication_operations.php", {
		method: "POST",
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
		},
		body: "action=get&id=" + id,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				populateEditForm(data.data);
				// Open modal
				const modal = new bootstrap.Modal(
					document.getElementById("addPublicationModal")
				);
				modal.show();
			} else {
				showToast("Failed to load publication", "error");
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showToast("An error occurred", "error");
		});
}

function populateEditForm(publication) {
	document.getElementById("modalTitle").textContent = "Edit Publication";
	document.getElementById("publicationId").value = publication.id;
	document.getElementById("title").value = publication.title;
	document.getElementById("excerpt").value = publication.excerpt;
	document.getElementById("category").value = publication.category;
	document.getElementById("tags").value = publication.tags.join(", ");

	// Set Quill content
	if (quill && publication.content) {
		quill.root.innerHTML = publication.content;
	}

	// Set published date
	if (publication.publishedAt) {
		const date = new Date(publication.publishedAt);
		const formattedDate = date.toISOString().slice(0, 16);
		document.getElementById("publishedAt").value = formattedDate;
	}

	// Set image URLs
	if (publication.featuredImage && publication.featuredImage.url) {
		document.getElementById("featuredImageUrl").value =
			publication.featuredImage.url;
	}

	if (publication.banner && publication.banner.url) {
		document.getElementById("bannerUrl").value = publication.banner.url;
	}
}

function deletePublication(id) {
	if (!confirm("Are you sure you want to delete this publication?")) {
		return;
	}

	const formData = new FormData();
	formData.append("action", "delete");
	formData.append("id", id);

	fetch("assets/php/page/publication_operations.php", {
		method: "POST",
		body: formData,
	})
		.then((response) => response.json())
		.then((data) => {
			if (data.success) {
				showToast(data.message, "success");
				setTimeout(() => location.reload(), 1500);
			} else {
				showToast(data.message || "Failed to delete publication", "error");
			}
		})
		.catch((error) => {
			console.error("Error:", error);
			showToast("An error occurred", "error");
		});
}

function displayPublicationDetails(publication) {
	// Create a modal to display publication details
	const modalHtml = `
        <div class="modal fade" id="viewPublicationModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${escapeHtml(
													publication.title
												)}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <span class="badge bg-primary">${escapeHtml(
															publication.category
														)}</span>
                            ${publication.tags
															.map(
																(tag) =>
																	`<span class="badge bg-secondary ms-1">${escapeHtml(
																		tag
																	)}</span>`
															)
															.join("")}
                        </div>
                        <p class="text-muted">${escapeHtml(
													publication.excerpt
												)}</p>
                        <div class="mt-3">
                            ${publication.content}
                        </div>
                        <div class="mt-3 text-muted">
                            <small>By ${escapeHtml(
															publication.author.name
														)} | ${new Date(
		publication.publishedAt
	).toLocaleDateString()} | ${publication.readingTime} min read</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

	// Remove existing modal if any
	const existing = document.getElementById("viewPublicationModal");
	if (existing) existing.remove();

	// Add modal to body
	document.body.insertAdjacentHTML("beforeend", modalHtml);

	// Show modal
	const modal = new bootstrap.Modal(
		document.getElementById("viewPublicationModal")
	);
	modal.show();

	// Remove modal from DOM after closing
	document
		.getElementById("viewPublicationModal")
		.addEventListener("hidden.bs.modal", function () {
			this.remove();
		});
}

function showToast(message, type = "info") {
	const backgroundColor = {
		success: "linear-gradient(to right, #00b09b, #96c93d)",
		error: "linear-gradient(to right, #ff5f6d, #ffc371)",
		info: "linear-gradient(to right, #2196F3, #64B5F6)",
	};

	if (typeof Toastify !== "undefined") {
		Toastify({
			text: message,
			duration: 3000,
			gravity: "top",
			position: "right",
			style: {
				background: backgroundColor[type] || backgroundColor["info"],
			},
		}).showToast();
	} else {
		alert(message);
	}
}

function escapeHtml(text) {
	const div = document.createElement("div");
	div.textContent = text;
	return div.innerHTML;
}

function getCurrentUserId() {
	const el = document.getElementById("current-user-id");
	if (el && el.value) {
		return el.value;
	}
	return 1;
}

// Reset form when modal is hidden
document
	.getElementById("addPublicationModal")
	?.addEventListener("hidden.bs.modal", function () {
		document.getElementById("publicationForm").reset();
		document.getElementById("modalTitle").textContent = "Add Publication";
		document.getElementById("publicationId").value = "";
		if (quill) {
			quill.setContents([]);
		}
	});
