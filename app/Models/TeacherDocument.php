<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherDocument extends Model
{
    protected $fillable = [
        'teacher_application_id', 'document_type', 'file_path',
        'original_name', 'mime_type', 'file_size', 'uploaded_at',
    ];

    // Never expose private storage paths when a model is serialized.
    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return ['file_size' => 'integer', 'uploaded_at' => 'datetime'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(TeacherApplication::class, 'teacher_application_id');
    }
}
