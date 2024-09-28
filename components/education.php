<!-- components/education.php -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>ข้อมูลการศึกษา</span>
        <button type="button" class="btn btn-add-education btn-sm" data-bs-toggle="modal" data-bs-target="#educationModal">
            เพิ่มข้อมูลการศึกษา
        </button>
    </div>
    <div class="card-body">
        <?php if (!empty($education)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ระดับการศึกษา</th>
                            <th>ประเทศ</th>
                            <th>มหาวิทยาลัย</th>
                            <th>รหัสนักศึกษา</th>
                            <th>คณะ</th>
                            <th>สาขา</th>
                            <th>ปีที่จบการศึกษา</th>
                            <th class="action-column">การกระทำ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($education as $edu): ?>
                            <?php
                            // แปลงระดับการศึกษาเป็นภาษาไทย
                            $degree_levels = [
                                'bachelor' => 'ปริญญาตรี',
                                'master' => 'ปริญญาโท',
                                'doctorate' => 'ปริญญาเอก'
                            ];
                            $degree_level_thai = $degree_levels[$edu['degree_level']] ?? $edu['degree_level'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($degree_level_thai) ?></td>
                                <td><?= htmlspecialchars($edu['country']) ?></td>
                                <td><?= htmlspecialchars($edu['university']) ?></td>
                                <td><?= htmlspecialchars($edu['student_id']) ?></td>
                                <td><?= htmlspecialchars($edu['faculty_name']) ?></td>
                                <td><?= htmlspecialchars($edu['major_name']) ?></td>
                                <td><?= htmlspecialchars($edu['graduation_year']) ?></td>
                                <td class="text-center action-column">
                                    <!-- ปุ่มแก้ไข -->
                                    <button type="button" class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#editEducationModal<?= htmlspecialchars($edu['education_id']) ?>">
                                        แก้ไข
                                    </button>
                                    <!-- ปุ่มลบ -->
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteEducation(<?= htmlspecialchars($edu['education_id']) ?>)">
                                        ลบ
                                    </button>
                                </td>
                            </tr>

                            <!-- รวมไฟล์ Modal สำหรับแก้ไขข้อมูลการศึกษา -->
                            <?php include 'modals/edit_education_modal.php'; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>ยังไม่มีข้อมูลการศึกษา</p>
        <?php endif; ?>
    </div>
</div>
