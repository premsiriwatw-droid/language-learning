# Learning Structure (คนที่ 2) — Language → Course → Unit → Lesson

## สิ่งที่เพิ่ม
- Admin CRUD ที่ `/admin/languages` → คอร์ส → Unit → Lesson (ต้อง login และเป็น admin)
- คอลัมน์ `position` ใน `units` (ลำดับภายใน Course) และ `lessons` (ลำดับภายใน Unit)
  - migration backfill ข้อมูลเดิมตามลำดับ id เดิม → ลำดับที่ผู้เรียนเห็นไม่เปลี่ยน
  - Unit/Lesson ใหม่ต่อท้ายให้อัตโนมัติ, ลบแล้วจัดลำดับใหม่ไม่มีช่องว่าง, Admin กด ↑ ↓ เพื่อสลับลำดับ
- สิทธิ์ Admin ใช้ระบบของ main (`users.role = 'admin'`, `User::isAdmin()`, middleware alias `admin`) — route ทั้งหมดอยู่ใต้ `['auth', 'admin']`
- ไม่ได้แก้ relationship ของ Vocabulary/Exercise/Question/Answer และไม่ได้แตะ Quiz/Progress

## สำหรับคนที่ 5 (Progress / Unlock)
ใช้ลำดับจาก `position` เท่านั้น — ห้ามเรียงด้วย id

```php
$course->units;                 // เรียงตาม position แล้ว
$unit->lessons;                 // เรียงตาม position แล้ว
$unit->nextUnit();              // Unit ถัดไปใน Course เดียวกัน หรือ null ถ้าเป็นอันสุดท้าย
$unit->previousUnit();          // Unit ก่อนหน้า หรือ null
$lesson->nextLesson();          // Lesson ถัดไปใน Unit เดียวกัน หรือ null
$lesson->previousLesson();
```

ตัวอย่างแนวคิด unlock (≥70%) ที่คนที่ 5 นำไปทำต่อ:
- Unit แรก (`previousUnit() === null`) ปลดล็อกเสมอ
- Unit อื่นปลดล็อกเมื่อ Lesson ใน `previousUnit()->lessons` ผ่านเงื่อนไข ≥70% ตามที่ทีมกำหนด
- "Unit ปัจจุบัน" = Unit แรกตามลำดับที่ยังไม่ผ่านเงื่อนไข

## การลบ
FK เป็น `cascade` อยู่แล้ว: ลบ Language/Course/Unit/Lesson จะลบลูกทั้งหมด (รวมคำศัพท์, แบบฝึกหัด, คำถาม, คำตอบ, lesson_progress)
หน้า Admin แสดงจำนวนลูกใน dialog ยืนยันก่อนลบ และแสดงสรุปหลังลบ

## วิธีรัน
```bash
php artisan migrate
ADMIN_PASSWORD=... php artisan db:seed --class=AdminUserSeeder   # หรือตั้ง role ผ่านหน้า User Management
php artisan test
```
