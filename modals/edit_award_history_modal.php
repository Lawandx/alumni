<?php
// modals/edit_award_history_modal.php
if (!isset($award)) {
    return;
}
?>
<div class="modal fade" id="editAwardHistoryModal<?= htmlspecialchars($award['award_id']) ?>" tabindex="-1" aria-labelledby="editAwardHistoryModalLabel<?= htmlspecialchars($award['award_id']) ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="editAwardHistoryForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAwardHistoryModalLabel<?= htmlspecialchars($award['award_id']) ?>">แก้ไขประวัติการรับรางวัล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="award_id" value="<?= htmlspecialchars($award['award_id']) ?>">
                    <div class="mb-3">
                        <label for="award_name<?= htmlspecialchars($award['award_id']) ?>" class="form-label">ชื่อรางวัล</label>
                        <input type="text" class="form-control" id="award_name<?= htmlspecialchars($award['award_id']) ?>" name="award_name" value="<?= htmlspecialchars($award['award_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="award_date<?= htmlspecialchars($award['award_id']) ?>" class="form-label">วันที่ได้รับรางวัล</label>
                        <input type="date" class="form-control" id="award_date<?= htmlspecialchars($award['award_id']) ?>" name="award_date" value="<?= htmlspecialchars($award['award_date'] ?? '') ?>" required>

                    </div>
                    <div class="mb-3">
                        <label for="awarding_organization<?= htmlspecialchars($award['award_id']) ?>" class="form-label">องค์กรที่มอบรางวัล</label>
                        <input type="text" class="form-control" id="awarding_organization<?= htmlspecialchars($award['award_id']) ?>" name="awarding_organization" value="<?= htmlspecialchars($award['awarding_organization']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description<?= htmlspecialchars($award['award_id']) ?>" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="description<?= htmlspecialchars($award['award_id']) ?>" name="description"><?= htmlspecialchars($award['description']) ?></textarea>
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
