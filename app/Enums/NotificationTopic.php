<?php

namespace App\Enums;

enum NotificationTopic: string
{
    case LevelUp = 'level_up';
    case QuestComplete = 'quest_complete';
    case TeacherApplicationStatus = 'teacher_application_status';
    case EnrollmentStatus = 'enrollment_status';
    case AssignmentNew = 'assignment_new';
    case RecommendationNew = 'recommendation_new';
    case TeacherReply = 'teacher_reply';
    case Certificate = 'certificate';
    case Achievement = 'achievement';
}
