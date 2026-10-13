<?php

namespace App\Http\Requests;

class StoreCourseAssignmentRequest extends TeacherLearningRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'target' => ['required', 'string', 'max:64', 'regex:/^(lesson|unit):[1-9][0-9]*$/'],
            'due_at' => ['required', 'date', 'after:now'],
        ];
    }
}
