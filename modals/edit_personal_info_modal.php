<!-- modals/edit_personal_info_modal.php -->
<div class="modal fade" id="editPersonalInfoModal" tabindex="-1" aria-labelledby="editPersonalInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPersonalInfoForm" action="edit_personal_info.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPersonalInfoModalLabel">แก้ไขข้อมูลส่วนบุคคล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person['person_id']) ?>">
                    
                    <!-- ฟอร์มข้อมูลอื่นๆ -->
                    <div class="mb-3">
                        <label for="first_name" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($person['first_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($person['last_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">คำนำหน้า</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($person['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="birth_date" class="form-label">วันเกิด</label>
                        <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?= htmlspecialchars($person['birth_date']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone_personal" class="form-label">เบอร์โทรเคลื่อนที่</label>
                        <input type="text" class="form-control" id="phone_personal" name="phone_personal" value="<?= htmlspecialchars($person['phone_personal']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">อีเมล</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($person['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="line_id" class="form-label">Line ID</label>
                        <input type="text" class="form-control" id="line_id" name="line_id" value="<?= htmlspecialchars($person['line_id']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="facebook" class="form-label">Facebook</label>
                        <input type="text" class="form-control" id="facebook" name="facebook" value="<?= htmlspecialchars($person['facebook']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="special_ability" class="form-label">ความสามารถพิเศษ</label>
                        <textarea class="form-control" id="special_ability" name="special_ability"><?= htmlspecialchars($person['special_ability']) ?></textarea>
                    </div>
                    
                    <!-- ตัวเลือกการเปลี่ยนรูปภาพ -->
                    <div class="mb-3">
                        <label class="form-label">เปลี่ยนรูปภาพโปรไฟล์</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="photo_option" id="photoUrlOption" value="url" checked>
                            <label class="form-check-label" for="photoUrlOption">
                                ใช้ URL รูปภาพ
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="photo_option" id="photoUploadOption" value="upload">
                            <label class="form-check-label" for="photoUploadOption">
                                อัปโหลดจากเครื่อง
                            </label>
                        </div>
                    </div>
                    
                    <!-- Input สำหรับ URL รูปภาพ -->
                    <div class="mb-3" id="photoUrlInput">
                        <label for="photo_url" class="form-label">URL รูปภาพ</label>
                        <input type="url" class="form-control" id="photo_url" name="photo_url" placeholder="https://example.com/image.jpg">
                    </div>
                    
                    <!-- Input สำหรับอัปโหลดรูปภาพ -->
                    <div class="mb-3 d-none" id="photoUploadInput">
                        <label for="photo_upload" class="form-label">อัปโหลดรูปภาพ</label>
                        <input type="file" class="form-control" id="photo_upload" name="photo_upload" accept="image/*">
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

<script>
// เพิ่ม JavaScript เพื่อจัดการการแสดงผล Input ตามการเลือก
document.addEventListener('DOMContentLoaded', function () {
    const photoUrlOption = document.getElementById('photoUrlOption');
    const photoUploadOption = document.getElementById('photoUploadOption');
    const photoUrlInput = document.getElementById('photoUrlInput');
    const photoUploadInput = document.getElementById('photoUploadInput');

    function togglePhotoInput() {
        if (photoUrlOption.checked) {
            photoUrlInput.classList.remove('d-none');
            photoUploadInput.classList.add('d-none');
        } else if (photoUploadOption.checked) {
            photoUrlInput.classList.add('d-none');
            photoUploadInput.classList.remove('d-none');
        }
    }

    // เรียกใช้ฟังก์ชันเมื่อโหลดหน้า
    togglePhotoInput();

    // เรียกใช้ฟังก์ชันเมื่อมีการเปลี่ยนแปลงตัวเลือก
    photoUrlOption.addEventListener('change', togglePhotoInput);
    photoUploadOption.addEventListener('change', togglePhotoInput);
});
</script>
