<!-- components/work_experience.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ประสบการณ์การทำงาน</span>
        <button type="button" class="btn btn-add-workexperience btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkExperienceModal">
            เพิ่มประสบการณ์การทำงาน
        </button>
    </div>
    <div class="card-body">
        <?php if (count($workexperience) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ตำแหน่ง</th>
                            <th>ชื่อบริษัท</th>
                            <th>เริ่มงาน</th>
                            <th>สิ้นสุดงาน</th>
                            <th>ประเทศ</th>
                            <th>ที่อยู่</th>
                            <th>เบอร์โทรศัพท์</th>
                            <th class="action-column">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workexperience as $work): ?>
                            <tr>
                                <td><?= htmlspecialchars($work['position'] ?? '') ?></td>
                                <td><?= htmlspecialchars($work['company_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($work['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($work['end_date'] ?? 'ปัจจุบัน') ?></td>
                                <td><?= htmlspecialchars($work['country'] ?? '') ?></td>
                                <td>
                                    <?= htmlspecialchars($work['house_number'] ?? '') ?>,
                                    <?= htmlspecialchars($work['village'] ?? '') ?>,
                                    <?= htmlspecialchars($work['sub_district'] ?? '') ?>,
                                    <?= htmlspecialchars($work['District'] ?? '') ?>,
                                    <?= htmlspecialchars($work['Province'] ?? '') ?>,
                                    <?= htmlspecialchars($work['zip_code'] ?? '') ?>
                                </td>
                                <td><?= htmlspecialchars($work['work_phone'] ?? '') ?></td>
                                <td class="text-center action-column">
                                    <div class="d-flex justify-content-center">
                                        <!-- ปุ่มแก้ไข -->
                                        <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editWorkExperienceModal<?= htmlspecialchars($work['work_id']) ?>">
                                            แก้ไข
                                        </button>
                                        <!-- ปุ่มลบ -->
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteWorkExperience(<?= htmlspecialchars($work['work_id']) ?>)">
                                            ลบ
                                        </button>
                                    </div>
                                </td>
                                <!-- รวมไฟล์ Modal สำหรับแก้ไขประสบการณ์การทำงาน -->
                                <?php include 'modals/edit_work_experience_modal.php'; ?>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>ไม่มีข้อมูลประสบการณ์การทำงาน</p>
        <?php endif; ?>
    </div>
</div>