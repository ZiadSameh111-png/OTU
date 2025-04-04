<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'student_id',
        'midterm_grade',
        'assignment_grade',
        'final_grade',
        'submitted',
        'submission_date',
        'updated_by',
        'comments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'midterm_grade' => 'float',
        'assignment_grade' => 'float',
        'final_grade' => 'float',
        'submitted' => 'boolean',
        'submission_date' => 'datetime',
    ];

    /**
     * Get the course that the grade belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the student (user) that the grade belongs to.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the teacher (user) who last updated the grade.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate the total grade (sum of assignment and final grades).
     * 
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->midterm_grade + $this->assignment_grade + $this->final_grade;
    }

    /**
     * Get the letter grade based on the percentage.
     * 
     * @return string
     */
    public function getLetterGradeAttribute()
    {
        if (!$this->submitted || is_null($this->midterm_grade) || is_null($this->assignment_grade) || is_null($this->final_grade)) {
            return '-';
        }

        $course = $this->course;
        $maxTotal = $course->midterm_grade + $course->assignment_grade + $course->final_grade;
        $percentage = ($this->getTotalAttribute() / $maxTotal) * 100;

        if ($percentage >= 95) return 'A+';
        if ($percentage >= 90) return 'A';
        if ($percentage >= 85) return 'B+';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 75) return 'C+';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 65) return 'D+';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Get the appropriate CSS class for the letter grade badge.
     * 
     * @return string
     */
    public function getLetterGradeColor()
    {
        $letterGrade = $this->getLetterGradeAttribute();
        
        if ($letterGrade == '-') return 'secondary';
        if (in_array($letterGrade, ['A+', 'A'])) return 'success';
        if (in_array($letterGrade, ['B+', 'B'])) return 'primary';
        if (in_array($letterGrade, ['C+', 'C'])) return 'info';
        if (in_array($letterGrade, ['D+', 'D'])) return 'warning';
        if ($letterGrade == 'F') return 'danger';
        
        return 'secondary';
    }

    /**
     * Scope a query to only include grades for a specific student.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $studentId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope a query to only include grades for a specific course.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $courseId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Scope a query to only include submitted grades.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubmitted($query)
    {
        return $query->where('submitted', true);
    }

    /**
     * Scope a query to only include unsubmitted grades.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsubmitted($query)
    {
        return $query->where('submitted', false);
    }
}
