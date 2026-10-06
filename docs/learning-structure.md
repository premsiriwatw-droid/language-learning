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

## ความถูกต้องของข้อมูล
- **Validation:** ชื่อบังคับกรอก, ยาวไม่เกิน 255, ห้ามซ้ำภายใน parent เดียวกัน (ภาษาห้ามซ้ำทั้งระบบ) ข้อความ error เป็นภาษาไทย และฟอร์มแก้ไขจะเปิดค้างพร้อมค่าที่พิมพ์ไว้เมื่อไม่ผ่าน
- **คนละ parent:** parent มาจาก URL เท่านั้น, `course_id`/`unit_id`/`position` ไม่ fillable จึงย้าย parent หรือแก้ลำดับผ่านฟอร์มแก้ไขไม่ได้, reorder ต้องส่ง id ของลูกใน parent นั้นครบพอดี (ไม่ขาด ไม่เกิน ไม่ซ้ำ ไม่ปนของ parent อื่น) ถ้าหน้าเก่าเกินไปจะให้รีเฟรช
- **ลำดับ:** เรียงด้วย (position, id) เสมอ, next/previous ไม่ข้ามหรือวนแม้ position ซ้ำ, การเพิ่ม/ลบ/reorder ทำใน transaction และล็อก parent, หลังลบจะ resequence เป็น 1..N

## การลบ
FK เป็น `cascade` อยู่แล้ว: ลบ Language/Course/Unit/Lesson จะลบลูกทั้งหมด (รวมคำศัพท์, แบบฝึกหัด, คำถาม, คำตอบ, lesson_progress)
ปุ่มลบพาไปหน้ายืนยัน (`/admin/{type}/{id}/delete`) ที่แสดงจำนวน Course/Unit/Lesson/คำศัพท์/แบบฝึกหัด/คำถาม/คำตอบ/ความคืบหน้าของผู้เรียน ที่จะหายไป และ server จะลบก็ต่อเมื่อพิมพ์ชื่อรายการตรงกัน (`confirm_name`)

## วิธีรัน
```bash
php artisan migrate
ADMIN_PASSWORD=... php artisan db:seed --class=AdminUserSeeder   # หรือตั้ง role ผ่านหน้า User Management
php artisan test
```
