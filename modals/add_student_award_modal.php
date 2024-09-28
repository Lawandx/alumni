<!-- modals/add_student_award_modal.php -->
<div class="modal fade" id="addStudentAwardModal" tabindex="-1" aria-labelledby="addStudentAwardModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addStudentAwardForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentAwardModalLabel">เพิ่มรางวัลนักเรียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
                    <div class="mb-3">
                        <label for="student_award_name" class="form-label">ชื่อรางวัล</label>
                        <input type="text" class="form-control" id="student_award_name" name="student_award_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_award_date" class="form-label">วันที่ได้รับรางวัล</label>
                        <input type="date" class="form-control" id="student_award_date" name="student_award_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="faculty" class="form-label">คณะ</label>
                        <input type="text" class="form-control" id="faculty" name="faculty" required>
                    </div>
                    <div class="mb-3">
                        <label for="major" class="form-label">สาขาวิชา</label>
                        <input type="text" class="form-control" id="major" name="major" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_awarding_organization" class="form-label">องค์กรที่มอบรางวัล</label>
                        <input type="text" class="form-control" id="student_awarding_organization" name="student_awarding_organization" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="student_description" name="student_description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success">เพิ่มรางวัลนักเรียน</button>
                </div>
            </form>
        </div>
    </div>
</div>
