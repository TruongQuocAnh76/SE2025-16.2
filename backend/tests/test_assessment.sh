#!/bin/bash

# Assessment Module Testing Script
# Hướng dẫn test API cho Assessment Module

BASE_URL="http://localhost:8000/api"

echo "==================================="
echo " ASSESSMENT MODULE TESTING"
echo "==================================="
echo

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW} STEP 1: GET TEST DATA FROM DATABASE${NC}"
echo "Lấy dữ liệu test từ database..."

# Get sample IDs from database
COURSE_ID=$(cd /home/anbt/projects/SE2025-16.2/backend && php artisan tinker --execute="echo \App\Models\Course::first()->id;")
QUIZ_ID=$(cd /home/anbt/projects/SE2025-16.2/backend && php artisan tinker --execute="echo \App\Models\Quiz::first()->id;")
STUDENT_ID=$(cd /home/anbt/projects/SE2025-16.2/backend && php artisan tinker --execute="echo \App\Models\User::where('role', 'STUDENT')->first()->id;")

echo "Course ID: $COURSE_ID"
echo "Quiz ID: $QUIZ_ID"  
echo "Student ID: $STUDENT_ID"
echo

echo -e "${YELLOW} STEP 2: TEST API ENDPOINTS${NC}"
echo

echo -e "${GREEN}1. Test GET /quizzes/course/{courseId} - List quizzes in course${NC}"
echo "curl -X GET $BASE_URL/quizzes/course/$COURSE_ID"
echo "Expected: List of quizzes in the course"
echo

echo -e "${GREEN}2. Test GET /quizzes/{quizId} - Get quiz details${NC}"
echo "curl -X GET $BASE_URL/quizzes/$QUIZ_ID"
echo "Expected: Quiz details with questions (without correct answers)"
echo

echo -e "${GREEN}3. Test GET /quizzes/{quizId}/questions - Get questions for quiz${NC}"
echo "curl -X GET $BASE_URL/quizzes/$QUIZ_ID/questions"
echo "Expected: List of questions for the quiz"
echo

echo -e "${GREEN}4. Test GET /quizzes/{quizId}/stats - Get student stats${NC}"
echo "curl -X GET '$BASE_URL/quizzes/$QUIZ_ID/stats?student_id=$STUDENT_ID'"
echo "Expected: Student statistics for the quiz"
echo

echo -e "${GREEN}5. Test GET /quizzes/{quizId}/attempts - Get attempt history${NC}"
echo "curl -X GET $BASE_URL/quizzes/$QUIZ_ID/attempts"
echo "Expected: Quiz attempt history for current user"
echo

echo -e "${YELLOW} STEP 3: INTEGRATION TEST FLOW${NC}"
echo

echo -e "${GREEN}Complete Quiz Flow Test:${NC}"
echo "1. Start attempt: POST /quizzes/{quizId}/start"
echo "2. Submit answers: POST /quizzes/attempt/{attemptId}/submit"  
echo "3. Review results: GET /grading/attempts/{attemptId}/review"
echo

echo -e "${YELLOW} STEP 4: MANUAL TESTING WITH POSTMAN/CURL${NC}"
echo

echo -e "${GREEN}Sample cURL commands:${NC}"
echo

# Test 1: Get quizzes in course
echo "# Test 1: Get quizzes in course"
echo "curl -X GET \\"
echo "  '$BASE_URL/quizzes/course/$COURSE_ID' \\"
echo "  -H 'Accept: application/json' \\"
echo "  -H 'Content-Type: application/json'"
echo

# Test 2: Get quiz details  
echo "# Test 2: Get quiz details"
echo "curl -X GET \\"
echo "  '$BASE_URL/quizzes/$QUIZ_ID' \\"
echo "  -H 'Accept: application/json'"
echo

# Test 3: Get questions
echo "# Test 3: Get questions for quiz"
echo "curl -X GET \\"
echo "  '$BASE_URL/quizzes/$QUIZ_ID/questions' \\"
echo "  -H 'Accept: application/json'"
echo

# Test 4: Create question (requires admin/teacher)
echo "# Test 4: Create new question"
echo "curl -X POST \\"
echo "  '$BASE_URL/quizzes/$QUIZ_ID/questions' \\"
echo "  -H 'Accept: application/json' \\"
echo "  -H 'Content-Type: application/json' \\"
echo "  -d '{"
echo "    \"question_text\": \"What is Laravel?\","
echo "    \"question_type\": \"MULTIPLE_CHOICE\","
echo "    \"points\": 1,"
echo "    \"order_index\": 1,"
echo "    \"options\": [\"PHP Framework\", \"Database\", \"Language\", \"Server\"],"
echo "    \"correct_answer\": \"PHP Framework\","
echo "    \"explanation\": \"Laravel is a popular PHP web framework\""
echo "  }'"
echo

echo -e "${YELLOW} STEP 5: DATABASE VERIFICATION${NC}"
echo

echo -e "${GREEN}Verify data in database:${NC}"
echo "cd /home/anbt/projects/SE2025-16.2/backend && php artisan tinker"
echo "Then run these commands in tinker:"
echo "Quiz::with('questions')->find('$QUIZ_ID')"
echo "QuizAttempt::with('answers')->where('quiz_id', '$QUIZ_ID')->first()"
echo "Question::where('quiz_id', '$QUIZ_ID')->count()"
echo

echo -e "${YELLOW} STEP 6: SWAGGER DOCUMENTATION${NC}"
echo

echo -e "${GREEN}Access API documentation:${NC}"
echo "Open browser: http://localhost:8000/api/documentation"
echo "This will show all API endpoints with interactive testing"
echo

echo -e "${RED}  IMPORTANT NOTES:${NC}"
echo "1. Server must be running: php artisan serve"
echo "2. Database must be seeded: php artisan migrate:fresh --seed"
echo "3. Authentication is disabled for testing (check middleware)"
echo "4. Use proper Content-Type headers for POST requests"
echo

echo -e "${GREEN} Ready to test Assessment Module!${NC}"