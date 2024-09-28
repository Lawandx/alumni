<!-- modals/edit_address_modal.php -->

<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAddressForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddressModalLabel">แก้ไขข้อมูลที่อยู่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">

                    <div class="mb-3">
                        <label for="address_type" class="form-label">ประเภทที่อยู่</label>
                        <select class="form-control" id="address_type" name="address_type" required>
                            <option value="permanent" <?= $address['address_type'] === 'permanent' ? 'selected' : '' ?>>ที่อยู่บ้าน</option>
                            <option value="contact" <?= $address['address_type'] === 'contact' ? 'selected' : '' ?>>ที่อยู่ติดต่อ</option>
                            <option value="work" <?= $address['address_type'] === 'work' ? 'selected' : '' ?>>ที่ทำงาน</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="house_number" class="form-label">บ้านเลขที่</label>
                        <input type="text" class="form-control" id="house_number" name="house_number" value="<?= htmlspecialchars($address['house_number']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="village" class="form-label">หมู่บ้าน</label>
                        <input type="text" class="form-control" id="village" name="village" value="<?= htmlspecialchars($address['village']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="province" class="form-label">จังหวัด</label>
                        <select class="form-control" id="province" name="province" required>
                            <option value="">เลือกจังหวัด</option>
                            <?php
                            // ดึงข้อมูลจังหวัดจากฐานข้อมูล
                            $stmt = $pdo->query("SELECT id, name_in_thai FROM provinces ORDER BY name_in_thai ASC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($row['id'] == $address['province']) ? 'selected' : '';
                                echo "<option value=\"{$row['id']}\" {$selected}>{$row['name_in_thai']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="district" class="form-label">อำเภอ</label>
                        <select class="form-control" id="district" name="district" required>
                            <option value="">เลือกอำเภอ</option>
                            <?php
                            // ดึงข้อมูลอำเภอจากฐานข้อมูลตามจังหวัดที่เลือก
                            if ($address['province']) {
                                $stmt = $pdo->prepare("SELECT id, name_in_thai FROM districts WHERE province_id = :province_id ORDER BY name_in_thai ASC");
                                $stmt->bindParam(':province_id', $address['province'], PDO::PARAM_INT);
                                $stmt->execute();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($row['id'] == $address['district']) ? 'selected' : '';
                                    echo "<option value=\"{$row['id']}\" {$selected}>{$row['name_in_thai']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="sub_district" class="form-label">ตำบล</label>
                        <select class="form-control" id="sub_district" name="subdistrict" required>
                            <option value="">เลือกตำบล</option>
                            <?php
                            // ดึงข้อมูลตำบลจากฐานข้อมูลตามอำเภอที่เลือก
                            if ($address['district']) {
                                $stmt = $pdo->prepare("SELECT id, name_in_thai FROM subdistricts WHERE district_id = :district_id ORDER BY name_in_thai ASC");
                                $stmt->bindParam(':district_id', $address['district'], PDO::PARAM_INT);
                                $stmt->execute();
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($row['id'] == $address['sub_district']) ? 'selected' : '';
                                    echo "<option value=\"{$row['id']}\" {$selected}>{$row['name_in_thai']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="zip_code" class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?= htmlspecialchars($address['zip_code']) ?>" required readonly>
                    </div>

                    <div class="mb-3">
                        <label for="phone_address" class="form-label">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control" id="phone_address" name="phone_address" value="<?= htmlspecialchars($address['phone_address']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="is_same_as_permanent" class="form-label">ที่อยู่นี้เหมือนกับที่อยู่ถาวรหรือไม่</label>
                        <select class="form-control" id="is_same_as_permanent" name="is_same_as_permanent">
                            <option value="1" <?= $address['is_same_as_permanent'] ? 'selected' : '' ?>>ใช่</option>
                            <option value="0" <?= !$address['is_same_as_permanent'] ? 'selected' : '' ?>>ไม่ใช่</option>
                        </select>
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

<!-- JavaScript สำหรับจัดการ Dropdowns และ Toast -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province');
    const districtSelect = document.getElementById('district');
    const subdistrictSelect = document.getElementById('sub_district');
    const zipCodeInput = document.getElementById('zip_code');
    const editAddressForm = document.getElementById('editAddressForm');

    // Function to show Toast
    function showToast(message, isSuccess = true) {
        const toastNotification = document.getElementById('toastNotification');
        const toastBody = document.getElementById('toastBody');
        toastBody.textContent = message;
        const toast = new bootstrap.Toast(toastNotification);
        toast.show();
    }

    // เมื่อเลือกจังหวัดให้แสดงอำเภอที่เกี่ยวข้อง
    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>';
        subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
        zipCodeInput.value = '';

        if (provinceId) {
            fetch('get_districts.php?province_id=' + provinceId)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        data.forEach(district => {
                            let option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name_in_thai;
                            districtSelect.appendChild(option);
                        });
                    } else {
                        showToast(data.error, false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาดในการดึงข้อมูลอำเภอ', false);
                });
        }
    });

    // เมื่อเลือกอำเภอให้แสดงตำบลที่เกี่ยวข้อง
    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        subdistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
        zipCodeInput.value = '';

        if (districtId) {
            fetch('get_subdistricts.php?district_id=' + districtId)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        data.forEach(subdistrict => {
                            let option = document.createElement('option');
                            option.value = subdistrict.id;
                            option.textContent = subdistrict.name_in_thai;
                            subdistrictSelect.appendChild(option);
                        });
                    } else {
                        showToast(data.error, false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาดในการดึงข้อมูลตำบล', false);
                });
        }
    });

    // เมื่อเลือกตำบลให้แสดงรหัสไปรษณีย์ที่เกี่ยวข้อง
    subdistrictSelect.addEventListener('change', function() {
        const subdistrictId = this.value;
        zipCodeInput.value = '';

        if (subdistrictId) {
            fetch('get_zipcode.php?subdistrict_id=' + subdistrictId)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        zipCodeInput.value = data.zip_code;
                    } else {
                        showToast(data.error, false);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาดในการดึงข้อมูลรหัสไปรษณีย์', false);
                });
        }
    });

    // จัดการการส่งฟอร์มผ่าน AJAX
    editAddressForm.addEventListener('submit', function(e) {
        e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

        const formData = new FormData(editAddressForm);

        fetch('edit_address.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showToast(data.message, true);
                // ปิด Modal หลังจากแสดง Toast
                const editAddressModal = bootstrap.Modal.getInstance(document.getElementById('editAddressModal'));
                editAddressModal.hide();

                // รีเฟรชหน้าเพื่อแสดงข้อมูลใหม่ (ถ้าต้องการ)
                setTimeout(() => {
                    window.location.href = `details.php?id=<?= htmlspecialchars($person_id) ?>&status=success`;
                }, 1000); // ให้ Toast แสดงเป็นเวลา 1 วินาที
            } else {
                showToast(data.message, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('เกิดข้อผิดพลาดในการบันทึกข้อมูล', false);
        });
    });
});
</script>
