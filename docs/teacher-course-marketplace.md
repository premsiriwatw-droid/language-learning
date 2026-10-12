# Teacher Course Marketplace & Enrollment (คนที่ 3 — Nakin)

Branch: `feature/teacher-course-marketplace`

## ทำอะไรบ้าง
- ปุ่ม **คอร์สจากผู้สอน** ในหน้า Languages → `/teacher-courses`
- Marketplace: แสดงเฉพาะคอร์ส `published` ของผู้สอนที่ผ่านการอนุมัติ กรองตามภาษา / ระดับ / ฟรี-เสียเงิน
- Course detail: ผู้สอน + Verified Instructor badge, รายละเอียด, ราคา, จำนวนบท/ผู้เรียน, ปุ่มขอเข้าเรียน
- ผู้สอน (approved เท่านั้น) สร้าง/แก้/เปิดรับสมัคร/ปิดคอร์สของตัวเองได้หลายคอร์ส ที่ `/teacher/courses`
- คำขอเข้าเรียน: อนุมัติ / ปฏิเสธ (พร้อมข้อความ) / ยืนยันรับชำระ / บันทึกว่าเรียนจบ
- Paid MVP: ไม่รับเงินจริง ผู้เรียนกด "ชำระเงิน (จำลอง)" แล้วผู้สอนกดยืนยันเอง
- "คอร์สที่ฉันสมัคร" ของผู้เรียนที่ `/teacher-courses/my`

System Course (ของ Admin) ไม่ถูกแก้ไข Teacher Course แค่ *อ้างอิง* บทเรียนของระบบเป็นหลักสูตร
และการเปิดบทยังใช้กฎปลดล็อกเดิม (`LessonAccess`) แบบอ่านอย่างเดียว

## ตาราง
| ตาราง | คอลัมน์หลัก |
|---|---|
| `teacher_courses` | teacher_id, language_id (nullOnDelete), title, description, level, price_type, price, status, enrollment_mode, published_at |
| `teacher_course_lessons` | teacher_course_id, lesson_id, position |
| `course_enrollments` | teacher_course_id, user_id (**unique คู่กัน**), status, payment_status, price_at_request, teacher_note, เวลาแต่ละขั้น |

## Contract สำหรับเพื่อนในทีม

### Kantaphat — นักเรียนในคอร์ส
```php
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;

$course->enrollments()->learning()->with('user')->get(); // active + completed
TeacherCourse::where('teacher_id', $teacher->id)->get();  // คอร์สของครูคนนี้
$course->lessons;                                          // บทเรียนในคอร์ส (เรียงตาม position)
```
- `course_enrollments.status`: `pending` / `approved` / `rejected` / `active` / `completed`
- `payment_status`: `not_required` / `unpaid` / `submitted` / `confirmed`
- สิทธิ์ครูต่อคอร์ส: `Gate::allows('update', $course)` (เจ้าของ + `isApprovedTeacher()`)

### Premsiriwat — การจบคอร์ส (XP/ดาว)
- Event `App\Events\TeacherCourses\EnrollmentStatusChanged($enrollment, $fromStatus, $toStatus)`
- `$toStatus === 'completed'` = ผู้เรียนจบ Teacher Course ยิงครั้งเดียวต่อการจบ
- โมดูลนี้ **ไม่แจกดาว/XP เอง** ให้ listener ของ Progression ตัดสินตาม reward policy

### Chotikan — Notifications
ฟัง event เดียวกัน:
| from → to | ความหมาย |
|---|---|
| `null`/`rejected` → `pending` | มีคำขอใหม่ (แจ้งครู) |
| `null`/`rejected` → `active` หรือ `approved` | เข้าร่วม/จองที่นั่งแล้ว |
| `pending` → `approved` / `active` | ครูอนุมัติ |
| `pending`/`approved` → `rejected` | ครูปฏิเสธ (`$enrollment->teacher_note` = เหตุผล) |
| `approved` → `active` | ยืนยันชำระเงินแล้ว |
| `active` → `completed` | เรียนจบ |

Event implement `ShouldDispatchAfterCommit` จึงยิงหลังบันทึกฐานข้อมูลสำเร็จเท่านั้น

### Tummaruk — ใช้ contract เดิม ไม่ได้แก้
- Route ฝั่งครูใช้ `ApprovedTeacherMiddleware`
- Marketplace ใช้ scope `TeacherCourse::fromApprovedTeachers()` ซึ่งเป็นรูป query ของ `User::isApprovedTeacher()`
  (role = teacher + application approved + approved_at) ครูที่ถูกถอนสิทธิ์ คอร์สจะหายจาก marketplace ทันที

## วิธีรัน
```bash
php artisan migrate
php artisan test
```
