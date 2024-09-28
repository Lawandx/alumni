<!-- modals/add_work_experience_modal.php -->
<div class="modal fade" id="addWorkExperienceModal" tabindex="-1" aria-labelledby="addWorkExperienceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addWorkExperienceForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWorkExperienceModalLabel">เพิ่มประสบการณ์การทำงาน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
                    
                    <!-- ฟิลด์ตำแหน่ง -->
                    <div class="mb-3">
                        <label for="position" class="form-label">ตำแหน่ง</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>
                    
                    <!-- ฟิลด์ชื่อบริษัท -->
                    <div class="mb-3">
                        <label for="company_name" class="form-label">ชื่อบริษัท</label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required>
                    </div>
                    
                    <!-- ฟิลด์เริ่มงาน -->
                    <div class="mb-3">
                        <label for="start_date" class="form-label">เริ่มงาน</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    
                    <!-- ฟิลด์สิ้นสุดงาน -->
                    <div class="mb-3">
                        <label for="end_date" class="form-label">สิ้นสุดงาน</label>
                        <input type="date" class="form-control" id="end_date" name="end_date">
                        <small class="form-text text-muted">หากยังทำงานอยู่ให้เว้นว่างไว้</small>
                    </div>
                    
                    <!-- ฟิลด์ประเทศ -->
                    <div class="mb-3">
                        <label for="country_select" class="form-label">ประเทศ</label>
                        <select class="form-select" id="country_select" name="country_select" required>
                            <option value="">เลือกประเทศ</option>
                            <option value="Thailand">ประเทศไทย</option>
                            <option value="Other">ประเทศอื่นๆ</option>
                        </select>
                    </div>
                    
                    <!-- ฟิลด์ชื่อประเทศ (เฉพาะเมื่อเลือกเป็นประเทศอื่น) -->
                    <div class="mb-3 d-none" id="other_country_div">
                        <label for="other_country" class="form-label">ชื่อประเทศ</label>
                        <input type="text" class="form-control" id="other_country" name="other_country">
                    </div>
                    
                    <!-- ฟิลด์หมายเลขบ้าน -->
                    <div class="mb-3">
                        <label for="house_number" class="form-label">หมายเลขบ้าน</label>
                        <input type="text" class="form-control" id="house_number" name="house_number">
                    </div>
                    
                    <!-- ฟิลด์หมู่บ้าน -->
                    <div class="mb-3">
                        <label for="village" class="form-label">หมู่บ้าน</label>
                        <input type="text" class="form-control" id="village" name="village">
                    </div>
                    
                    <!-- ฟิลด์จังหวัด (สำหรับประเทศไทย) -->
                    <div class="mb-3" id="province_select_div">
                        <label for="province_select" class="form-label">จังหวัด</label>
                        <select class="form-select" id="province_select" name="province_id" disabled>
                            <option value="">เลือกจังหวัด</option>
                            <!-- Provinces จะถูกโหลดผ่าน JavaScript -->
                        </select>
                    </div>
                    
                    <!-- ฟิลด์จังหวัด (สำหรับประเทศอื่นๆ) -->
                    <div class="mb-3 d-none" id="province_other_div">
                        <label for="province_other" class="form-label">จังหวัด</label>
                        <input type="text" class="form-control" id="province_other" name="province_other">
                    </div>
                    
                    <!-- ฟิลด์อำเภอ (สำหรับประเทศไทย) -->
                    <div class="mb-3" id="district_select_div">
                        <label for="district_select" class="form-label">อำเภอ</label>
                        <select class="form-select" id="district_select" name="district_id" disabled>
                            <option value="">เลือกอำเภอ</option>
                            <!-- Districts จะถูกโหลดผ่าน JavaScript -->
                        </select>
                    </div>
                    
                    <!-- ฟิลด์อำเภอ (สำหรับประเทศอื่นๆ) -->
                    <div class="mb-3 d-none" id="district_other_div">
                        <label for="district_other" class="form-label">อำเภอ</label>
                        <input type="text" class="form-control" id="district_other" name="district_other">
                    </div>
                    
                    <!-- ฟิลด์ตำบล (สำหรับประเทศไทย) -->
                    <div class="mb-3" id="sub_district_select_div">
                        <label for="sub_district_select" class="form-label">ตำบล</label>
                        <select class="form-select" id="sub_district_select" name="sub_district_id" disabled>
                            <option value="">เลือกตำบล</option>
                            <!-- Subdistricts จะถูกโหลดผ่าน JavaScript -->
                        </select>
                    </div>
                    
                    <!-- ฟิลด์ตำบล (สำหรับประเทศอื่นๆ) -->
                    <div class="mb-3 d-none" id="sub_district_other_div">
                        <label for="sub_district_other" class="form-label">ตำบล</label>
                        <input type="text" class="form-control" id="sub_district_other" name="sub_district_other">
                    </div>
                    
                    <!-- ฟิลด์รหัสไปรษณีย์ (สำหรับประเทศไทย) -->
                    <div class="mb-3" id="zip_code_select_div">
                        <label for="zip_code_select" class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" class="form-control" id="zip_code_select" name="zip_code_select" readonly>
                    </div>
                    
                    <!-- ฟิลด์รหัสไปรษณีย์ (สำหรับประเทศอื่นๆ) -->
                    <div class="mb-3 d-none" id="zip_code_other_div">
                        <label for="zip_code_other" class="form-label">รหัสไปรษณีย์</label>
                        <input type="text" class="form-control" id="zip_code_other" name="zip_code_other">
                    </div>
                    
                    <!-- ฟิลด์เบอร์โทรศัพท์ที่ทำงาน -->
                    <div class="mb-3">
                        <label for="work_phone" class="form-label">เบอร์โทรศัพท์ที่ทำงาน</label>
                        <input type="text" class="form-control" id="work_phone" name="work_phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success">เพิ่มประสบการณ์การทำงาน</button>
                </div>
            </form>
        </div>
    </div>
</div>
