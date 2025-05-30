<?php

namespace App\Helpers;

class ScoreHelper
{
    /**
     * Get the appropriate color class based on the score percentage
     *
     * @param float $score
     * @param float $maxScore
     * @return string
     */
    public static function getScoreColor($score, $maxScore = 100)
    {
        $percentage = ($maxScore > 0) ? ($score / $maxScore) * 100 : 0;

        if ($percentage >= 90) {
            return 'bg-success';
        } elseif ($percentage >= 80) {
            return 'bg-info';
        } elseif ($percentage >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }

    /**
     * Get the appropriate text color class based on the score percentage
     *
     * @param float $score
     * @param float $maxScore
     * @return string
     */
    public static function getScoreTextColor($score, $maxScore = 100)
    {
        $percentage = ($maxScore > 0) ? ($score / $maxScore) * 100 : 0;

        if ($percentage >= 90) {
            return 'text-success';
        } elseif ($percentage >= 80) {
            return 'text-info';
        } elseif ($percentage >= 60) {
            return 'text-warning';
        } else {
            return 'text-danger';
        }
    }

    /**
     * Get the appropriate progress bar class based on the score percentage
     *
     * @param float $score
     * @param float $maxScore
     * @return string
     */
    public static function getProgressBarClass($score, $maxScore = 100)
    {
        $percentage = ($maxScore > 0) ? ($score / $maxScore) * 100 : 0;

        if ($percentage >= 90) {
            return 'progress-bar-success';
        } elseif ($percentage >= 80) {
            return 'progress-bar-info';
        } elseif ($percentage >= 60) {
            return 'progress-bar-warning';
        } else {
            return 'progress-bar-danger';
        }
    }

    /**
     * Format the score as a percentage
     *
     * @param float $score
     * @param float $maxScore
     * @return string
     */
    public static function formatScorePercentage($score, $maxScore = 100)
    {
        if ($maxScore <= 0) {
            return '0%';
        }

        $percentage = ($score / $maxScore) * 100;
        return number_format($percentage, 1) . '%';
    }

    /**
     * Get the appropriate badge class based on pass/fail status
     *
     * @param bool $passed
     * @return string
     */
    public static function getPassFailBadgeClass($passed)
    {
        return $passed ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get the appropriate text for pass/fail status
     *
     * @param bool $passed
     * @return string
     */
    public static function getPassFailText($passed)
    {
        return $passed ? 'ناجح' : 'راسب';
    }

    /**
     * Get the appropriate color class based on pass rate percentage
     *
     * @param float $passRate
     * @return string
     */
    public static function getPassRateColor($passRate)
    {
        if ($passRate >= 90) {
            return 'success';
        } elseif ($passRate >= 80) {
            return 'info';
        } elseif ($passRate >= 60) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Get the appropriate text color class based on pass rate percentage
     *
     * @param float $passRate
     * @return string
     */
    public static function getPassRateTextColor($passRate)
    {
        if ($passRate >= 90) {
            return 'text-success';
        } elseif ($passRate >= 80) {
            return 'text-info';
        } elseif ($passRate >= 60) {
            return 'text-warning';
        } else {
            return 'text-danger';
        }
    }

    /**
     * Get the appropriate background color class based on pass rate percentage
     *
     * @param float $passRate
     * @return string
     */
    public static function getPassRateBackgroundColor($passRate)
    {
        if ($passRate >= 90) {
            return 'bg-success';
        } elseif ($passRate >= 80) {
            return 'bg-info';
        } elseif ($passRate >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }
} 