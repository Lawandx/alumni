<!-- components/address.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ข้อมูลที่อยู่</span>
        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editAddressModal">
            แก้ไขข้อมูลที่อยู่
        </button>
    </div>
    <div class="card-body">
        <?php if ($address): ?>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    ประเภทที่อยู่:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($type_label) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    บ้านเลขที่:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['house_number']) ?>
                </div>
            </div>
            <?php if (!empty($address['village'])): ?>
                <div class="row mb-3">
                    <div class="col-md-4 info-label">
                        หมู่บ้าน:
                    </div>
                    <div class="col-md-8">
                        <?= htmlspecialchars($address['village']) ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    ตำบล:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['subdistrict_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    อำเภอ:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['district_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    จังหวัด:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['province_name']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    รหัสไปรษณีย์:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['zip_code']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    เบอร์โทรศัพท์:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['phone_address']) ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4 info-label">
                    ที่อยู่นี้เหมือนกับที่อยู่ถาวรหรือไม่:
                </div>
                <div class="col-md-8">
                    <?= htmlspecialchars($address['is_same_as_permanent'] ? 'ใช่' : 'ไม่ใช่') ?>
                </div>
            </div>
        <?php else: ?>
            <p>ไม่มีข้อมูลที่อยู่</p>
        <?php endif; ?>
    </div>
</div>
