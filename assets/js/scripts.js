// assets/js/scripts.js

// ฟังก์ชันสำหรับแสดง Toast ในสโคป Global
function showToast(message) {
    const toastBody = document.getElementById('toastBody');
    const toastElement = document.getElementById('toastNotification');

    if (toastBody && toastElement) {
        toastBody.innerText = message;
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 }); // แสดง Toast เป็นเวลา 5 วินาที
        toast.show();
    } else {
        console.error('ไม่พบองค์ประกอบของ Toast ใน HTML');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // ฟังก์ชันสำหรับจัดการแก้ไขข้อมูลส่วนบุคคล
    const editPersonalInfoForm = document.getElementById('editPersonalInfoForm');
    if (editPersonalInfoForm) {
        editPersonalInfoForm.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const form = editPersonalInfoForm;
            const formData = new FormData(form);

            fetch('edit_personal_info.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที (1000 มิลลิวินาที)
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000); // 1000 มิลลิวินาที = 1 วินาที
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
        });
    }

    // ฟังก์ชันเพิ่มข้อมูลการศึกษา
    const educationForm = document.getElementById('educationForm');
    if (educationForm) {
        educationForm.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const form = educationForm;
            const formData = new FormData(form);

            fetch('add_education.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที (1000 มิลลิวินาที)
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000); // 1000 มิลลิวินาที = 1 วินาที
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
        });
    }

    // ฟังก์ชันแก้ไขข้อมูลการศึกษา
    const editForms = document.querySelectorAll('[id^="editEducationForm"]');
    editForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const formData = new FormData(form);

            fetch('edit_education.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที (1000 มิลลิวินาที)
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000); // 1000 มิลลิวินาที = 1 วินาที
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
        });
    });

    // ฟังก์ชันยืนยันการลบข้อมูลการศึกษา
    let educationIdToDelete = null;

    window.confirmDeleteEducation = function (educationId) {
        educationIdToDelete = educationId;
        // เปิด Modal
        var deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'), {
            keyboard: false
        });
        deleteModal.show();
    }

    // ฟังก์ชันจัดการเมื่อผู้ใช้คลิกปุ่ม "ยืนยัน" ใน Modal
    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', function () {
            if (educationIdToDelete) {
                fetch('delete_education.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ education_id: educationIdToDelete })
                })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        showToast(data.message);
                        if (data.status === 'success') {
                            // รีเฟรชหน้าเว็บหลังจาก 1 วินาที (1000 มิลลิวินาที)
                            setTimeout(() => {
                                window.location.href = 'details.php?id=' + personId;
                            }, 1000); // 1000 มิลลิวินาที = 1 วินาที
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('เกิดข้อผิดพลาด: ' + error.message);
                    });
            }
            // ซ่อน Modal หลังจากคลิกปุ่ม "ยืนยัน"
            var deleteModalElement = document.getElementById('confirmDeleteModal');
            var deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
            deleteModal.hide();
        });
    }

    // ฟังก์ชันยืนยันการลบรางวัลนักเรียน
    let awardstudentIdToDelete = null;

    window.confirmDeleteStudentAward = function (awardId) {
        awardstudentIdToDelete = awardId;
        // เปิด Modal ยืนยันการลบ
        var deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteStudentAwardModal'), {
            keyboard: false
        });
        deleteModal.show();
    }

    // ฟังก์ชันจัดการเมื่อผู้ใช้คลิกปุ่ม "ยืนยัน" ใน Modal
    const confirmDeleteStudentAwardButton = document.getElementById('confirmDeleteStudentAwardButton');
    if (confirmDeleteStudentAwardButton) {
        confirmDeleteStudentAwardButton.addEventListener('click', function () {
            if (awardstudentIdToDelete) {
                fetch('delete_student_award.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ award_id: awardstudentIdToDelete })
                })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            return response.text().then(text => {
                                throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        showToast(data.message);
                        if (data.status === 'success') {
                            // รีเฟรชหน้าเว็บหลังจาก 1 วินาที
                            setTimeout(() => {
                                window.location.href = 'details.php?id=' + personId;
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('เกิดข้อผิดพลาด: ' + error.message);
                    });
            }

            // ซ่อน Modal หลังจากคลิกปุ่ม "ยืนยัน"
            var deleteModalElement = document.getElementById('confirmDeleteStudentAwardModal');
            var deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
            deleteModal.hide();
        });
    }

    // ฟังก์ชันเพิ่มรางวัลนักเรียน
    const addStudentAwardForm = document.getElementById('addStudentAwardForm');
    if (addStudentAwardForm) {
        addStudentAwardForm.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const formData = new FormData(addStudentAwardForm);

            fetch('add_student_award.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
        });
    }

    // ฟังก์ชันแก้ไขรางวัลนักเรียน
    const editStudentAwardForms = document.querySelectorAll('.editStudentAwardForm');
    editStudentAwardForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const formData = new FormData(form);

            fetch('edit_student_award.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
        });
    });

    // ฟังก์ชันเพิ่มประสบการณ์การทำงาน
    const addWorkExperienceFormElement = document.getElementById('addWorkExperienceForm');
    if (addWorkExperienceFormElement) {
        addWorkExperienceFormElement.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const formData = new FormData(addWorkExperienceFormElement);

            // จัดการข้อมูลประเทศและฟิลด์ที่เกี่ยวข้อง
            if (countrySelect.value === 'Thailand') {
                // กำหนดประเทศเป็น 'ประเทศไทย'
                formData.set('country', 'ประเทศไทย');
                // ลบฟิลด์อื่นๆ สำหรับประเทศอื่นๆ
                formData.delete('other_country');
                formData.delete('province_other');
                formData.delete('district_other');
                formData.delete('sub_district_other');
                formData.delete('zip_code_other');
            } else if (countrySelect.value === 'Other') {
                // กำหนดประเทศจากฟิลด์อื่น
                const otherCountry = otherCountryInput.value.trim();
                formData.set('country', otherCountry);
                // ลบฟิลด์อื่นๆ สำหรับประเทศไทย
                formData.delete('country_select');
                formData.delete('province_id');
                formData.delete('district_id');
                formData.delete('sub_district_id');
                formData.delete('zip_code_select');
            } else {
                // กรณีไม่ได้เลือกประเทศอย่างถูกต้อง
                showToast('กรุณาเลือกประเทศ');
                return;
            }

            fetch('add_work_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                showToast(data.message);
                if (data.status === 'success') {
                    // รีเฟรชหน้าเว็บหลังจาก 1 วินาทีเพื่อให้ Toast แสดงได้
                    setTimeout(() => {
                        window.location.href = 'details.php?id=' + personId;
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('เกิดข้อผิดพลาด: ' + error.message);
            });
        });
    }

    // ฟังก์ชันจัดการการเลือกประเทศและฟิลด์ต่าง ๆ ในฟอร์มเพิ่มประสบการณ์การทำงาน
    const countrySelect = document.getElementById('country_select');
    const otherCountryDiv = document.getElementById('other_country_div');

    // Divs สำหรับประเทศไทย
    const provinceSelectDiv = document.getElementById('province_select_div');
    const districtSelectDiv = document.getElementById('district_select_div');
    const subDistrictSelectDiv = document.getElementById('sub_district_select_div');
    const zipCodeSelectDiv = document.getElementById('zip_code_select_div');

    // Divs สำหรับประเทศอื่นๆ
    const provinceOtherDiv = document.getElementById('province_other_div');
    const districtOtherDiv = document.getElementById('district_other_div');
    const subDistrictOtherDiv = document.getElementById('sub_district_other_div');
    const zipCodeOtherDiv = document.getElementById('zip_code_other_div');

    // Select elements
    const provinceSelect = document.getElementById('province_select');
    const districtSelect = document.getElementById('district_select');
    const subDistrictSelect = document.getElementById('sub_district_select');
    const zipCodeSelect = document.getElementById('zip_code_select');

    // Input elements สำหรับประเทศอื่นๆ
    const provinceOther = document.getElementById('province_other');
    const districtOther = document.getElementById('district_other');
    const subDistrictOther = document.getElementById('sub_district_other');
    const zipCodeOther = document.getElementById('zip_code_other');
    const otherCountryInput = document.getElementById('other_country');

    // ฟังก์ชันโหลดจังหวัด
    function loadProvinces() {
        fetch('get_provinces.php')
            .then(response => response.json())
            .then(data => {
                provinceSelect.innerHTML = '<option value="">เลือกจังหวัด</option>';
                data.forEach(province => {
                    provinceSelect.innerHTML += `<option value="${province.id}">${province.name_in_thai}</option>`;
                });
                provinceSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading provinces:', error);
            });
    }

    // ฟังก์ชันโหลดอำเภอ
    function loadDistricts(provinceId) {
        fetch(`get_districts.php?province_id=${provinceId}`)
            .then(response => response.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>';
                data.forEach(district => {
                    districtSelect.innerHTML += `<option value="${district.id}">${district.name_in_thai}</option>`;
                });
                districtSelect.disabled = false;
                // ล้างข้อมูลตำบลและรหัสไปรษณีย์
                subDistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
                subDistrictSelect.disabled = true;
                zipCodeSelect.value = '';
            })
            .catch(error => {
                console.error('Error loading districts:', error);
            });
    }

    // ฟังก์ชันโหลดตำบล
    function loadSubdistricts(districtId) {
        fetch(`get_subdistricts.php?district_id=${districtId}`)
            .then(response => response.json())
            .then(data => {
                subDistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
                data.forEach(subdistrict => {
                    subDistrictSelect.innerHTML += `<option value="${subdistrict.id}">${subdistrict.name_in_thai}</option>`;
                });
                subDistrictSelect.disabled = false;
                // ล้างรหัสไปรษณีย์
                zipCodeSelect.value = '';
            })
            .catch(error => {
                console.error('Error loading subdistricts:', error);
            });
    }

    // ฟังก์ชันโหลดรหัสไปรษณีย์
    function loadZipCode(subdistrictId) {
        fetch(`get_zipcode.php?subdistrict_id=${subdistrictId}`)
            .then(response => response.json())
            .then(data => {
                if (data.zip_code) {
                    zipCodeSelect.value = data.zip_code;
                } else {
                    zipCodeSelect.value = '';
                }
            })
            .catch(error => {
                console.error('Error loading zip code:', error);
            });
    }

    // เมื่อเลือกประเทศ
    countrySelect.addEventListener('change', function () {
        const selectedCountry = this.value;
        if (selectedCountry === 'Thailand') {
            otherCountryDiv.classList.add('d-none');
            otherCountryInput.value = '';
            
            // แสดงฟิลด์สำหรับประเทศไทย
            provinceSelectDiv.classList.remove('d-none');
            districtSelectDiv.classList.remove('d-none');
            subDistrictSelectDiv.classList.remove('d-none');
            zipCodeSelectDiv.classList.remove('d-none');
            
            provinceSelect.disabled = false;
            districtSelect.disabled = false;
            subDistrictSelect.disabled = false;
            zipCodeSelect.readOnly = true;
            
            // ซ่อนฟิลด์สำหรับประเทศอื่นๆ
            provinceOtherDiv.classList.add('d-none');
            districtOtherDiv.classList.add('d-none');
            subDistrictOtherDiv.classList.add('d-none');
            zipCodeOtherDiv.classList.add('d-none');
            
            // ล้างค่าฟิลด์สำหรับประเทศอื่นๆ
            provinceOther.value = '';
            districtOther.value = '';
            subDistrictOther.value = '';
            zipCodeOther.value = '';
            
            // โหลดข้อมูลจังหวัด
            loadProvinces();
        } else if (selectedCountry === 'Other') {
            otherCountryDiv.classList.remove('d-none');
            otherCountryInput.focus();
            
            // ซ่อนฟิลด์สำหรับประเทศไทย
            provinceSelectDiv.classList.add('d-none');
            districtSelectDiv.classList.add('d-none');
            subDistrictSelectDiv.classList.add('d-none');
            zipCodeSelectDiv.classList.add('d-none');
            
            provinceSelect.disabled = true;
            districtSelect.disabled = true;
            subDistrictSelect.disabled = true;
            zipCodeSelect.value = '';
            
            // แสดงฟิลด์สำหรับประเทศอื่นๆ
            provinceOtherDiv.classList.remove('d-none');
            districtOtherDiv.classList.remove('d-none');
            subDistrictOtherDiv.classList.remove('d-none');
            zipCodeOtherDiv.classList.remove('d-none');
            
            // เปิดใช้งานฟิลด์รหัสไปรษณีย์สำหรับประเทศอื่นๆ
            zipCodeOther.readOnly = false;
            
            // ล้างค่าฟิลด์สำหรับประเทศไทย
            provinceSelect.innerHTML = '<option value="">เลือกจังหวัด</option>';
            districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>';
            subDistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
            zipCodeSelect.value = '';
        } else {
            // ซ่อนฟิลด์ทั้งหมดหากไม่ได้เลือกประเทศ
            otherCountryDiv.classList.add('d-none');
            otherCountryInput.value = '';
            
            // ซ่อนฟิลด์สำหรับประเทศไทย
            provinceSelectDiv.classList.add('d-none');
            districtSelectDiv.classList.add('d-none');
            subDistrictSelectDiv.classList.add('d-none');
            zipCodeSelectDiv.classList.add('d-none');
            
            provinceSelect.disabled = true;
            districtSelect.disabled = true;
            subDistrictSelect.disabled = true;
            zipCodeSelect.value = '';
            
            // ซ่อนฟิลด์สำหรับประเทศอื่นๆ
            provinceOtherDiv.classList.add('d-none');
            districtOtherDiv.classList.add('d-none');
            subDistrictOtherDiv.classList.add('d-none');
            zipCodeOtherDiv.classList.add('d-none');
            
            // ล้างค่าฟิลด์ทั้งหมด
            provinceOther.value = '';
            districtOther.value = '';
            subDistrictOther.value = '';
            zipCodeOther.value = '';
        }
    });

    // เมื่อเลือกจังหวัด
    provinceSelect.addEventListener('change', function () {
        const provinceId = this.value;
        if (provinceId) {
            loadDistricts(provinceId);
        } else {
            // หากไม่เลือกจังหวัด
            districtSelect.disabled = true;
            districtSelect.innerHTML = '<option value="">เลือกอำเภอ</option>';
            subDistrictSelect.disabled = true;
            subDistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
            zipCodeSelect.value = '';
        }
    });

    // เมื่อเลือกอำเภอ
    districtSelect.addEventListener('change', function () {
        const districtId = this.value;
        if (districtId) {
            loadSubdistricts(districtId);
        } else {
            // หากไม่เลือกอำเภอ
            subDistrictSelect.disabled = true;
            subDistrictSelect.innerHTML = '<option value="">เลือกตำบล</option>';
            zipCodeSelect.value = '';
        }
    });

    // เมื่อเลือกตำบล
    subDistrictSelect.addEventListener('change', function () {
        const subdistrictId = this.value;
        if (subdistrictId) {
            loadZipCode(subdistrictId);
        } else {
            // หากไม่เลือกตำบล
            zipCodeSelect.value = '';
        }
    });

    // ฟังก์ชันยืนยันการลบประสบการณ์การทำงาน
    let workIdToDelete = null;

    window.confirmDeleteWorkExperience = function (workId) {
        workIdToDelete = workId;
        // เปิด Modal ยืนยันการลบ
        var deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteWorkExperienceModal'), {
            keyboard: false
        });
        deleteModal.show();
    }

    // ฟังก์ชันจัดการเมื่อผู้ใช้คลิกปุ่ม "ยืนยัน" ใน Modal ลบประสบการณ์การทำงาน
    const confirmDeleteWorkExperienceButton = document.getElementById('confirmDeleteWorkExperienceButton');
    if (confirmDeleteWorkExperienceButton) {
        confirmDeleteWorkExperienceButton.addEventListener('click', function () {
            if (workIdToDelete) {
                fetch('delete_work_experience.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ work_id: workIdToDelete })
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            console.error('Response is not JSON:', text);
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        // รีเฟรชหน้าเว็บหลังจาก 1 วินาที
                        setTimeout(() => {
                            window.location.href = 'details.php?id=' + personId;
                        }, 1000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('เกิดข้อผิดพลาด: ' + error.message);
                });
            }

            // ซ่อน Modal หลังจากคลิกปุ่ม "ยืนยัน"
            var deleteModalElement = document.getElementById('confirmDeleteWorkExperienceModal');
            var deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
            deleteModal.hide();
        });
    }

    // ฟังก์ชันแก้ไขประสบการณ์การทำงาน
    const editWorkExperienceForms = document.querySelectorAll('.editWorkExperienceForm');
    editWorkExperienceForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); // ป้องกันไม่ให้ฟอร์มส่งแบบปกติ

            const formData = new FormData(form);

            // หากต้องการจัดการการเลือกประเทศในการแก้ไข, เพิ่มโค้ดที่นี่

            fetch('edit_work_experience.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                showToast(data.message);
                if (data.status === 'success') {
                    // รีเฟรชหน้าเว็บหลังจาก 1 วินาทีเพื่อให้ Toast แสดงได้
                    setTimeout(() => {
                        window.location.href = 'details.php?id=' + personId;
                    }, 1000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('เกิดข้อผิดพลาด: ' + error.message);
            });
        });
    });
    const addAwardHistoryForm = document.getElementById('addAwardHistoryForm');
    if (addAwardHistoryForm) {
        addAwardHistoryForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(addAwardHistoryForm);
            fetch('add_award_history.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                showToast(data.message);
                if (data.status === 'success') {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                console.error('Error:', error);
            });
        });
    }

    // แก้ไขประวัติการรับรางวัล
    const editAwardHistoryForms = document.querySelectorAll('.editAwardHistoryForm');
    editAwardHistoryForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch('edit_award_history.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                showToast(data.message);
                if (data.status === 'success') {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
            .catch(error => {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                console.error('Error:', error);
            });
        });
    });

    // ยืนยันการลบประวัติการรับรางวัล
    let awardIdToDelete = null;
    
    window.confirmDeleteAwardHistory = function (awardId) {
        awardIdToDelete = awardId;
        const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteAwardHistoryModal'));
        deleteModal.show();
    }

    const confirmDeleteAwardHistoryButton = document.getElementById('confirmDeleteAwardHistoryButton');
    if (confirmDeleteAwardHistoryButton) {
        confirmDeleteAwardHistoryButton.addEventListener('click', function () {
            if (awardIdToDelete) {
                fetch('delete_award_history.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ award_id: awardIdToDelete })
                })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        return response.text().then(text => {
                            throw new TypeError("ไม่สามารถประมวลผลได้ เนื่องจาก Response ไม่เป็น JSON: " + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    showToast(data.message);
                    if (data.status === 'success') {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                })
                .catch(error => {
                    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์');
                    console.error('Error:', error);
                });
            }

            // ซ่อน Modal หลังจากคลิกปุ่ม "ยืนยัน"
            var deleteModalElement = document.getElementById('confirmDeleteAwardHistoryModal');
            var deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
            deleteModal.hide();
        });
    }
});
