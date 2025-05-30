<?php

namespace App\Helpers;

class GradeHelper
{
    /**
     * Get the appropriate color class based on the score
     *
     * @param float $score
     * @return string
     */
    public static function getScoreColor($score)
    {
        if ($score >= 90) {
            return 'bg-success';
        } elseif ($score >= 80) {
            return 'bg-info';
        } elseif ($score >= 70) {
            return 'bg-primary';
        } elseif ($score >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }

    /**
     * Get the appropriate color class based on the pass rate
     *
     * @param float $rate
     * @return string
     */
    public static function getPassRateColor($rate)
    {
        if ($rate >= 90) {
            return 'bg-success';
        } elseif ($rate >= 75) {
            return 'bg-info';
        } elseif ($rate >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }

    /**
     * Get the appropriate badge color class based on the grade
     *
     * @param string $grade
     * @return string
     */
    public static function getGradeBadgeColor($grade)
    {
        if (in_array($grade, ['A+', 'A', 'A-'])) {
            return 'bg-success';
        }
        if (in_array($grade, ['B+', 'B', 'B-'])) {
            return 'bg-info';
        }
        if (in_array($grade, ['C+', 'C', 'C-', 'D+', 'D'])) {
            return 'bg-warning';
        }
        return 'bg-danger';
    }

    /**
     * Get the appropriate badge color class based on the GPA
     *
     * @param float $gpa
     * @return string
     */
    public static function getGpaBadgeColor($gpa)
    {
        if ($gpa >= 3.5) return 'bg-success';
        if ($gpa >= 2.5) return 'bg-info';
        if ($gpa >= 1.5) return 'bg-warning';
        return 'bg-danger';
    }

    /**
     * Get the GPA text description
     *
     * @param float $gpa
     * @return string
     */
    public static function getGpaText($gpa)
    {
        if ($gpa >= 3.5) return 'ممتاز';
        if ($gpa >= 2.5) return 'جيد جداً';
        if ($gpa >= 1.5) return 'جيد';
        if ($gpa >= 1.0) return 'مقبول';
        return 'ضعيف';
    }

    /**
     * Get the appropriate color class based on the success rate
     *
     * @param float $rate
     * @return string
     */
    public static function getSuccessRateColor($rate)
    {
        if ($rate >= 90) return 'bg-success';
        if ($rate >= 70) return 'bg-info';
        if ($rate >= 50) return 'bg-warning';
        return 'bg-danger';
    }

    /**
     * Get the appropriate icon class based on the course type
     *
     * @param string $type
     * @return string
     */
    public static function getCourseTypeIcon($type)
    {
        switch ($type) {
            case 'core': return 'fa-book';
            case 'elective': return 'fa-puzzle-piece';
            case 'lab': return 'fa-flask';
            case 'practicum': return 'fa-hands-helping';
            default: return 'fa-book';
        }
    }

    /**
     * Get the appropriate color class based on the course type
     *
     * @param string $type
     * @return string
     */
    public static function getCourseTypeColor($type)
    {
        switch ($type) {
            case 'core': return 'primary';
            case 'elective': return 'success';
            case 'lab': return 'info';
            case 'practicum': return 'warning';
            default: return 'secondary';
        }
    }

    /**
     * Get the appropriate color class based on the attendance rate
     *
     * @param float $rate
     * @return string
     */
    public static function getAttendanceBadgeColor($rate)
    {
        if ($rate >= 90) return 'bg-success';
        if ($rate >= 75) return 'bg-info';
        if ($rate >= 60) return 'bg-warning';
        return 'bg-danger';
    }
} 