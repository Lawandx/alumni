<?php
// modals/edit_work_experience_modal.php
// ตรวจสอบว่ามีข้อมูล $work ถูกส่งมาหรือไม่
if (!isset($work)) {
    // ถ้าไม่มีข้อมูล $work, ไม่แสดง Modal
    return;
}
?>
<div class="modal fade" id="editWorkExperienceModal<?= htmlspecialchars($work['work_id']) ?>" tabindex="-1" aria-labelledby="editWorkExperienceModalLabel<?= htmlspecialchars($work['work_id']) ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="editWorkExperienceForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editWorkExperienceModalLabel<?= htmlspecialchars($work['work_id']) ?>">แก้ไขประสบการณ์การทำงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="work_id" value="<?= htmlspecialchars($work['work_id']) ?>">
                    <div class="mb-3">
                        <label for="position<?= htmlspecialchars($work['work_id']) ?>" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="position<?= htmlspecialchars($work['work_id']) ?>" name="position" value="<?= htmlspecialchars($work['position']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="company_name<?= htmlspecialchars($work['work_id']) ?>" class="form-label">ชื่อบริษัท</label>
                        <input type="text" class="form-control" id="company_name<?= htmlspecialchars($work['work_id']) ?>" name="company_name" value="<?= htmlspecialchars($work['company_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="start_date<?= htmlspecialchars($work['work_id']) ?>" class="form-label">เริ่มงาน</label>
                        <input type="date" class="form-control" id="start_date<?= htmlspecialchars($work['work_id']) ?>" name="start_date" value="<?= htmlspecialchars($work['start_date']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="end_date<?= htmlspecialchars($work['work_id']) ?>" class="form-label">สิ้นสุดงาน</label>
                        <input type="date" class="form-control" id="end_date<?= htmlspecialchars($work['work_id']) ?>" name="end_date" value="<?= htmlspecialchars($work['end_date']) ?>">
                        <small class="form-text text-muted">หากยังทำงานอยู่ให้เว้นว่างไว้</small>
                    </div>
                    <div class="mb-3">
                        <label for="country<?= htmlspecialchars($work['work_id']) ?>" class="form-label">ประเทศ</label>
                        <input type="text" class="form-control" id="country<?= htmlspecialchars($work['work_id']) ?>" name="country" value="<?= htmlspecialchars($work['country']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="house_number<?= htmlspecialchars($work['work_id']) ?>" class="form-label">หมายเลขบ้าน</label>
                        <input type="text" class="form-control" id="house_number<?= htmlspecialchars($work['work_id']) ?>" name="house_number" value="<?= htmlspecialchars($work['house_number']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="village<?= htmlspecialchars($work['work_id']) ?>" class="form-label">หมู่บ้าน</label>
                        <input type="text" class="form-control" id="village<?= htmlspecialchars($work['work_id']) ?>" name="village" value="<?= htmlspecialchars($work['village']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="Province<?= htmlspecialchars($work['work_id']) ?>" class="form-label">จังหวัด</label>
                        <input type="text" class="form-control" id="Province<?= htmlspecialchars($work['work_id']) ?>" name="Province" value="<?= htmlspecialchars($work['Province']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="District<?= htmlspecialchars($work['work_id']) ?>" class="form-label">อำเภอ</label>
                        <input type="text" class="form-control" id="District<?= htmlspecialchars($work['work_id']) ?>" name="District" value="<?= htmlspecialchars($work['District']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="sub_district<?= htmlspecialchars($work['work_id']) ?>" class="form-label">ตำบล</label>
                        <input type="text" class="form-control" id="sub_district<?= htmlspecialchars($work['work_id']) ?>" name="sub_district" value="<?= htmlspecialchars($work['sub_district']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="zip_code<?= htmlspecialchars($work['work_id']) ?>" class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" class="form-control" id="zip_code<?= htmlspecialchars($work['work_id']) ?>" name="zip_code" value="<?= htmlspecialchars($work['zip_code']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="work_phone<?= htmlspecialchars($work['work_id']) ?>" class="form-label">เบอร์โทรศัพท์ที่ทำงาน</label>
                        <input type="text" class="form-control" id="work_phone<?= htmlspecialchars($work['work_id']) ?>" name="work_phone" value="<?= htmlspecialchars($work['work_phone']) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>

