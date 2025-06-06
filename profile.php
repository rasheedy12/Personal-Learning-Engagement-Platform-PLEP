<?php
define('BASE_URL', './');
$page_title = "My Profile";
// User role and data would be fetched from session/DB
$_SESSION['user_role'] = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'student'; // Mock
$current_user_data = [
    'name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'John Doe',
    'email' => 'john.doe@example.com', // From DB
    'avatar' => isset($_SESSION['user_avatar']) ? $_SESSION['user_avatar'] : BASE_URL . 'images/user_avatar_placeholder.png',
    'bio' => 'Passionate learner and aspiring web developer. Always eager to connect with fellow students and explore new technologies.',
    'role' => $_SESSION['user_role'],
    'joined_date' => '2025-01-15',
];
include 'includes/dashboard_header.php';
?>
<div class="container-fluid">
    <h1 class="h2 mb-4">My Profile</h1> 
    <p class="lead mb-4">Manage your account details, preferences, and security settings.</p>

    <form action="#" method="POST" enctype="multipart/form-data"> 
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm text-center h-100">
                <div class="card-body">
                    <img src="<?php echo htmlspecialchars($current_user_data['avatar']); ?>" alt="<?php echo htmlspecialchars($current_user_data['name']); ?>" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;" onerror="this.src='<?php echo BASE_URL; ?>images/user_avatar_placeholder.png'">
                    <h4 class="card-title"><?php echo htmlspecialchars($current_user_data['name']); ?></h4>
                    <p class="text-muted mb-1 text-capitalize"><?php echo htmlspecialchars($current_user_data['role']); ?></p>
                    <p class="small text-muted">Joined: <?php echo date("M d, Y", strtotime($current_user_data['joined_date'])); ?></p>
                    <input type="file" class="form-control form-control-sm mt-2" id="profileAvatar" name="profile_avatar" accept="image/*">
                    <small class="form-text text-muted d-block">Upload a new profile picture.</small>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><h5 class="mb-0">Account Information</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="profileName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="profileName" name="profile_name" value="<?php echo htmlspecialchars($current_user_data['name']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="profileEmail" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="profileEmail" name="profile_email" value="<?php echo htmlspecialchars($current_user_data['email']); ?>" readonly>
                            <small class="form-text text-muted">Email cannot be changed here. Contact support if needed.</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="profileBio" class="form-label">Short Bio / About Me</label>
                        <textarea class="form-control" id="profileBio" name="profile_bio" rows="4"><?php echo htmlspecialchars($current_user_data['bio']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Change Password</h5></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password">
                        </div>
                    </div>
                </div>
            </div>
             <div class="text-end mt-4">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
    </form>
</div>
<?php include 'includes/dashboard_footer.php'; ?>

