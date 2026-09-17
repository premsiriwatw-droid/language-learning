{{-- พิกัดไฟล์: resources/views/frontend/profile.blade.php --}}
@extends('layouts.app') {{-- เปลี่ยนเป็นชื่อ Layout ของคุณ หากไม่ได้ใช้ให้ลบบรรทัดนี้ออก --}}

@section('content')
<div style="max-width: 500px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif;">

    {{-- 1. ปุ่มกลับหน้าหลัก (พิกัด: บนสุดซ้ายมือ) --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ url('/') }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
            ❮ กลับหน้าหลัก
        </a>
    </div>

    {{-- 2. รูปโปรไฟล์และการอัปโหลด (พิกัด: ตรงกลางส่วนบน) --}}
    <div style="text-align: center; margin-bottom: 30px;">
        <img src="{{ auth()->user()->profile_photo_path ? asset('storage/'.auth()->user()->profile_photo_path) : asset('images/default.png') }}" 
             alt="Profile" 
             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #28a745;">
        
        <form action="{{ url('/profile/upload') }}" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            @csrf
            <input type="file" name="photo" accept="image/*" style="font-size: 14px;">
            <button type="submit" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                เปลี่ยนรูป
            </button>
        </form>
    </div>

    {{-- 4. ระดับภาษา (HSK) & 5. ดาวสะสม (พิกัด: ตรงกลางหน้าจอ วางเรียงคู่กัน) --}}
    <div style="display: flex; gap: 15px; margin-bottom: 30px;">
        <div style="flex: 1; background-color: #fff3cd; padding: 15px; border-radius: 8px; text-align: center;">
            <p style="margin: 0; font-size: 14px; color: #666;">ระดับภาษา</p>
            <h3 style="margin: 5px 0 0 0; color: #856404;">HSK {{ auth()->user()->hsk_level ?? 1 }}</h3>
        </div>
        <div style="flex: 1; background-color: #d4edda; padding: 15px; border-radius: 8px; text-align: center;">
            <p style="margin: 0; font-size: 14px; color: #666;">ดาวสะสม</p>
            <h3 style="margin: 5px 0 0 0; color: #155724;">⭐ {{ auth()->user()->stars ?? 0 }}</h3>
        </div>
    </div>

    {{-- 3. บทเรียนที่เรียนถึง (พิกัด: ส่วนล่างสุด) --}}
    <div style="background-color: #e9ecef; padding: 15px; border-radius: 8px;">
        <p style="margin: 0 0 5px 0; font-size: 14px; color: #555;">กำลังเรียนอยู่:</p>
        <h4 style="margin: 0 0 15px 0; color: #333;">
            {{ auth()->user()->current_lesson ?? 'ยังไม่ได้ระบุบทเรียน' }}
        </h4>
        
        {{-- แถบสถานะความคืบหน้า (Progress Bar) --}}
        <div style="background-color: #dee2e6; border-radius: 10px; height: 10px; width: 100%;">
            <div style="background-color: #007bff; height: 10px; border-radius: 10px; width: 45%;"></div>
        </div>
        <p style="margin: 5px 0 0 0; font-size: 12px; color: #666; text-align: right;">ความคืบหน้า 45%</p>
    </div>

</div>
@endsection