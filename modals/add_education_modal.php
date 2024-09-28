<!-- modals/add_education_modal.php -->
<div class="modal fade" id="educationModal" tabindex="-1" aria-labelledby="educationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="educationForm" action="add_education.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="educationModalLabel">เพิ่มข้อมูลการศึกษา</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="degree_level" class="form-label">ระดับการศึกษา</label>
                        <select class="form-control" id="degree_level" name="degree_level" required>
                            <option value="bachelor">ปริญญาตรี</option>
                            <option value="master">ปริญญาโท</option>
                            <option value="doctorate">ปริญญาเอก</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">ประเทศ</label>
                        <input type="text" class="form-control" id="country" name="country" required>
                    </div>
                    <div class="mb-3">
                        <label for="university" class="form-label">มหาวิทยาลัย</label>
                        <input type="text" class="form-control" id="university" name="university" required>
                    </div>
                    <div class="mb-3">
                        <label for="student_id" class="form-label">รหัสนักศึกษา</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" required>
                    </div>
                    <div class="mb-3">
                        <label for="faculty_name" class="form-label">คณะ</label>
                        <input type="text" class="form-control" id="faculty_name" name="faculty_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="major_name" class="form-label">สาขา</label>
                        <input type="text" class="form-control" id="major_name" name="major_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="graduation_year" class="form-label">ปีที่จบการศึกษา (ค.ศ.)</label>
                        <input type="text" class="form-control" id="graduation_year" name="graduation_year" pattern="\d{4}" maxlength="4" required>
                    </div>
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>
