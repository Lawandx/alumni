<!-- components/student_award.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>รางวัลนักเรียน</span>
        <button type="button" class="btn btn-add-studentaward btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentAwardModal">
            เพิ่มรางวัลนักเรียน
        </button>
    </div>
    <div class="card-body">
        <?php if (count($studentaward) > 0): ?>
            <div class="table-responsive">
                <table class="table  table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ชื่อรางวัล</th>
                            <th>วันที่ได้รับรางวัล</th>
                            <th>คณะ</th>
                            <th>สาขาวิชา</th>
                            <th>องค์กรที่มอบรางวัล</th>
                            <th>รายละเอียด</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($studentaward as $award): ?>
                            <tr>
                                <td><?= htmlspecialchars($award['student_award_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['student_award_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['faculty'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['major'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['student_awarding_organization'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['student_description'] ?? '') ?></td>
                                <td class="text-center action-column">
                                    <!-- ปุ่มแก้ไข -->
                                    <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editStudentAwardModal<?= htmlspecialchars($award['award_id']) ?>">
                                        แก้ไข
                                    </button>
                                    <!-- ปุ่มลบ -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteStudentAward(<?= htmlspecialchars($award['award_id']) ?>)">
                                        ลบ
                                    </button>
                                </td>
                            </tr>

                            <!-- รวมไฟล์ Modal สำหรับแก้ไขรางวัลนักเรียน -->
                            <?php include 'modals/edit_student_award_modal.php'; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>ไม่มีข้อมูลรางวัลนักเรียน</p>
        <?php endif; ?>
    </div>
</div>
