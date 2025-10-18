// Team Members Management JS

document.addEventListener("DOMContentLoaded", function () {
	const form = document.getElementById("teamForm");
	if (form) form.addEventListener("submit", onSubmitTeamForm);
});

function onSubmitTeamForm(e) {
	e.preventDefault();
	const formData = new FormData(e.target);
	const id = formData.get("id");
	formData.append("action", id ? "update" : "create");

	fetch("assets/php/page/team_operations.php", { method: "POST", body: formData })
		.then((r) => r.json())
		.then((data) => {
			if (data.success) {
				showToast(data.message || "Saved", "success");
				const modal = bootstrap.Modal.getInstance(document.getElementById("addTeamModal"));
				if (modal) modal.hide();
				setTimeout(() => location.reload(), 1200);
			} else {
				showToast(data.message || "Operation failed", "error");
			}
		})
		.catch(() => showToast("Request failed", "error"));
}

function viewMember(id) {
	const body = new URLSearchParams({ action: "get", id: String(id) });
	fetch("assets/php/page/team_operations.php", { method: "POST", headers: {"Content-Type":"application/x-www-form-urlencoded"}, body })
		.then((r) => r.json())
		.then((data) => {
			if (!data.success) return showToast("Failed to fetch member", "error");
			alert((data.data && data.data.name) ? data.data.name : "Member");
		})
		.catch(() => showToast("Request failed", "error"));
}

function editMember(id) {
	const body = new URLSearchParams({ action: "get", id: String(id) });
	fetch("assets/php/page/team_operations.php", { method: "POST", headers: {"Content-Type":"application/x-www-form-urlencoded"}, body })
		.then((r) => r.json())
		.then((data) => {
			if (!data.success) return showToast("Failed to fetch member", "error");
			const m = data.data;
			document.getElementById("teamModalTitle").textContent = "Edit Team Member";
			document.getElementById("memberId").value = m.id;
			document.getElementById("memberName").value = m.name || "";
			document.getElementById("memberPosition").value = m.position || "";
			document.getElementById("memberDepartment").value = m.department || "";
			document.getElementById("memberEmail").value = m.email || "";
			document.getElementById("memberPhone").value = m.phone || "";
			document.getElementById("memberOrder").value = m.order || 0;
			document.getElementById("memberJoined").value = (m.joinedAt || '').slice(0,10);
			document.getElementById("memberBio").value = m.bio || "";
			document.getElementById("memberSocial").value = m.socialLinks ? JSON.stringify(m.socialLinks) : "";
			document.getElementById("memberAchievements").value = m.achievements ? JSON.stringify(m.achievements) : "";
			const modal = new bootstrap.Modal(document.getElementById("addTeamModal"));
			modal.show();
		})
		catch(() => showToast("Request failed", "error"));
}

function deleteMember(id) {
	if (!confirm("Delete this member?")) return;
	const formData = new FormData();
	formData.append("action", "delete");
	formData.append("id", String(id));
	fetch("assets/php/page/team_operations.php", { method: "POST", body: formData })
		.then((r) => r.json())
		.then((data) => {
			if (data.success) { showToast("Deleted", "success"); setTimeout(() => location.reload(), 800); }
			else { showToast(data.message || "Delete failed", "error"); }
		})
		.catch(() => showToast("Request failed", "error"));
}


