<?php

namespace App\Http\Requests;

class StoreTeacherRecommendationRequest extends TeacherLearningRequest
{
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:2000'],
        ];
    }
}
