<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeacherApplication;
use App\Models\User;
use Illuminate\Support\Str;

class TeacherApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find some student users to create sample applications for
        $students = User::where('role', 'STUDENT')->limit(10)->get();
        $admin = User::where('role', 'ADMIN')->first();

        if ($students->isEmpty()) {
            // No students available - nothing to seed
            return;
        }

        $counter = 0;
        foreach ($students as $student) {
            if ($counter++ >= 11) break;

            $status = 'PENDING';
            $reviewedBy = null;
            $reviewedAt = null;
            $rejectionReason = null;

            // Make 1 approved and 1 rejected sample if admin exists
            if ($counter === 2 && $admin) {
                $status = 'APPROVED';
                $reviewedBy = $admin->id;
                $reviewedAt = now();
            }

            if ($counter === 3 && $admin) {
                $status = 'REJECTED';
                $reviewedBy = $admin->id;
                $reviewedAt = now();
                $rejectionReason = 'Insufficient certificate evidence';
            }

            TeacherApplication::create([
                'id' => Str::uuid()->toString(),
                'user_id' => $student->id,
                'status' => $status,
                // Personal Information
                'full_name' => $student->first_name . ' ' . $student->last_name,
                'email' => $student->email,
                'bio' => 'Experienced educator with passion for teaching.',
                'gender' => rand(0, 1) ? 'MALE' : 'FEMALE',
                'phone' => '+84' . rand(900000000, 999999999),
                'date_of_birth' => now()->subYears(rand(25, 45))->toDateString(),
                'country' => 'Vietnam',
                // Certificate Information
                'certificate_title' => 'Professional Teaching Certificate Level ' . ($counter + 1),
                'issuer' => 'Example Institute',
                'issue_date' => now()->subYears(2)->toDateString(),
                'expiry_date' => now()->addYears(3)->toDateString(),
                'certificate_file_path' => null,
                // Review Information
                'reviewed_by' => $reviewedBy,
                'reviewed_at' => $reviewedAt,
                'rejection_reason' => $rejectionReason,
            ]);
        }
    }
}
