# Laravel Authorization Policies Documentation

## Overview

This document outlines the comprehensive authorization policy system implemented for the CertChain Learning Platform. The system uses Laravel's Policy-based authorization to control access to various resources based on user roles and relationships.

## User Roles

The platform supports three main user roles:
- **ADMIN**: Full system access and management capabilities
- **TEACHER**: Can create and manage their own courses, view student progress
- **STUDENT**: Can enroll in courses, view their own progress and certificates

## Policy Structure

All policies follow Laravel's standard authorization pattern, implementing methods that correspond to common CRUD operations and domain-specific actions.

---

## CoursePolicy

Controls access to course-related operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can list/view all courses
- **ADMIN**: ✅ Can view all courses
- **TEACHER**: ✅ Can view all courses
- **STUDENT**: ✅ Can view all courses

#### `view(User $user, Course $course): bool`
- **Purpose**: Determine if user can view a specific course
- **ADMIN**: ✅ Can view any course
- **TEACHER**: ✅ Can view any course
- **STUDENT**: ✅ Can view any course

#### `create(User $user): bool`
- **Purpose**: Determine if user can create new courses
- **ADMIN**: ✅ Can create courses
- **TEACHER**: ✅ Can create courses
- **STUDENT**: ❌ Cannot create courses

#### `update(User $user, Course $course): bool`
- **Purpose**: Determine if user can update a specific course
- **ADMIN**: ✅ Can update any course
- **TEACHER**: ✅ Can update only their own courses (`$course->teacher_id === $user->id`)
- **STUDENT**: ❌ Cannot update courses

#### `delete(User $user, Course $course): bool`
- **Purpose**: Determine if user can delete a specific course
- **ADMIN**: ✅ Can delete any course
- **TEACHER**: ✅ Can delete only their own courses
- **STUDENT**: ❌ Cannot delete courses

#### `enroll(User $user, Course $course): bool`
- **Purpose**: Determine if user can enroll in a course
- **ADMIN**: ❌ Cannot enroll (administrative role)
- **TEACHER**: ❌ Cannot enroll (teaching role)
- **STUDENT**: ✅ Can enroll in courses

#### `viewStudents(User $user, Course $course): bool`
- **Purpose**: Determine if user can view enrolled students for a course
- **ADMIN**: ✅ Can view students in any course
- **TEACHER**: ✅ Can view students only in their own courses
- **STUDENT**: ❌ Cannot view enrolled students

#### `manageContent(User $user, Course $course): bool`
- **Purpose**: Determine if user can manage course content (modules, lessons)
- **ADMIN**: ✅ Can manage content in any course
- **TEACHER**: ✅ Can manage content only in their own courses
- **STUDENT**: ❌ Cannot manage course content

---

## UserPolicy

Controls access to user management operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can list all users
- **ADMIN**: ✅ Can list all users
- **TEACHER**: ❌ Cannot list all users
- **STUDENT**: ❌ Cannot list all users

#### `view(User $user, User $model): bool`
- **Purpose**: Determine if user can view a specific user's profile
- **ADMIN**: ✅ Can view any user's profile
- **TEACHER**: ❌ Cannot view other user profiles
- **STUDENT**: ✅ Can view only their own profile (`$user->id === $model->id`)

#### `create(User $user): bool`
- **Purpose**: Determine if user can create new users
- **ADMIN**: ✅ Can create users
- **TEACHER**: ❌ Cannot create users
- **STUDENT**: ❌ Cannot create users

#### `update(User $user, User $model): bool`
- **Purpose**: Determine if user can update a specific user
- **ADMIN**: ✅ Can update any user
- **TEACHER**: ❌ Cannot update users
- **STUDENT**: ✅ Can update only their own profile

#### `delete(User $user, User $model): bool`
- **Purpose**: Determine if user can delete a specific user
- **ADMIN**: ✅ Can delete any user except themselves (`$user->id !== $model->id`)
- **TEACHER**: ❌ Cannot delete users
- **STUDENT**: ❌ Cannot delete users

#### `filterByRole(User $user): bool`
- **Purpose**: Determine if user can filter users by role
- **ADMIN**: ✅ Can filter users by role
- **TEACHER**: ✅ Can filter users by role
- **STUDENT**: ❌ Cannot filter users by role

#### `viewCertificates(User $user, User $model): bool`
- **Purpose**: Determine if user can view another user's certificates
- **ADMIN**: ✅ Can view any user's certificates
- **TEACHER**: ✅ Can view any user's certificates
- **STUDENT**: ✅ Can view only their own certificates

#### `viewReviews(User $user, User $model): bool`
- **Purpose**: Determine if user can view another user's reviews
- **ADMIN**: ✅ Can view any user's reviews
- **TEACHER**: ✅ Can view any user's reviews
- **STUDENT**: ✅ Can view only their own reviews

---

## ModulePolicy

Controls access to course module operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can view modules
- **All Roles**: ✅ All authenticated users can view modules

#### `view(User $user, Module $module): bool`
- **Purpose**: Determine if user can view a specific module
- **ADMIN**: ✅ Can view any module
- **TEACHER**: ✅ Can view modules in their own courses
- **STUDENT**: ✅ Can view modules in enrolled courses

#### `create(User $user): bool`
- **Purpose**: Determine if user can create modules
- **ADMIN**: ✅ Can create modules
- **TEACHER**: ✅ Can create modules
- **STUDENT**: ❌ Cannot create modules

#### `update(User $user, Module $module): bool`
- **Purpose**: Determine if user can update a specific module
- **ADMIN**: ✅ Can update any module
- **TEACHER**: ✅ Can update modules in their own courses
- **STUDENT**: ❌ Cannot update modules

#### `delete(User $user, Module $module): bool`
- **Purpose**: Determine if user can delete a specific module
- **ADMIN**: ✅ Can delete any module
- **TEACHER**: ✅ Can delete modules in their own courses
- **STUDENT**: ❌ Cannot delete modules

---

## LessonPolicy

Controls access to lesson operations with special handling for free vs paid content.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can view lessons
- **All Roles**: ✅ All authenticated users can view lessons

#### `view(User $user, Lesson $lesson): bool`
- **Purpose**: Determine if user can view a specific lesson
- **ADMIN**: ✅ Can view any lesson
- **TEACHER**: ✅ Can view lessons in their own courses
- **STUDENT**: ✅ Can view lessons in enrolled courses (with free/paid logic)

#### `create(User $user): bool`
- **Purpose**: Determine if user can create lessons
- **ADMIN**: ✅ Can create lessons
- **TEACHER**: ✅ Can create lessons
- **STUDENT**: ❌ Cannot create lessons

#### `update(User $user, Lesson $lesson): bool`
- **Purpose**: Determine if user can update a specific lesson
- **ADMIN**: ✅ Can update any lesson
- **TEACHER**: ✅ Can update lessons in their own courses
- **STUDENT**: ❌ Cannot update lessons

#### `delete(User $user, Lesson $lesson): bool`
- **Purpose**: Determine if user can delete a specific lesson
- **ADMIN**: ✅ Can delete any lesson
- **TEACHER**: ✅ Can delete lessons in their own courses
- **STUDENT**: ❌ Cannot delete lessons

---

## QuizPolicy

Controls access to quiz operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can view quizzes
- **All Roles**: ✅ All authenticated users can view quizzes

#### `view(User $user, Quiz $quiz): bool`
- **Purpose**: Determine if user can view a specific quiz
- **ADMIN**: ✅ Can view any quiz
- **TEACHER**: ✅ Can view quizzes in their own courses
- **STUDENT**: ✅ Can view quizzes in enrolled courses

#### `create(User $user, Course $course): bool`
- **Purpose**: Determine if user can create quizzes
- **ADMIN**: ✅ Can create quizzes in any course
- **TEACHER**: ✅ Can create quizzes in their own courses
- **STUDENT**: ❌ Cannot create quizzes

#### `update(User $user, Quiz $quiz): bool`
- **Purpose**: Determine if user can update a specific quiz
- **ADMIN**: ✅ Can update any quiz
- **TEACHER**: ✅ Can update quizzes in their own courses
- **STUDENT**: ❌ Cannot update quizzes

#### `delete(User $user, Quiz $quiz): bool`
- **Purpose**: Determine if user can delete a specific quiz
- **ADMIN**: ✅ Can delete any quiz
- **TEACHER**: ✅ Can delete quizzes in their own courses
- **STUDENT**: ❌ Cannot delete quizzes

#### `startAttempt(User $user, Quiz $quiz): bool`
- **Purpose**: Determine if user can start a quiz attempt
- **ADMIN**: ❌ Cannot take quizzes
- **TEACHER**: ❌ Cannot take quizzes
- **STUDENT**: ✅ Can start quiz attempts in enrolled courses

---

## EnrollmentPolicy

Controls access to course enrollment operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can view enrollments
- **ADMIN**: ✅ Can view all enrollments
- **TEACHER**: ✅ Can view enrollments
- **STUDENT**: ❌ Cannot view all enrollments

#### `view(User $user, Enrollment $enrollment): bool`
- **Purpose**: Determine if user can view a specific enrollment
- **ADMIN**: ✅ Can view any enrollment
- **TEACHER**: ✅ Can view enrollments in their own courses
- **STUDENT**: ✅ Can view only their own enrollments

#### `create(User $user): bool`
- **Purpose**: Determine if user can create enrollments
- **ADMIN**: ✅ Can create enrollments (for any user)
- **TEACHER**: ❌ Cannot create enrollments
- **STUDENT**: ✅ Can create their own enrollments

#### `update(User $user, Enrollment $enrollment): bool`
- **Purpose**: Determine if user can update enrollments
- **ADMIN**: ✅ Can update any enrollment
- **TEACHER**: ❌ Cannot update enrollments
- **STUDENT**: ❌ Cannot update enrollments

#### `delete(User $user, Enrollment $enrollment): bool`
- **Purpose**: Determine if user can cancel enrollments
- **ADMIN**: ✅ Can cancel any enrollment
- **TEACHER**: ❌ Cannot cancel enrollments
- **STUDENT**: ✅ Can cancel their own enrollments

---

## CertificatePolicy

Controls access to certificate operations.

### Methods

#### `viewAny(User $user): bool`
- **Purpose**: Determine if user can view certificates
- **ADMIN**: ✅ Can view all certificates
- **TEACHER**: ✅ Can view certificates for their courses
- **STUDENT**: ❌ Cannot view all certificates

#### `view(User $user, Certificate $certificate): bool`
- **Purpose**: Determine if user can view a specific certificate
- **ADMIN**: ✅ Can view any certificate
- **TEACHER**: ✅ Can view certificates for their courses
- **STUDENT**: ✅ Can view only their own certificates

#### `create(User $user): bool`
- **Purpose**: Determine if user can create certificates
- **ADMIN**: ✅ Can create certificates (manual creation)
- **TEACHER**: ❌ Cannot create certificates
- **STUDENT**: ❌ Cannot create certificates

#### `update(User $user, Certificate $certificate): bool`
- **Purpose**: Determine if user can update certificates
- **ADMIN**: ✅ Can update any certificate
- **TEACHER**: ❌ Cannot update certificates
- **STUDENT**: ❌ Cannot update certificates

#### `delete(User $user, Certificate $certificate): bool`
- **Purpose**: Determine if user can delete certificates
- **ADMIN**: ✅ Can delete any certificate
- **TEACHER**: ❌ Cannot delete certificates
- **STUDENT**: ❌ Cannot delete certificates

---

## Policy Registration

All policies are registered in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    Course::class => CoursePolicy::class,
    User::class => UserPolicy::class,
    Module::class => ModulePolicy::class,
    Lesson::class => LessonPolicy::class,
    Quiz::class => QuizPolicy::class,
    Enrollment::class => EnrollmentPolicy::class,
    Certificate::class => CertificatePolicy::class,
];
```

## Usage in Controllers

Policies are used in controllers via the `authorize()` method:

```php
public function store(Request $request)
{
    $this->authorize('create', Course::class);
    // ... rest of the method
}

public function update(Request $request, Course $course)
{
    $this->authorize('update', $course);
    // ... rest of the method
}
```

## Security Considerations

1. **Role-Based Access**: All policies enforce strict role-based permissions
2. **Ownership Checks**: Teachers can only modify their own courses and content
3. **Enrollment Verification**: Students can only access content in courses they're enrolled in
4. **Free Content Access**: Students have access to free lessons even in paid courses
5. **Administrative Overrides**: ADMIN role has full access to all resources

## Testing

Each policy method should be tested with different user roles to ensure proper authorization logic. Use Laravel's `Gate::allows()` or policy testing methods in your test suite.
