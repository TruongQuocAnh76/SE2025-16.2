# Code Conventions & Contribution Guide

This document outlines the coding standards, formatting rules, and contribution process for the Certchain project.

## Table of Contents

- [General Principles](#general-principles)
- [Backend (Laravel/PHP)](#backend-laravelphp)
- [Frontend (Nuxt/Vue)](#frontend-nuxtvue)
- [Blockchain (Node.js/TypeScript)](#blockchain-nodejstypescript)
- [Git Workflow](#git-workflow)
- [Testing Guidelines](#testing-guidelines)
- [Pull Request Process](#pull-request-process)

---

## General Principles

1. **Write readable code** - Code is read more often than written
2. **Follow existing patterns** - Consistency over personal preference
3. **Document complex logic** - Comments explain "why", not "what"
4. **Write tests** - All new features should have test coverage
5. **Small commits** - Each commit should do one thing well

---

## Backend (Laravel/PHP)

### Code Style

We follow **PSR-12** coding standards with Laravel conventions.

```php
<?php

namespace App\Services;

use App\Models\Course;
use App\Contracts\CourseRepositoryInterface;

class CourseService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    /**
     * Create a new course with modules.
     *
     * @param array $data Course data
     * @param int $teacherId Teacher's user ID
     * @return Course
     */
    public function createCourse(array $data, int $teacherId): Course
    {
        // Implementation
    }
}
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Controllers | PascalCase, singular, `Controller` suffix | `CourseController` |
| Models | PascalCase, singular | `Course`, `QuizAttempt` |
| Services | PascalCase, `Service` suffix | `CertificateService` |
| Migrations | snake_case, descriptive | `create_courses_table` |
| Routes | kebab-case | `/api/courses/{id}/enroll` |
| Database tables | snake_case, plural | `quiz_attempts` |
| Database columns | snake_case | `created_at`, `student_id` |

### File Organization

```
backend/
├── app/
│   ├── Console/         # Artisan commands
│   ├── Contracts/       # Interfaces
│   ├── Http/
│   │   ├── Controllers/ # API controllers
│   │   ├── Middleware/  # HTTP middleware
│   │   └── Requests/    # Form request validation
│   ├── Jobs/            # Queue jobs
│   ├── Models/          # Eloquent models
│   ├── Policies/        # Authorization policies
│   ├── Providers/       # Service providers
│   ├── Repositories/    # Data access layer
│   └── Services/        # Business logic
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── routes/
│   └── api.php          # API routes
└── tests/
    ├── Feature/         # Feature tests
    └── Unit/            # Unit tests
```

### API Response Format

All API responses should follow this structure:

```php
// Success response
return response()->json([
    'success' => true,
    'data' => $result,
    'message' => 'Operation completed successfully'
]);

// Error response
return response()->json([
    'success' => false,
    'error' => 'Error message',
    'errors' => $validationErrors // optional
], 422);
```

### Best Practices

1. **Use dependency injection** - Inject services via constructor
2. **Validate requests** - Use Form Request classes for validation
3. **Use Eloquent relationships** - Avoid raw queries when possible
4. **Queue heavy tasks** - Use jobs for email, blockchain, video processing
5. **Use UUIDs** - All models use UUID primary keys

```php
// Good: Using Form Request
public function store(StoreCourseRequest $request)
{
    return $this->courseService->create($request->validated());
}

// Good: Using relationships
$course->modules()->with('lessons')->get();

// Good: Queue heavy operations
IssueCertificateToBlockchain::dispatch($certificate);
```

---

## Frontend (Nuxt/Vue)

### Code Style

We use **ESLint** with Vue 3 recommended rules and **Prettier** for formatting.

```typescript
<script setup lang="ts">
import { ref, computed } from 'vue'
import type { Course } from '~/types/course'

// Props
const props = defineProps<{
  course: Course
  isEnrolled?: boolean
}>()

// Emits
const emit = defineEmits<{
  enroll: [courseId: string]
}>()

// State
const loading = ref(false)

// Computed
const formattedPrice = computed(() => {
  return props.course.price === 0 
    ? 'Free' 
    : `$${props.course.price}`
})

// Methods
async function handleEnroll() {
  loading.value = true
  try {
    await enrollInCourse(props.course.id)
    emit('enroll', props.course.id)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="course-card">
    <h3>{{ course.title }}</h3>
    <p>{{ formattedPrice }}</p>
    <button 
      :disabled="loading || isEnrolled"
      @click="handleEnroll"
    >
      {{ isEnrolled ? 'Enrolled' : 'Enroll Now' }}
    </button>
  </div>
</template>
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Components | PascalCase | `CourseCard.vue` |
| Composables | camelCase, `use` prefix | `useCourses.ts` |
| Pages | kebab-case | `course-details.vue` |
| Types | PascalCase | `CourseEnrollment` |
| CSS classes | kebab-case (Tailwind) | `course-card`, `bg-primary` |

### File Organization

```
frontend/
├── app/
│   ├── base/              # Shared base layer
│   │   ├── components/    # Shared components
│   │   ├── composables/   # Shared composables
│   │   ├── layouts/       # Base layouts
│   │   ├── middleware/    # Auth middleware
│   │   └── pages/         # Common pages (admin, settings)
│   └── domains/           # Feature domains
│       ├── auth/
│       │   ├── components/
│       │   ├── composables/
│       │   └── pages/
│       ├── courses/
│       ├── payment/
│       └── verification/
├── public/                # Static assets
└── nuxt.config.ts         # Nuxt configuration
```

### Best Practices

1. **Use Composition API** - `<script setup>` syntax preferred
2. **Type everything** - Use TypeScript for all new code
3. **Extract composables** - Reusable logic goes in composables
4. **Use Tailwind** - Prefer Tailwind classes over custom CSS
5. **Lazy load** - Use dynamic imports for heavy components

```typescript
// Good: Composable for API calls
export function useCourses() {
  const courses = ref<Course[]>([])
  const loading = ref(false)
  
  async function fetchCourses() {
    loading.value = true
    try {
      const { data } = await $fetch('/api/courses')
      courses.value = data
    } finally {
      loading.value = false
    }
  }
  
  return { courses, loading, fetchCourses }
}
```

---

## Blockchain (Node.js/TypeScript)

### Code Style

We use **ESLint** with TypeScript rules and **Prettier** for formatting.

```typescript
import { ethers } from 'ethers';
import type { CertificateData, IssuanceResult } from '../types';

export class CertificateService {
  private contract: ethers.Contract;
  
  constructor(
    private readonly provider: ethers.Provider,
    private readonly signer: ethers.Signer
  ) {
    this.contract = new ethers.Contract(
      process.env.CONTRACT_ADDRESS!,
      CertificateABI,
      this.signer
    );
  }

  /**
   * Issue a certificate to the blockchain
   */
  async issueCertificate(data: CertificateData): Promise<IssuanceResult> {
    const hash = this.computeHash(data);
    const tx = await this.contract.issue(hash, data.recipientAddress);
    const receipt = await tx.wait();
    
    return {
      transactionHash: receipt.hash,
      blockNumber: receipt.blockNumber,
      gasUsed: receipt.gasUsed.toString()
    };
  }

  private computeHash(data: CertificateData): string {
    return ethers.keccak256(
      ethers.toUtf8Bytes(JSON.stringify(data))
    );
  }
}
```

### Best Practices

1. **Handle errors gracefully** - Blockchain operations can fail
2. **Use environment variables** - Never hardcode keys or addresses
3. **Implement retries** - Network issues are common
4. **Log transactions** - Keep audit trail of all blockchain operations

---

## Git Workflow

### Branch Naming

```
feature/[ticket-id]-short-description
bugfix/[ticket-id]-short-description
hotfix/[ticket-id]-short-description

Examples:
feature/123-add-certificate-export
bugfix/456-fix-login-redirect
hotfix/789-payment-webhook-crash
```

### Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

**Types:**
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code style (formatting, semicolons)
- `refactor` - Code refactoring
- `test` - Adding or updating tests
- `chore` - Maintenance tasks

**Examples:**
```
feat(courses): add HLS video streaming support

fix(auth): resolve OAuth callback redirect issue

docs(readme): update installation instructions

test(certificates): add blockchain verification tests
```

### Branch Protection

- `main` - Protected, requires PR review
- `dev` - Development branch, requires PR from feature branches

---

## Testing Guidelines

### Backend Testing

```bash
# Run all tests
cd backend
php artisan test

# Run specific test file
php artisan test tests/Feature/CourseUploadTest.php

# Run with coverage
php artisan test --coverage
```

**Test Structure:**
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_in_free_course(): void
    {
        // Arrange
        $student = User::factory()->create(['role' => 'STUDENT']);
        $course = Course::factory()->create(['price' => 0]);

        // Act
        $response = $this->actingAs($student)
            ->postJson("/api/courses/{$course->id}/enroll");

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }
}
```

### Frontend Testing

```bash
# Run unit tests
cd frontend
pnpm test

# Run with coverage
pnpm test:coverage
```

### Blockchain Testing

```bash
# Run Hardhat tests
cd blockchain
pnpm test

# Run Solidity tests only
npx hardhat test solidity

# Run Mocha tests only
npx hardhat test mocha
```

---

## Pull Request Process

### Before Creating a PR

1. **Pull latest changes** from `dev` branch
2. **Run all tests** locally
3. **Run linting** and fix issues
4. **Update documentation** if needed

### PR Template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows project conventions
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No console.log or debug statements
```

### Review Process

1. Create PR from feature branch to `dev`
2. CI pipeline must pass (tests, lint, build)
3. At least 1 approval required
4. Squash and merge when approved
5. Delete feature branch after merge

### CI Pipeline

The CI pipeline runs on every PR:

1. **Backend Tests** - PHPUnit tests with PostgreSQL
2. **Build Images** - Docker image builds (main branch only)
3. **Push to Registry** - GHCR push (main branch only)

---

## Code Formatting

### Backend

```bash
# Install PHP CS Fixer (if not installed)
composer require --dev friendsofphp/php-cs-fixer

# Format code
./vendor/bin/php-cs-fixer fix
```

### Frontend

```bash
# Format with Prettier
pnpm format

# Check formatting
pnpm format:check

# Lint with ESLint
pnpm lint
```

### All Projects

```bash
# From root directory, format everything
pnpm format
```

---

## Questions?

If you have questions about contributing, please:

1. Check existing documentation
2. Search closed issues/PRs
3. Ask in team communication channels
4. Open a GitHub issue for clarification
