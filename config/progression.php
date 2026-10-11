<?php

return [
    'placement' => [
        'question_count' => 12,
        'intermediate_percent' => 50,
        'advanced_percent' => 80,
    ],

    'promotion' => [
        // Match actual course titles; intermediate evidence must be configured
        // when an intermediate course becomes available.
        'course_titles' => [
            'beginner' => ['Chinese Beginner', 'English Beginner'],
            'intermediate' => [],
        ],
        'completion_percent' => 80,
        'first_attempt_score_percent' => 75,
        'minimum_scored_lessons' => 5,
    ],

    'stars' => [
        'milestone_lessons' => 5,
        'milestone_reward' => 10,
    ],

    'quests' => [
        'complete_lesson' => ['target' => 1, 'reward_stars' => 2],
        'correct_answers' => ['target' => 5, 'reward_stars' => 3],
        'review_mistakes' => ['target' => 3, 'reward_stars' => 3],
    ],
];
