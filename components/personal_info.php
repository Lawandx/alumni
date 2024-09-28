<!-- components/personal_info.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ข้อมูลส่วนบุคคล</span>
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPersonalInfoModal">
            แก้ไขข้อมูล
        </button>
    </div>
    <div class="card-body">
        <div class="text-center mb-4">
            <?php
            $baseUrl = '/Work/assets/images/profile_pictures/'; 

            if (!empty($person['photo'])):
                // ตรวจสอบว่า photo เป็น URL หรือ path ของไฟล์
                if (filter_var($person['photo'], FILTER_VALIDATE_URL)) {
                    $photo_src = htmlspecialchars($person['photo']);
                } else {
                    // ใช้ basename เพื่อป้องกันการโจมตีแบบ Directory Traversal
                    $photo_src = $baseUrl . htmlspecialchars(basename($person['photo']));
                }
            ?>
                <img src="<?= $photo_src ?>" alt="รูปภาพ" class="profile-picture mb-3">
            <?php else: ?>
                <img src="/Work/assets/images/default-avatar.png" alt="รูปภาพ" class="profile-picture mb-3">
            <?php endif; ?>
        </div>
        <!-- ฟิลด์ข้อมูลอื่นๆ -->
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                คำนำหน้า:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['title']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                ชื่อ:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['first_name']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                นามสกุล:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['last_name']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                วันเกิด:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['birth_date']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                เบอร์โทรเคลื่อนที่:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['phone_personal']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                อีเมล:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['email']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                Line ID:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['line_id']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                Facebook:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['facebook']) ?>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6 info-label">
                ความสามารถพิเศษ:
            </div>
            <div class="col-md-6">
                <?= htmlspecialchars($person['special_ability']) ?>
            </div>
        </div>
    </div>
</div>