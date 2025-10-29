// Documents Management JS

document.addEventListener("DOMContentLoaded", function () {
	const form = document.getElementById("documentForm");
	if (form) {
		form.addEventListener("submit", onSubmitDocForm);
	}
});

function onSubmitDocForm(e) {
	e.preventDefault();
	const formData = new FormData(e.target);
	const id = formData.get("id");
	formData.append("action", id ? "update" : "create");

	fetch("assets/php/page/document_operations.php", {
		method: "POST",
		body: formData,
	})
		.then((r) => r.json())
		.then((data) => {
			if (data.success) {
				showToast(data.message || "Saved", "success");
				const modal = bootstrap.Modal.getInstance(
					document.getElementById("addDocumentModal")
				);
				if (modal) modal.hide();
				setTimeout(() => location.reload(), 1200);
			} else {
				showToast(data.message || "Operation failed", "error");
			}
		})
		.catch((err) => {
			console.error(err);
			showToast("Request failed", "error");
		});
}

function viewDocument(id) {
	const body = new URLSearchParams({ action: "get", id: String(id) });
	fetch("assets/php/page/document_operations.php", {
		method: "POST",
		headers: { "Content-Type": "application/x-www-form-urlencoded" },
		body,
	})
		.then((r) => r.json())
		.then((data) => {
			if (!data.success) return showToast("Failed to fetch document", "error");
			const d = data.data;
			const downloadUrl = d.downloadUrl || d.fileUrl;
			if (!downloadUrl) {
				return showToast("File not available", "warning");
			}
			window.open(downloadUrl, "_blank");
		})
		.catch(() => showToast("Request failed", "error"));
}

function editDocument(id) {
	const body = new URLSearchParams({ action: "get", id: String(id) });
	fetch("assets/php/page/document_operations.php", {
		method: "POST",
		headers: { "Content-Type": "application/x-www-form-urlencoded" },
		body,
	})
		.then((r) => r.json())
		.then((data) => {
			if (!data.success) return showToast("Failed to fetch document", "error");
			const d = data.data;
			document.getElementById("docModalTitle").textContent = "Edit Document";
			document.getElementById("docId").value = d.id;
			document.getElementById("docTitle").value = d.title || "";
			document.getElementById("docDesc").value = d.description || "";
			document.getElementById("docCategory").value = d.category || "";
			const modal = new bootstrap.Modal(
				document.getElementById("addDocumentModal")
			);
			modal.show();
		})
		.catch(() => showToast("Request failed", "error"));
}

function deleteDocument(id) {
	if (!confirm("Delete this document?")) return;
	const formData = new FormData();
	formData.append("action", "delete");
	formData.append("id", String(id));
	fetch("assets/php/page/document_operations.php", {
		method: "POST",
		body: formData,
	})
		.then((r) => r.json())
		.then((data) => {
			if (data.success) {
				showToast(data.message || "Deleted", "success");
				setTimeout(() => location.reload(), 800);
			} else {
				showToast(data.message || "Delete failed", "error");
			}
		})
		.catch(() => showToast("Request failed", "error"));
}
