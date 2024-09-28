<!-- modals/edit_education_modal.php -->
<?php foreach ($education as $edu): ?>
    <div class="modal fade" id="editEducationModal<?= htmlspecialchars($edu['education_id']) ?>" tabindex="-1" aria-labelledby="editEducationModalLabel<?= htmlspecialchars($edu['education_id']) ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editEducationForm<?= htmlspecialchars($edu['education_id']) ?>" action="edit_education.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editEducationModalLabel<?= htmlspecialchars($edu['education_id']) ?>">แก้ไขข้อมูลการศึกษา</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="education_id" value="<?= htmlspecialchars($edu['education_id']) ?>">
                        <!-- ฟิลด์ข้อมูลต่างๆ -->
                        <div class="mb-3">
                            <label for="degree_level<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">ระดับการศึกษา</label>
                            <select class="form-control" id="degree_level<?= htmlspecialchars($edu['education_id']) ?>" name="degree_level" required>
                                <option value="bachelor" <?= $edu['degree_level'] === 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                                <option value="master" <?= $edu['degree_level'] === 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                                <option value="doctorate" <?= $edu['degree_level'] === 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                            </select>
                        </div>
                        <!-- ฟิลด์อื่นๆ -->
                        <div class="mb-3">
                            <label for="country<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">ประเทศ</label>
                            <input type="text" class="form-control" id="country<?= htmlspecialchars($edu['education_id']) ?>" name="country" value="<?= htmlspecialchars($edu['country']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="university<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">มหาวิทยาลัย</label>
                            <input type="text" class="form-control" id="university<?= htmlspecialchars($edu['education_id']) ?>" name="university" value="<?= htmlspecialchars($edu['university']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="student_id<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">รหัสนักศึกษา</label>
                            <input type="text" class="form-control" id="student_id<?= htmlspecialchars($edu['education_id']) ?>" name="student_id" value="<?= htmlspecialchars($edu['student_id']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="faculty_name<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">คณะ</label>
                            <input type="text" class="form-control" id="faculty_name<?= htmlspecialchars($edu['education_id']) ?>" name="faculty_name" value="<?= htmlspecialchars($edu['faculty_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="major_name<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">สาขา</label>
                            <input type="text" class="form-control" id="major_name<?= htmlspecialchars($edu['education_id']) ?>" name="major_name" value="<?= htmlspecialchars($edu['major_name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="graduation_year<?= htmlspecialchars($edu['education_id']) ?>" class="form-label">ปีที่จบการศึกษา (ค.ศ.)</label>
                            <input type="text" class="form-control" id="graduation_year<?= htmlspecialchars($edu['education_id']) ?>" name="graduation_year" value="<?= htmlspecialchars($edu['graduation_year']) ?>" pattern="\d{4}" maxlength="4" required>
                        </div>
                        <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
