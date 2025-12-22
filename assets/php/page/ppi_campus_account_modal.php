<?php
$id = $_GET['id'];
require_once("../user.php");
$user = new User();
if ($user->hasPermission("student_db_add") == false){
    echo $user->getUserPermissions();
    exit();
}
$isExist = $user->getUniUserByUniId($id);
if ($isExist){
?>
<b>Account Status:</b> Active
<label for="username" class="form-label">Username</label>
<input type="text" id="username" class="form-control" name="username" value="<?php echo $isExist[0][1]; ?>" disabled>
<small>Note: you can reset the password from "user list" page</small>
<br><br>
<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Back</button>
</div>
<?php
} else {
?>
<b>Account Status:</b> Not exist, need to create new account
<br><br>
<form id="addPPICampusAccForm" action="../assets/php/page/add_PPI_account.php" method="post">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" class="form-control" name="username" required>
    </div>
    <div class="mb-3">
        <label for="user-type" class="form-label">User Type</label>
        <select class="form-select" id="user-type" name="usertype" required>
            <option value="1000">PPI Campus account</option>
        </select>
    </div>
    <!-- Hidden password field that will be set by JavaScript -->
    <input type="hidden" id="password" name="password">
    <input type="hidden" id="ppi-details" name="university-details" value="<?= $id; ?>">
    <button type="button" id="create-account-btn" class="btn btn-primary">Create Account</button>
</form>
<div id="result-container" class="mt-3" style="display:none;">
    <div class="alert alert-success">
        <h5>Account Created Successfully</h5>
        <p><strong>Username:</strong> <span id="result-username"></span></p>
        <p><strong>Password:</strong> <span id="result-password"></span></p>
        <p><strong>User Type:</strong> <span id="result-usertype"></span>
        </p>
    </div>
</div>
<?php } ?>