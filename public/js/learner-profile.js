(() => {
    const input = document.getElementById('profile-photo');
    const preview = document.getElementById('avatar-preview');
    const initial = document.getElementById('avatar-initial');
    const feedback = document.getElementById('photo-feedback');

    if (!input || !preview || !feedback) return;

    const form = input.form;
    const button = form.querySelector('button[type=submit]');
    const maxUploadBytes = 2 * 1024 * 1024;
    const originalSrc = preview.getAttribute('src');

    let submitting = false;
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

        const supported =
            ['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
            (!file.type && /\.(jpe?g|png|webp)$/i.test(file.name));

        let error = '';

        if (!supported) {
            error = 'กรุณาเลือกไฟล์ JPG, PNG หรือ WebP';
        } else if (file.size > 20 * 1024 * 1024) {
            error = 'กรุณาเลือกรูปที่มีขนาดไม่เกิน 20 MB';
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

        feedback.textContent =
            'ตัวอย่างรูปที่เลือก — กดบันทึกเพื่ออัปเดตโปรไฟล์';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitting) return;

        submitting = true;
        button.disabled = true;
        input.disabled = true;
        button.textContent = 'กำลังเตรียมรูป…';
        delete feedback.dataset.error;

        try {
            const file = input.files[0];
            await preview.decode();

            const needsResize =
                file.size > maxUploadBytes ||
                preview.naturalWidth > 4096 ||
                preview.naturalHeight > 4096;

            if (needsResize) {
                const scale = Math.min(
                    1,
                    1024 / Math.max(
                        preview.naturalWidth,
                        preview.naturalHeight
                    )
                );

                const canvas = document.createElement('canvas');

                canvas.width = Math.max(
                    1,
                    Math.round(preview.naturalWidth * scale)
                );

                canvas.height = Math.max(
                    1,
                    Math.round(preview.naturalHeight * scale)
                );

                const context = canvas.getContext('2d');

                // รูปที่ย่อเป็น JPEG ใช้พื้นหลังสีขาว
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                context.drawImage(
                    preview,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                const blob = await new Promise(resolve => {
                    canvas.toBlob(resolve, 'image/jpeg', 0.9);
                });

                if (!blob || blob.size > maxUploadBytes) {
                    throw new Error('resize');
                }

                const transfer = new DataTransfer();

                transfer.items.add(
                    new File([blob], 'profile.jpg', {
                        type: 'image/jpeg'
                    })
                );

                input.files = transfer.files;

                feedback.textContent =
                    'ย่อรูปแล้ว กำลังบันทึกรูปโปรไฟล์…';
            } else {
                feedback.textContent = 'กำลังบันทึกรูปโปรไฟล์…';
            }

            // ต้องเปิด input ก่อนส่ง เพื่อให้ไฟล์ติดไปกับฟอร์ม
            input.disabled = false;
            button.textContent = 'กำลังบันทึก…';

            HTMLFormElement.prototype.submit.call(form);
        } catch {
            submitting = false;
            input.disabled = false;
            button.disabled = false;
            button.textContent = 'บันทึกรูปโปรไฟล์ ↗';

            feedback.textContent =
                'เตรียมรูปไม่สำเร็จ กรุณาเลือกรูป JPG, PNG หรือ WebP ใหม่ หรือใช้รูปไม่เกิน 2 MB';

            feedback.dataset.error = 'true';
        }
    });

    window.addEventListener('pageshow', () => {
        submitting = false;
        input.disabled = false;
        button.disabled = false;
        button.textContent = 'บันทึกรูปโปรไฟล์ ↗';
    });
})();
