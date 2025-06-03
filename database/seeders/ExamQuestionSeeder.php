<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamQuestion;
use App\Models\Exam;

class ExamQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if Exam ID 3 exists
        $exam = Exam::find(3);

        if ($exam) {
            $questions = [
                [
                    'exam_id' => 3,
                    'question_text' => 'What is the capital of France?',
                    'question_type' => 'multiple_choice',
                    'options' => json_encode([
                        ['key' => 'a', 'value' => 'Berlin'],
                        ['key' => 'b', 'value' => 'Madrid'],
                        ['key' => 'c', 'value' => 'Paris'],
                        ['key' => 'd', 'value' => 'Rome']
                    ]),
                    'correct_answer' => 'c',
                    'marks' => 5,
                    'order' => 1,
                ],
                [
                    'exam_id' => 3,
                    'question_text' => 'The Earth is flat.',
                    'question_type' => 'true_false',
                    'options' => json_encode([
                        ['key' => 'true', 'value' => 'True'],
                        ['key' => 'false', 'value' => 'False']
                    ]),
                    'correct_answer' => 'false',
                    'marks' => 3,
                    'order' => 2,
                ],
                [
                    'exam_id' => 3,
                    'question_text' => 'Explain the theory of relativity in simple terms.',
                    'question_type' => 'open_ended',
                    'options' => null,
                    'correct_answer' => null, // Or a model answer key if applicable
                    'marks' => 10,
                    'order' => 3,
                ],
                [
                    'exam_id' => 3,
                    'question_text' => 'Which planet is known as the Red Planet?',
                    'question_type' => 'multiple_choice',
                    'options' => json_encode([
                        ['key' => 'a', 'value' => 'Earth'],
                        ['key' => 'b', 'value' => 'Mars'],
                        ['key' => 'c', 'value' => 'Jupiter'],
                        ['key' => 'd', 'value' => 'Saturn']
                    ]),
                    'correct_answer' => 'b',
                    'marks' => 5,
                    'order' => 4,
                ],
                [
                    'exam_id' => 3,
                    'question_text' => 'Water boils at 100 degrees Celsius at sea level.',
                    'question_type' => 'true_false',
                     'options' => json_encode([
                        ['key' => 'true', 'value' => 'True'],
                        ['key' => 'false', 'value' => 'False']
                    ]),
                    'correct_answer' => 'true',
                    'marks' => 2,
                    'order' => 5,
                ]
            ];

            foreach ($questions as $question) {
                ExamQuestion::create($question);
            }

            $this->command->info('Exam questions for Exam ID 3 seeded successfully!');
        } else {
            $this->command->warn('Exam with ID 3 not found. No questions seeded for this exam.');
        }
    }
} 