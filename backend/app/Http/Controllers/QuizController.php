<?php namespace App\Http\Controllers;
use App\Services\QuizService;
use App\Services\QuizAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller {
    protected $quizService;
    protected $attemptService;
    
    public function __construct(QuizService $quizService, QuizAttemptService $attemptService) {
        $this->quizService = $quizService;
        $this->attemptService = $attemptService;
    }
    
    public function index($courseId) {
        return response()->json($this->quizService->getQuizzesByCourse($courseId));
    }
    
    public function store(Request $request, $courseId) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quiz_type' => 'in:PRACTICE,GRADED,FINAL',
            'passing_score' => 'numeric|min:0|max:100',
            'time_limit' => 'nullable|integer|min:1',
            'max_attempts' => 'nullable|integer|min:1',
            'order_index' => 'required|integer',
        ]);
        
        $validated['course_id'] = $courseId;
        $quiz = $this->quizService->createQuiz($validated);
        return response()->json($quiz, 201);
    }
    
    public function show($quizId) {
        return response()->json($this->quizService->getQuizById($quizId));
    }
    
    public function update(Request $request, $quizId) {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'quiz_type' => 'sometimes|in:PRACTICE,GRADED,FINAL',
            'passing_score' => 'sometimes|numeric|min:0|max:100',
            'time_limit' => 'sometimes|integer|min:1',
            'max_attempts' => 'sometimes|integer|min:1',
            'order_index' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);
        
        $quiz = $this->quizService->updateQuiz($quizId, $validated);
        return response()->json($quiz);
    }
    
    public function destroy($quizId) {
        $this->quizService->deleteQuiz($quizId);
        return response()->json(['message' => 'Quiz deleted successfully']);
    }
    
    public function startAttempt($quizId) {
        $attempt = $this->attemptService->startAttempt(Auth::id(), $quizId);
        $quiz = $this->quizService->getQuizById($quizId);
        return response()->json(['attempt' => $attempt, 'questions' => $quiz->questions]);
    }
    
    public function submitAttempt(Request $request, $attemptId) {
        $validated = $request->validate(['answers' => 'required|array', 'time_spent' => 'nullable|integer']);
        $attempt = $this->attemptService->submitAttemptWithDetails($attemptId, $validated['answers']);
        return response()->json([
            'score' => $attempt->score,
            'passed' => $attempt->is_passed,
            'message' => $attempt->is_passed ? 'Quiz passed!' : 'Quiz failed'
        ]);
    }
    
    public function attemptsHistory($quizId) {
        return response()->json($this->attemptService->getAttemptHistory(Auth::id(), $quizId));
    }
}
