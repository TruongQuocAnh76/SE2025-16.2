<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get quizzes
        $vueFundamentalsQuiz = DB::table('quizzes')->where('title', 'Vue.js Fundamentals Quiz')->first();
        $vueComponentsQuiz = DB::table('quizzes')->where('title', 'Vue.js Components Assessment')->first();
        $webKnowledgeCheck = DB::table('quizzes')->where('title', 'Web Development Knowledge Check')->first();

        DB::table('questions')->insert([
            // Vue.js Fundamentals Quiz Questions
            [
                'id' => Str::uuid(),
                'question_text' => 'What is Vue.js?',
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => 1,
                'order_index' => 1,
                'options' => json_encode([
                    'A JavaScript framework for building user interfaces',
                    'A CSS preprocessor',
                    'A database management system',
                    'A server-side programming language'
                ]),
                'correct_answer' => 'A JavaScript framework for building user interfaces',
                'explanation' => 'Vue.js is a progressive JavaScript framework for building user interfaces and single-page applications.',
                'quiz_id' => $vueFundamentalsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Which directive is used for two-way data binding in Vue.js?',
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => 1,
                'order_index' => 2,
                'options' => json_encode([
                    'v-bind',
                    'v-model',
                    'v-if',
                    'v-for'
                ]),
                'correct_answer' => 'v-model',
                'explanation' => 'The v-model directive creates two-way data bindings on form input elements.',
                'quiz_id' => $vueFundamentalsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Vue.js follows the MVVM pattern.',
                'question_type' => 'TRUE_FALSE',
                'points' => 1,
                'order_index' => 3,
                'options' => json_encode(['True', 'False']),
                'correct_answer' => 'True',
                'explanation' => 'Vue.js follows the Model-View-ViewModel (MVVM) architectural pattern.',
                'quiz_id' => $vueFundamentalsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Which of the following are core Vue.js directives? (Select all that apply)',
                'question_type' => 'CHECKBOX',
                'points' => 2,
                'order_index' => 4,
                'options' => json_encode([
                    'v-if',
                    'v-for',
                    'v-bind',
                    'ng-repeat'
                ]),
                'correct_answer' => json_encode(['v-if', 'v-for', 'v-bind']),
                'explanation' => 'v-if, v-for, and v-bind are core Vue.js directives. ng-repeat is from AngularJS.',
                'quiz_id' => $vueFundamentalsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Explain the concept of reactivity in Vue.js.',
                'question_type' => 'SHORT_ANSWER',
                'points' => 3,
                'order_index' => 5,
                'options' => null,
                'correct_answer' => 'Reactivity in Vue.js means that when data changes, the view automatically updates to reflect those changes.',
                'explanation' => 'Reactivity is a core concept where Vue automatically tracks data dependencies and updates the DOM when data changes.',
                'quiz_id' => $vueFundamentalsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Vue.js Components Assessment Questions
            [
                'id' => Str::uuid(),
                'question_text' => 'How do you pass data from a parent component to a child component?',
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => 2,
                'order_index' => 1,
                'options' => json_encode([
                    'Using props',
                    'Using events',
                    'Using slots',
                    'Using refs'
                ]),
                'correct_answer' => 'Using props',
                'explanation' => 'Props are used to pass data from parent components to child components in Vue.js.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Components in Vue.js can only have one root element.',
                'question_type' => 'TRUE_FALSE',
                'points' => 1,
                'order_index' => 2,
                'options' => json_encode(['True', 'False']),
                'correct_answer' => 'False',
                'explanation' => 'In Vue 3, components can have multiple root nodes (Fragments). In Vue 2, they were limited to one.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'What is the purpose of the $emit method in Vue.js?',
                'question_type' => 'SHORT_ANSWER',
                'points' => 2,
                'order_index' => 3,
                'options' => null,
                'correct_answer' => 'The $emit method is used to trigger custom events from child components to communicate with parent components.',
                'explanation' => '$emit enables upward communication from child to parent components through custom events.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Describe the difference between Props and State.',
                'question_type' => 'ESSAY',
                'points' => 5,
                'order_index' => 4,
                'options' => null,
                'correct_answer' => 'Props are passed down from parent to child and are immutable from the childs perspective. State is internal data managed by the component itself.',
                'explanation' => 'Props are external/read-only, State is internal/mutable.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'What are Vue Lifecycle Hooks? Name three.',
                'question_type' => 'SHORT_ANSWER',
                'points' => 3,
                'order_index' => 5,
                'options' => null,
                'correct_answer' => 'Lifecycle hooks are methods that allow you to run code at specific stages of a components life.',
                'explanation' => 'Hooks allow execution of code at creation, mounting, updating, and destruction phases.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Explain the concept of Slots in Vue.js.',
                'question_type' => 'ESSAY',
                'points' => 5,
                'order_index' => 6,
                'options' => null,
                'correct_answer' => 'Slots are a mechanism for content distribution. They allow you to compose components by injecting content from a parent into a child component template.',
                'explanation' => 'Slots allow flexible content injection into components.',
                'quiz_id' => $vueComponentsQuiz->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Web Development Knowledge Check Questions
            [
                'id' => Str::uuid(),
                'question_text' => 'What does HTML stand for?',
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => 1,
                'order_index' => 1,
                'options' => json_encode([
                    'HyperText Markup Language',
                    'High Tech Modern Language',
                    'Home Tool Markup Language',
                    'Hyperlink and Text Markup Language'
                ]),
                'correct_answer' => 'HyperText Markup Language',
                'explanation' => 'HTML stands for HyperText Markup Language, which is the standard markup language for creating web pages.',
                'quiz_id' => $webKnowledgeCheck->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'CSS is used for styling web pages.',
                'question_type' => 'TRUE_FALSE',
                'points' => 1,
                'order_index' => 2,
                'options' => json_encode(['True', 'False']),
                'correct_answer' => 'True',
                'explanation' => 'CSS (Cascading Style Sheets) is used to style and layout web pages.',
                'quiz_id' => $webKnowledgeCheck->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Which of the following are valid CSS units? (Select all that apply)',
                'question_type' => 'CHECKBOX',
                'points' => 2,
                'order_index' => 3,
                'options' => json_encode([
                    'px',
                    'rem',
                    'em',
                    'hz'
                ]),
                'correct_answer' => json_encode(['px', 'rem', 'em']),
                'explanation' => 'px, rem, and em are valid CSS units. hz is a unit for frequency, not typically used for styling layout/sizes.',
                'quiz_id' => $webKnowledgeCheck->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'Which of the following is NOT a JavaScript data type?',
                'question_type' => 'MULTIPLE_CHOICE',
                'points' => 1,
                'order_index' => 4,
                'options' => json_encode([
                    'String',
                    'Boolean',
                    'Float',
                    'Number'
                ]),
                'correct_answer' => 'Float',
                'explanation' => 'JavaScript has Number type for all numeric values. Float is not a separate data type in JavaScript.',
                'quiz_id' => $webKnowledgeCheck->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'question_text' => 'What is the DOM in web development?',
                'question_type' => 'SHORT_ANSWER',
                'points' => 3,
                'order_index' => 5,
                'options' => null,
                'correct_answer' => 'The Document Object Model (DOM) is a programming interface for HTML and XML documents.',
                'explanation' => 'The DOM represents the page so that programs can change the document structure, style, and content.',
                'quiz_id' => $webKnowledgeCheck->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
