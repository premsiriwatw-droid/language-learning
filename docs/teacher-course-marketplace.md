# Teacher Course Marketplace & Enrollment (คนที่ 3 — Nakin)

Branch: `feature/teacher-course-marketplace`

## ทำอะไรบ้าง
- ปุ่ม **คอร์สจากผู้สอน** ในหน้า Languages → `/teacher-courses`
- Marketplace: แสดงเฉพาะคอร์ส `published` ของผู้สอนที่ผ่านการอนุมัติ กรองตามภาษา / ระดับ / ฟรี-เสียเงิน
- Course detail: ผู้สอน + Verified Instructor badge, รายละเอียด, ราคา, จำนวนบท/ผู้เรียน, ปุ่มขอเข้าเรียน
- ผู้สอน (approved เท่านั้น) สร้าง/แก้/ปิดคอร์สของตัวเองได้หลายคอร์ส ที่ `/teacher/courses`
- **บทเรียนของผู้สอนเอง** (`teacher_lessons`): ข้อความ + เอกสาร 1 ไฟล์ (PDF/Word/PowerPoint/Excel/TXT ≤ 20 MB) + วิดีโอ 1 รายการ (ลิงก์ YouTube/Vimeo หรืออัปโหลด MP4/WebM ≤ 100 MB) จัดลำดับขึ้น/ลงได้
  - ไฟล์เก็บใน disk `local` (private) โฟลเดอร์ `teacher-course-materials/{course_id}` เปิดได้เฉพาะเจ้าของ แอดมิน และผู้เรียนสถานะ active/completed ผ่าน route ที่ตรวจสิทธิ์ (`Gate 'study'`)
  - เอกสารบังคับดาวน์โหลด (attachment + nosniff) วิดีโอลิงก์ embed ได้เฉพาะ YouTube (nocookie) / Vimeo
- **แอดมินอนุมัติก่อนเผยแพร่**: ครูกด "ส่งให้แอดมินตรวจ" → แอดมินอนุมัติ/ไม่อนุมัติ (ต้องใส่เหตุผล) ที่ `/admin/teacher-courses`
  - แก้คอร์สที่เผยแพร่อยู่ → กลับไปรอตรวจ (หายจาก marketplace แต่ผู้เรียนเดิมเรียนต่อได้)
  - ปิดคอร์สแล้วเปิดใหม่ได้ทันทีถ้าไม่ได้แก้อะไรหลังอนุมัติ ถ้าแก้แล้วต้องส่งตรวจใหม่
  - คอร์สที่มีผู้เรียนแล้ว ถ้าถอนคำขอ/ถูกตีกลับ จะเป็น `closed` ไม่กลับเป็น `draft`
- คำขอเข้าเรียน: อนุมัติ / ปฏิเสธ (พร้อมข้อความ) / ยืนยันรับชำระ / บันทึกว่าเรียนจบ
- Paid MVP: ไม่รับเงินจริง ผู้เรียนกด "ชำระเงิน (จำลอง)" แล้วผู้สอนกดยืนยันเอง
- "คอร์สที่ฉันสมัคร" ของผู้เรียนที่ `/teacher-courses/my`

System Course (ของ Admin) ไม่ถูกแก้ไข Teacher Course แค่ *อ้างอิง* บทเรียนของระบบเป็นหลักสูตร
และการเปิดบทยังใช้กฎปลดล็อกเดิม (`LessonAccess`) แบบอ่านอย่างเดียว

## ตาราง
| ตาราง | คอลัมน์หลัก |
|---|---|
| `teacher_courses` | teacher_id, language_id (nullOnDelete), title, description, level, price_type, price, status (`draft` / `pending_review` / `published` / `closed`), enrollment_mode, published_at, submitted_at, approved_at, reviewed_at, reviewed_by, review_note |
| `teacher_lessons` | teacher_course_id, title, body, video_url, video_path/name/size, document_path/name/size, position |
| `teacher_course_lessons` | teacher_course_id, lesson_id, position |
| `course_enrollments` | teacher_course_id, user_id (**unique คู่กัน**), status, payment_status, price_at_request, teacher_note, เวลาแต่ละขั้น |

## Contract สำหรับเพื่อนในทีม

### Kantaphat — นักเรียนในคอร์ส
```php
use App\Models\CourseEnrollment;
use App\Models\TeacherCourse;

$course->enrollments()->learning()->with('user')->get(); // active + completed
TeacherCourse::where('teacher_id', $teacher->id)->get();  // คอร์สของครูคนนี้
$course->lessons;                                          // แบบฝึกจากบทเรียนหลักของระบบ (มี quiz/attempt, เรียงตาม position)
$course->teacherLessons;                                   // บทเรียนที่ครูเขียนเอง (ไม่มี quiz จึงไม่มีข้อมูลจุดอ่อน)
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

### Chotikan — แจ้งเตือนการตรวจคอร์ส
โมดูลนี้ยิง `UserNotificationRequested` เองตาม contract (ไม่ได้แก้โค้ด notification):
- ส่งตรวจ → แอดมินทุกคน, path `/admin/teacher-courses/{id}`
- อนุมัติ / ไม่อนุมัติ (พร้อมเหตุผล) → ครูเจ้าของคอร์ส, path `/teacher/courses/{id}/edit`
- ใช้ topic `TeacherApplicationStatus` ชั่วคราว ถ้าอยากแยก ช่วยเพิ่ม case เช่น `CourseReview` ใน `NotificationTopic` แล้วผมจะเปลี่ยนไปใช้

### เจ้าของหน้า Admin dashboard
หน้าตรวจคอร์สอยู่ที่ `route('admin.teacher-courses.index')` (ตอนนี้มีปุ่มในหน้าคอร์สจากผู้สอนสำหรับแอดมิน) ถ้าจะใส่ลิงก์ในเมนูแอดมินใช้ route นี้ได้เลย

### Tummaruk — ใช้ contract เดิม ไม่ได้แก้
- Route ฝั่งครูใช้ `ApprovedTeacherMiddleware`
- Marketplace ใช้ scope `TeacherCourse::fromApprovedTeachers()` ซึ่งเป็นรูป query ของ `User::isApprovedTeacher()`
  (role = teacher + application approved + approved_at) ครูที่ถูกถอนสิทธิ์ คอร์สจะหายจาก marketplace ทันที

## วิธีรัน
```bash
php artisan migrate
php artisan test
```
