(() => {
    const input = document.getElementById('profile-photo');
    const preview = document.getElementById('avatar-preview');
    const initial = document.getElementById('avatar-initial');
    const feedback = document.getElementById('photo-feedback');
    if (!input || !preview || !feedback) return;

    const originalSrc = preview.getAttribute('src');
    let objectUrl;
    const resetPreview = () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = undefined;
        if (originalSrc) {
            preview.src = originalSrc;
            preview.hidden = false;
        } else {
            preview.removeAttribute('src');
            preview.hidden = true;
        }
        if (initial) initial.hidden = false;
    };

    input.addEventListener('change', () => {
        resetPreview();
        input.setCustomValidity('');
        feedback.textContent = '';
        delete feedback.dataset.error;
        const file = input.files[0];
        if (!file) return;

        let error = '';
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            error = 'กรุณาเลือกไฟล์ JPG, PNG หรือ WebP';
        } else if (file.size > 2 * 1024 * 1024) {
            error = 'รูปภาพต้องมีขนาดไม่เกิน 2 MB';
        }
        if (error) {
            input.setCustomValidity(error);
            feedback.textContent = error;
            feedback.dataset.error = 'true';
            input.reportValidity();
            return;
        }

        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.hidden = false;
        if (initial) initial.hidden = true;
        feedback.textContent = 'ตัวอย่างรูปที่เลือก — กดบันทึกเพื่ออัปเดตโปรไฟล์';
    });
    input.form.addEventListener('submit', () => {
        const button = input.form.querySelector('button[type=submit]');
        button.disabled = true;
        button.textContent = 'กำลังบันทึก…';
    });
    window.addEventListener('pagehide', resetPreview);
    window.addEventListener('pageshow', () => {
        const button = input.form.querySelector('button[type=submit]');
        button.disabled = false;
        button.textContent = 'บันทึกรูปโปรไฟล์ ↗';
    });
})();
