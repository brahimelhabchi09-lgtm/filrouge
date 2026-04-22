<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Infrastructure\Persistence\Eloquent\Model\User;
use App\Infrastructure\Persistence\Eloquent\Model\Report;
use App\Infrastructure\Persistence\Eloquent\Model\GeneratedReport;
use App\Infrastructure\Persistence\Eloquent\Model\RequestMeeting;
use App\Infrastructure\Persistence\Eloquent\Model\Category;
use App\Infrastructure\Persistence\Eloquent\Model\Meeting;

class RoleApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;
    private User $bde;
    private User $teacher;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
            'role' => 'ADMIN',
        ]);

        $this->student = User::create([
            'first_name' => 'Student',
            'last_name' => 'User',
            'email' => 'student@test.com',
            'password' => bcrypt('password123'),
            'role' => 'STUDENT',
        ]);

        $this->bde = User::create([
            'first_name' => 'BDE',
            'last_name' => 'User',
            'email' => 'bde@test.com',
            'password' => bcrypt('password123'),
            'role' => 'BDE',
        ]);

        $this->teacher = User::create([
            'first_name' => 'Teacher',
            'last_name' => 'User',
            'email' => 'teacher@test.com',
            'password' => bcrypt('password123'),
            'role' => 'TEACHER',
        ]);

        $this->category = Category::create([
            'name' => 'Plumbing Issue',
            'description' => 'Plumbing problems in the building',
        ]);
    }

    public function test_admin_can_add_user(): void
    {
        $response = $this->postJson('/api/admin/create-user', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'student',
        ], [
            'X-User-Id' => $this->admin->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'first_name', 'last_name', 'email', 'role'],
            ])
            ->assertJson([
                'message' => 'User created successfully!',
                'user' => [
                    'first_name' => 'New',
                    'last_name' => 'User',
                    'email' => 'newuser@test.com',
                    'role' => 'STUDENT',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'role' => 'STUDENT',
        ]);
    }

    public function test_student_can_add_report(): void
    {
        $response = $this->postJson('/api/student/create-report', [
            'title' => 'Leaking faucet in room 101',
            'description' => 'The faucet in room 101 is leaking and needs repair',
            'category_id' => $this->category->id,
        ], [
            'X-User-Id' => $this->student->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'report' => ['id', 'title', 'description', 'category'],
            ])
            ->assertJson([
                'message' => 'Report submitted successfully!',
                'report' => [
                    'title' => 'Leaking faucet in room 101',
                ],
            ]);

        $this->assertDatabaseHas('reports', [
            'title' => 'Leaking faucet in room 101',
            'student_id' => $this->student->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('generated_reports', [
            'status' => 'pending',
        ]);
    }

    public function test_bde_can_add_request_meeting(): void
    {
        $generatedReport = GeneratedReport::create([
            'message' => 'Test meeting request',
            'priority' => 'P2',
            'status' => 'pending',
            'reports_count' => 1,
        ]);

        $futureDate = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->postJson('/api/request-meetings', [
            'generated_report_id' => $generatedReport->id,
            'meeting_date' => $futureDate,
            'notes' => 'Please schedule a meeting to discuss the issue',
        ], [
            'X-User-Id' => $this->bde->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'request_meeting' => ['id', 'bde_id', 'generated_report_id', 'status'],
            ])
            ->assertJson([
                'message' => 'Meeting request submitted successfully.',
                'request_meeting' => [
                    'bde_id' => $this->bde->id,
                    'generated_report_id' => $generatedReport->id,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('request_meetings', [
            'bde_id' => $this->bde->id,
            'generated_report_id' => $generatedReport->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_accept_meeting(): void
    {
        $generatedReport = GeneratedReport::create([
            'message' => 'Test meeting request for approval',
            'priority' => 'P1',
            'status' => 'pending',
            'reports_count' => 1,
        ]);

        $requestMeeting = RequestMeeting::create([
            'bde_id' => $this->bde->id,
            'generated_report_id' => $generatedReport->id,
            'meeting_date' => now()->addDays(3),
            'notes' => 'Need to discuss the facility issue',
            'status' => 'pending',
        ]);

        $futureDate = now()->addDays(4)->format('Y-m-d H:i:s');

        $response = $this->postJson('/api/meetings', [
            'request_meeting_id' => $requestMeeting->id,
            'title' => 'Facility Discussion Meeting',
            'description' => 'Meeting to discuss the facility issue',
            'date' => $futureDate,
            'link' => 'https://meet.google.com/test-meeting',
        ], [
            'X-User-Id' => $this->admin->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'meeting' => ['id', 'title', 'link'],
            ])
            ->assertJson([
                'message' => 'Meeting scheduled successfully.',
                'meeting' => [
                    'title' => 'Facility Discussion Meeting',
                ],
            ]);

        $this->assertDatabaseHas('meetings', [
            'title' => 'Facility Discussion Meeting',
            'request_meeting_id' => $requestMeeting->id,
            'admin_id' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('request_meetings', [
            'id' => $requestMeeting->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('generated_reports', [
            'id' => $generatedReport->id,
            'status' => 'resolved',
        ]);
    }

    public function test_student_can_view_their_dashboard(): void
    {
        Report::create([
            'title' => 'Test Report',
            'description' => 'Test description',
            'student_id' => $this->student->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/student/dashboard', [
            'X-User-Id' => $this->student->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'stats' => ['total', 'pending', 'resolved', 'rejected'],
            ])
            ->assertJson([
                'message' => 'Student Dashboard',
                'stats' => ['total' => 1, 'pending' => 1],
            ]);
    }

    public function test_teacher_can_view_dashboard(): void
    {
        $response = $this->getJson('/api/teacher/dashboard', [
            'X-User-Id' => $this->teacher->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_bde_can_view_dashboard(): void
    {
        $response = $this->getJson('/api/bde/dashboard', [
            'X-User-Id' => $this->bde->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $response = $this->getJson('/api/admin/dashboard', [
            'X-User-Id' => $this->admin->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_teacher_can_resolve_report(): void
    {
        $pedagogyCategory = Category::create([
            'name' => 'Pedagogy Issue',
            'description' => 'Teaching related issues',
        ]);

        $generatedReport = GeneratedReport::create([
            'message' => 'Test report for teacher',
            'priority' => 'P2',
            'status' => 'pending',
            'reports_count' => 1,
        ]);

        $report = Report::create([
            'title' => 'Test Report for Teacher',
            'description' => 'Test description for pedagogy category',
            'student_id' => $this->student->id,
            'category_id' => $pedagogyCategory->id,
            'generated_report_id' => $generatedReport->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/teacher/reports/' . $report->id . '/resolve');

        $response->assertStatus(200);

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);
    }
}