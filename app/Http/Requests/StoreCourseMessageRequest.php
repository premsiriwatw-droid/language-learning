<?php

namespace App\Http\Requests;

class StoreCourseMessageRequest extends TeacherLearningRequest
{
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'max:2000']];
    }
}
