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
        'student_id',
        'course_id',
        'midterm_grade',
        'assignment_grade',
        'final_grade',
        'score',
        'gpa',
        'grade',
        'submitted',
        'submission_date',
        'updated_by',
        'comments'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'midterm_grade' => 'decimal:2',
        'assignment_grade' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'score' => 'decimal:2',
        'gpa' => 'decimal:2',
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
     * Get the exam submissions for this grade.
     */
    public function examSubmissions()
    {
        return $this->hasMany(StudentExamAttempt::class, 'student_id', 'student_id')
                    ->whereHas('exam', function($query) {
                        $query->where('course_id', $this->course_id);
                    });
    }

    /**
     * Calculate the online exam percentage.
     * 
     * @return float|null
     */
    public function getOnlineExamPercentageAttribute()
    {
        if (!$this->online_exam_total || !$this->online_exam_grade) {
            return null;
        }
        
        return ($this->online_exam_grade / $this->online_exam_total) * 100;
    }

    /**
     * Calculate the paper exam percentage.
     * 
     * @return float|null
     */
    public function getPaperExamPercentageAttribute()
    {
        if (!$this->paper_exam_total || !$this->paper_exam_grade) {
            return null;
        }
        
        return ($this->paper_exam_grade / $this->paper_exam_total) * 100;
    }

    /**
     * Calculate the practical percentage.
     * 
     * @return float|null
     */
    public function getPracticalPercentageAttribute()
    {
        if (!$this->practical_total || !$this->practical_grade) {
            return null;
        }
        
        return ($this->practical_grade / $this->practical_total) * 100;
    }

    /**
     * Calculate the total percentage.
     * 
     * @return float|null
     */
    public function getTotalPercentageAttribute()
    {
        if (!$this->total_possible || !$this->total_grade) {
            return null;
        }
        
        return ($this->total_grade / $this->total_possible) * 100;
    }

    /**
     * Get the letter grade based on the percentage.
     * 
     * @return string
     */
    public function getLetterGradeAttribute()
    {
        if (!$this->is_final || !$this->total_possible || !$this->total_grade) {
            return '-';
        }

        $percentage = $this->getTotalPercentageAttribute();

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
    public function getLetterGradeColorAttribute()
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
     * Calculate and update the total grade.
     * 
     * @return void
     */
    public function calculateTotalGrade()
    {
        $onlineGrade = $this->online_exam_grade ?? 0;
        $paperGrade = $this->paper_exam_grade ?? 0;
        $practicalGrade = $this->practical_grade ?? 0;
        
        $this->total_grade = $onlineGrade + $paperGrade + $practicalGrade;
        
        $onlineTotal = $this->online_exam_total ?? 0;
        $paperTotal = $this->paper_exam_total ?? 0;
        $practicalTotal = $this->practical_total ?? 0;
        
        $this->total_possible = $onlineTotal + $paperTotal + $practicalTotal;
        
        $this->save();
    }

    /**
     * Update online exam grades based on submissions.
     * 
     * @return void
     */
    public function updateOnlineExamGrades()
    {
        $courseId = $this->course_id;
        $studentId = $this->student_id;
        
        // Obtener todos los exámenes de este curso
        $exams = Exam::where('course_id', $courseId)->get();
        
        if ($exams->isEmpty()) {
            return;
        }
        
        $totalMarks = 0;
        $totalPossible = 0;
        
        foreach ($exams as $exam) {
            // Buscar el intento de este examen para este estudiante
            $attempt = StudentExamAttempt::where('student_id', $studentId)
                ->where('exam_id', $exam->id)
                ->where('status', 'graded')
                ->first();
            
            if ($attempt) {
                $totalMarks += $attempt->total_marks_obtained ?? 0;
                $totalPossible += $attempt->total_possible_marks ?? 0;
            }
        }
        
        $this->online_exam_grade = $totalMarks;
        $this->online_exam_total = $totalPossible;
        
        // Recalcular el total
        $this->calculateTotalGrade();
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
     * Scope a query to only include finalized grades.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinalized($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope a query to only include non-finalized grades.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotFinalized($query)
    {
        return $query->where('is_final', false);
    }

    /**
     * Calculate and update the total score and letter grade.
     */
    public function calculateScore()
    {
        $midterm = $this->midterm_grade ?? 0;
        $assignment = $this->assignment_grade ?? 0;
        $final = $this->final_grade ?? 0;

        // Calculate total score (assuming equal weights, adjust as needed)
        $this->score = ($midterm * 0.3) + ($assignment * 0.3) + ($final * 0.4);

        // Calculate letter grade
        $this->grade = $this->calculateLetterGrade($this->score);
        
        // Calculate GPA
        $this->gpa = $this->calculateGPA($this->grade);

        return $this;
    }

    /**
     * Calculate letter grade based on score.
     */
    protected function calculateLetterGrade($score)
    {
        if ($score >= 95) return 'A+';
        if ($score >= 90) return 'A';
        if ($score >= 85) return 'A-';
        if ($score >= 80) return 'B+';
        if ($score >= 75) return 'B';
        if ($score >= 70) return 'B-';
        if ($score >= 65) return 'C+';
        if ($score >= 60) return 'C';
        if ($score >= 55) return 'C-';
        if ($score >= 50) return 'D+';
        if ($score >= 45) return 'D';
        return 'F';
    }

    /**
     * Calculate GPA based on letter grade.
     */
    protected function calculateGPA($grade)
    {
        $gpaScale = [
            'A+' => 4.00,
            'A'  => 4.00,
            'A-' => 3.70,
            'B+' => 3.30,
            'B'  => 3.00,
            'B-' => 2.70,
            'C+' => 2.30,
            'C'  => 2.00,
            'C-' => 1.70,
            'D+' => 1.30,
            'D'  => 1.00,
            'F'  => 0.00,
        ];

        return $gpaScale[$grade] ?? 0.00;
    }
}
