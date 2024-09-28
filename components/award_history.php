<!-- components/award_history.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ประวัติการรับรางวัล</span>
        <button type="button" class="btn btn-add-awardhistory btn-sm" data-bs-toggle="modal" data-bs-target="#addAwardHistoryModal">
            เพิ่มประวัติการรับรางวัล
        </button>
    </div>
    <div class="card-body">
        <?php if (count($awardhistory) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ชื่อรางวัล</th>
                            <th>วันที่ได้รับรางวัล</th>
                            <th>องค์กรที่มอบรางวัล</th>
                            <th>รายละเอียด</th>
                            <th>การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($awardhistory as $award): ?>
                            <tr>
                                <td><?= htmlspecialchars($award['award_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['award_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['awarding_organization'] ?? '') ?></td>
                                <td><?= htmlspecialchars($award['description'] ?? '') ?></td>
                                <td>
                                    <!-- ปุ่มแก้ไข -->
                                    <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editAwardHistoryModal<?= htmlspecialchars($award['award_id']) ?>">
                                        แก้ไข
                                    </button>
                                    <!-- ปุ่มลบ -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAwardHistory(<?= htmlspecialchars($award['award_id']) ?>)">
                                        ลบ
                                    </button>
                                </td>
                            </tr>

                            <!-- รวมไฟล์ Modal สำหรับแก้ไขประวัติการรับรางวัล -->
                            <?php include 'modals/edit_award_history_modal.php'; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>ไม่มีข้อมูลประวัติการรับรางวัล</p>
        <?php endif; ?>
    </div>
</div>
