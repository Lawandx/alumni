<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New User</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* สีเทาอ่อน */
        }
        .add-user-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff; /* สีขาว */
            border: 1px solid #ced4da;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .add-user-container h3 {
            color: #fd7e14; /* สีส้ม */
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-orange {
            background-color: #fd7e14; /* สีส้ม */
            color: #ffffff;
        }
        .btn-orange:hover {
            background-color: #e06b0e; /* สีส้มเข้ม */
        }
    </style>
</head>
<body>

<div class="add-user-container">
    <h3>Add New User</h3>
    <form action="add_user.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="access_level" class="form-label">Access Level</label>
            <select class="form-control" id="access_level" name="access_level" required>
                <option value="admin">Admin</option>
                <option value="faculty">Faculty</option>
                <option value="department">Department</option>
                <option value="major">Major</option>
            </select>
        </div>
        <button type="submit" class="btn btn-orange w-100">Add User</button>
    </form>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
