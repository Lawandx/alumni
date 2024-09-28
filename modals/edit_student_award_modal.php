<?php
// modals/edit_student_award_modal.php
// ตรวจสอบว่ามีข้อมูล $award ถูกส่งมาหรือไม่
if (!isset($award)) {
    // ถ้าไม่มีข้อมูล $award, ไม่แสดง Modal
    return;
}
?>
<div class="modal fade" id="editStudentAwardModal<?= htmlspecialchars($award['award_id']) ?>" tabindex="-1" aria-labelledby="editStudentAwardModalLabel<?= htmlspecialchars($award['award_id']) ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="editStudentAwardForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStudentAwardModalLabel<?= htmlspecialchars($award['award_id']) ?>">แก้ไขรางวัลนักเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="award_id" value="<?= htmlspecialchars($award['award_id']) ?>">
                    <div class="mb-3">
                        <label for="student_award_name<?= htmlspecialchars($award['award_id']) ?>" class="form-label">ชื่อรางวัล</label>
                        <input type="text" class="form-control" id="student_award_name<?= htmlspecialchars($award['award_id']) ?>" name="student_award_name" value="<?= htmlspecialchars($award['student_award_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_award_date<?= htmlspecialchars($award['award_id']) ?>" class="form-label">วันที่ได้รับรางวัล</label>
                        <input type="date" class="form-control" id="student_award_date<?= htmlspecialchars($award['award_id']) ?>" name="student_award_date" value="<?= htmlspecialchars($award['student_award_date'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="faculty<?= htmlspecialchars($award['award_id']) ?>" class="form-label">คณะ</label>
                        <input type="text" class="form-control" id="faculty<?= htmlspecialchars($award['award_id']) ?>" name="faculty" value="<?= htmlspecialchars($award['faculty']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="major<?= htmlspecialchars($award['award_id']) ?>" class="form-label">สาขาวิชา</label>
                        <input type="text" class="form-control" id="major<?= htmlspecialchars($award['award_id']) ?>" name="major" value="<?= htmlspecialchars($award['major']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_awarding_organization<?= htmlspecialchars($award['award_id']) ?>" class="form-label">องค์กรที่มอบรางวัล</label>
                        <input type="text" class="form-control" id="student_awarding_organization<?= htmlspecialchars($award['award_id']) ?>" name="student_awarding_organization" value="<?= htmlspecialchars($award['student_awarding_organization']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_description<?= htmlspecialchars($award['award_id']) ?>" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="student_description<?= htmlspecialchars($award['award_id']) ?>" name="student_description"><?= htmlspecialchars($award['student_description']) ?></textarea>
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
