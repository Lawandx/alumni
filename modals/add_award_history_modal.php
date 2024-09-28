<!-- modals/add_award_history_modal.php -->
<div class="modal fade" id="addAwardHistoryModal" tabindex="-1" aria-labelledby="addAwardHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addAwardHistoryForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAwardHistoryModalLabel">เพิ่มประวัติการรับรางวัล</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="person_id" value="<?= htmlspecialchars($person_id) ?>">
                    <div class="mb-3">
                        <label for="award_name" class="form-label">ชื่อรางวัล</label>
                        <input type="text" class="form-control" id="award_name" name="award_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="award_date" class="form-label">วันที่ได้รับรางวัล</label>
                        <input type="date" class="form-control" id="award_date" name="award_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="awarding_organization" class="form-label">องค์กรที่มอบรางวัล</label>
                        <input type="text" class="form-control" id="awarding_organization" name="awarding_organization" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">รายละเอียด</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success">เพิ่มประวัติการรับรางวัล</button>
                </div>
            </form>
        </div>
    </div>
</div>
