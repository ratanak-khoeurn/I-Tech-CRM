<?php

namespace Database\Seeders;

use App\Models\AlumniEvent;
use App\Models\Campus;
use App\Models\CommunicationLog;
use App\Models\Course;
use App\Models\EducationHistory;
use App\Models\EmploymentHistory;
use App\Models\EventRegistration;
use App\Models\FollowUpTask;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\Student;
use App\Models\StudentCourse;
use App\Models\StudentNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Student::count() > 0) {
            return;
        }

        $staff = User::whereIn('email', ['staff@crm.test', 'staff2@crm.test', 'admin@crm.test'])->pluck('id', 'email');
        $adminId = $staff['admin@crm.test'];
        $campuses = Campus::pluck('id', 'code');
        $courses = Course::pluck('id', 'code');

        $provinces = ['Phnom Penh', 'Siem Reap', 'Battambang', 'Kampot', 'Kandal', 'Takeo', 'Sihanoukville', 'Kampong Cham'];
        $companies = [
            ['Smart Axiata', 'Telecom'], ['Wing Bank', 'Banking'], ['Grab Cambodia', 'Technology'],
            ['Cellcard', 'Telecom'], ['Prince Bank', 'Banking'], ['RMA Group', 'Conglomerate'],
            ['Pathmazing', 'IT Services'], ['Sabay Digital', 'Media'], ['Chip Mong Group', 'Conglomerate'],
            ['Cambodia Post', 'Logistics'], ['Mekong Enterprise', 'Investment'], ['Brown Coffee', 'F&B'],
        ];
        $jobTitles = ['Junior Developer', 'Graphic Designer', 'Marketing Officer', 'Accountant',
            'Sales Executive', 'HR Assistant', 'IT Support', 'Content Creator', 'Freelancer',
            'Intern', 'Project Coordinator', 'Customer Service Officer'];
        $employmentStatuses = ['employed', 'self_employed', 'unemployed', 'freelancing', 'internship', 'student'];
        $educationStatuses = ['studying', 'graduated', 'paused', 'not_studying'];
        $khmerFirst = ['Sokha', 'Dara', 'Bopha', 'Vichea', 'Sreyneang', 'Piseth', 'Channary', 'Rathanak',
            'Sreyleap', 'Visal', 'Theary', 'Narith', 'Kunthea', 'Samnang', 'Moly', 'Davith',
            'Chantrea', 'Sopheap', 'Vannak', 'Malis', 'Raksa', 'Tola', 'Srey touch', 'Veasna'];
        $khmerLast = ['Sok', 'Chan', 'Kim', 'Heng', 'Ly', 'Meas', 'San', 'Chhay', 'Touch', 'Nov',
            'Mok', 'Kong', 'Noun', 'Ros', 'Keo', 'Sim', 'Yun', 'Chea', 'Um', 'Prum', 'Ouk', 'Long', 'Thach', 'Pen'];

        mt_srand(20260928);
        $studentIds = [];

        for ($i = 0; $i < 26; $i++) {
            $first = $khmerFirst[$i % count($khmerFirst)];
            $last = $khmerLast[$i % count($khmerLast)];
            $english = "$first $last";
            $gradYear = 2019 + ($i % 6);
            $courseCode = ['WEB', 'GD', 'ENG', 'CHI', 'DM', 'ACC'][$i % 6];
            $campusCode = ['PP', 'CBA', 'RK'][$i % 3];
            $empStatus = $i % 9 === 0 ? 'unemployed' : ($i % 9 === 1 ? 'student' : $employmentStatuses[$i % 6]);
            $eduStatus = in_array($empStatus, ['student', 'internship']) ? 'studying' : $educationStatuses[($i + 1) % 4];
            $company = $companies[$i % count($companies)];
            $status = $i % 11 === 0 ? 'inactive' : ($i % 17 === 0 ? 'do_not_contact' : 'active_alumni');

            $student = Student::create([
                'student_code' => sprintf('STU-%s-%04d', $gradYear, $i + 1),
                'name_english' => $english,
                'name_khmer' => $first . ' ' . $last,
                'name_chinese' => $i % 4 === 0 ? '李明' . $i : null,
                'gender' => $i % 2 === 0 ? 'female' : 'male',
                'date_of_birth' => Carbon::create(1996 + ($i % 8), 1 + ($i % 12), 1 + ($i % 28)),
                'nationality' => 'Cambodian',
                'national_id' => sprintf('1234%05d', $i + 1),
                'marital_status' => $i % 5 === 0 ? 'married' : 'single',
                'phone' => '01' . ($i % 3 + 1) . sprintf('%07d', 100000 + $i * 37),
                'telegram' => '@' . strtolower(str_replace(' ', '', $english)) . $i,
                'email' => strtolower(str_replace(' ', '.', $english)) . $i . '@example.com',
                'facebook_url' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $english)) . $i,
                'linkedin_url' => $i % 3 === 0 ? 'https://linkedin.com/in/' . strtolower(str_replace(' ', '-', $english)) : null,
                'address' => 'St. ' . ($i + 100) . ', House #' . ($i + 5) . 'A',
                'province' => $provinces[$i % count($provinces)],
                'country' => 'Cambodia',
                'emergency_contact_name' => $khmerLast[($i + 3) % count($khmerLast)] . ' ' . $first,
                'emergency_contact_relationship' => ['Parent', 'Sibling', 'Spouse'][$i % 3],
                'emergency_contact_phone' => '09' . sprintf('%08d', 200000 + $i * 53),
                'preferred_channel' => ['phone_call', 'telegram', 'email', 'facebook'][$i % 4],
                'preferred_language' => ['Khmer', 'English', 'Chinese'][$i % 3],
                'registered_date' => Carbon::now()->subMonths($i + 1),
                'status' => $status,
                'education_status' => $eduStatus,
                'employment_status' => $empStatus,
                'is_currently_studying' => $eduStatus === 'studying',
                'is_currently_employed' => in_array($empStatus, ['employed', 'self_employed', 'freelancing']),
                'is_seeking_employment' => in_array($empStatus, ['unemployed', 'internship']),
                'professional_skills' => implode(', ', array_slice(['Communication', 'Teamwork', 'Leadership', 'Problem Solving', 'Time Management'], 0, 2 + ($i % 3))),
                'technical_skills' => $courseCode === 'WEB' ? 'PHP, Laravel, JavaScript, MySQL' : ($courseCode === 'GD' ? 'Photoshop, Illustrator, Figma' : 'Microsoft Office'),
                'certifications' => $i % 3 === 0 ? 'Course Completion Certificate' : null,
                'portfolio_url' => $courseCode === 'GD' ? 'https://behance.net/' . strtolower(str_replace(' ', '', $english)) : null,
                'career_goals' => 'To grow into a senior professional in the ' . $company[1] . ' industry within 5 years.',
                'interested_jobs' => $i % 2 === 0,
                'interested_internships' => $i % 3 === 0,
                'interested_mentoring' => $i % 4 === 0,
                'interested_trainer' => $i % 6 === 0,
                'interested_volunteering' => $i % 5 === 0,
                'alumni_membership_number' => sprintf('AL-%s-%03d', $gradYear, $i + 1),
                'alumni_group' => 'Class of ' . $gradYear,
                'interested_activities' => $i % 2 === 1,
                'interested_newsletter' => $i % 3 !== 2,
                'interested_job_announcements' => $i % 2 === 0,
                'interested_training' => $i % 4 !== 3,
                'last_contacted_date' => Carbon::now()->subDays(($i * 7) % 90),
                'next_followup_date' => Carbon::now()->addDays((($i * 5) % 30) - 10),
                'engagement_status' => ['engaged', 'semi_engaged', 'not_engaged'][$i % 3],
                'alumni_feedback' => $i % 4 === 0 ? 'Grateful for the training; it helped launch my career.' : null,
                'assigned_staff_id' => $i % 2 === 0 ? $staff['staff@crm.test'] : $staff['staff2@crm.test'],
                'created_by' => $adminId,
                'updated_by' => $adminId,
                'record_status' => $i % 13 === 0 ? 'archived' : 'active',
                'internal_notes' => $i % 7 === 0 ? 'Prefers contact after 5pm.' : null,
            ]);
            $studentIds[] = $student->id;

            StudentCourse::create([
                'student_id' => $student->id,
                'campus_id' => $campuses[$campusCode],
                'course_id' => $courses[$courseCode],
                'center_name' => 'I-Tech Media Center',
                'registration_number' => sprintf('REG-%s-%04d', $gradYear, $i + 1),
                'major' => $courseCode === 'WEB' ? 'Full-stack' : ($courseCode === 'ACC' ? 'Financial Accounting' : 'General'),
                'class_group' => chr(65 + ($i % 4)) . ($gradYear % 100),
                'instructor_name' => 'Mr. ' . $khmerLast[($i + 7) % count($khmerLast)] . ' ' . $khmerFirst[($i + 2) % count($khmerFirst)],
                'enrollment_date' => Carbon::create($gradYear - 1, 3, 15),
                'course_start_date' => Carbon::create($gradYear - 1, 4, 1),
                'course_completion_date' => Carbon::create($gradYear, 3, 30),
                'graduation_date' => Carbon::create($gradYear, 4, 15),
                'graduation_year' => $gradYear,
                'attendance_percentage' => 75 + ($i % 25),
                'grade' => ['A', 'B+', 'B', 'C+'][$i % 4],
                'certificate_received' => $i % 8 !== 0,
                'certificate_number' => $i % 8 !== 0 ? sprintf('CERT-%s-%04d', $gradYear, $i + 1) : null,
                'scholarship_status' => $i % 5 === 0 ? 'Partial scholarship (30%)' : 'None',
                'internship_participation' => $i % 3 === 0,
                'internship_company' => $i % 3 === 0 ? $company[0] : null,
                'graduation_project' => $courseCode === 'WEB' ? 'E-commerce Website for Local SME' : null,
                'alumni_membership_status' => 'member',
                'alumni_membership_date' => Carbon::create($gradYear, 5, 1),
            ]);

            if ($eduStatus === 'studying') {
                EducationHistory::create([
                    'student_id' => $student->id,
                    'is_current' => true,
                    'institution' => ['Royal University of Phnom Penh', 'Institute of Technology of Cambodia', 'Build Bright University'][$i % 3],
                    'degree_program' => ['Bachelor of Science', 'Bachelor of Management', 'Associate Degree'][$i % 3],
                    'major' => ['Computer Science', 'Business Administration', 'Finance'][$i % 3],
                    'year_level' => 'Year ' . (1 + $i % 4),
                    'start_date' => Carbon::now()->subYears(1 + $i % 3),
                    'expected_graduation_date' => Carbon::now()->addYears(2),
                    'scholarship_info' => $i % 4 === 0 ? 'Merit scholarship' : null,
                    'further_education_plans' => 'Plans to pursue a master\'s degree abroad.',
                    'status' => 'studying',
                ]);
            }

            if ($empStatus !== 'unemployed' && $empStatus !== 'student') {
                EmploymentHistory::create([
                    'student_id' => $student->id,
                    'is_current' => true,
                    'company' => $company[0],
                    'job_title' => $jobTitles[$i % count($jobTitles)],
                    'industry' => $company[1],
                    'department' => ['IT', 'Marketing', 'Finance', 'Operations'][$i % 4],
                    'work_phone' => '023' . sprintf('%06d', 300000 + $i * 91),
                    'work_email' => strtolower(str_replace(' ', '.', $english)) . '@' . strtolower(str_replace([' ', ' Cambodia'], '', $company[0])) . '.com',
                    'start_date' => Carbon::now()->subMonths(6 + $i),
                    'salary_range' => ['$' . (300 + $i * 25) . ' - $' . (500 + $i * 25), '$400 - $700'][$i % 2],
                    'employment_type' => ['full_time', 'part_time', 'contract', 'freelance'][$i % 4],
                    'employment_status' => $empStatus,
                ]);
            }
            if ($i % 2 === 0) {
                EmploymentHistory::create([
                    'student_id' => $student->id,
                    'is_current' => false,
                    'company' => $companies[($i + 4) % count($companies)][0],
                    'job_title' => $jobTitles[($i + 3) % count($jobTitles)],
                    'industry' => $companies[($i + 4) % count($companies)][1],
                    'start_date' => Carbon::now()->subYears(3),
                    'end_date' => Carbon::now()->subMonths(8),
                    'employment_type' => 'full_time',
                    'employment_status' => 'employed',
                ]);
            }

            // Communications
            foreach (range(1, 1 + ($i % 3)) as $c) {
                CommunicationLog::create([
                    'student_id' => $student->id,
                    'user_id' => $i % 2 === 0 ? $staff['staff@crm.test'] : $staff['staff2@crm.test'],
                    'contact_datetime' => Carbon::now()->subDays($c * 20 + $i)->setTime(9 + ($c % 8), ($i * 7) % 60),
                    'channel' => array_keys(CommunicationLog::CHANNELS)[$c % 8],
                    'direction' => $c % 2 === 0 ? 'incoming' : 'outgoing',
                    'subject' => ['Check-in call', 'Career update', 'Event invitation', 'Newsletter follow-up'][$c % 4],
                    'message_summary' => 'Discussed current progress and upcoming alumni activities.',
                    'outcome' => ['Positive', 'Needs follow-up', 'No answer', 'Information updated'][$c % 4],
                    'followup_required' => $c % 3 === 0,
                    'next_followup_date' => $c % 3 === 0 ? Carbon::now()->addDays(14) : null,
                ]);
            }

            // Follow-up tasks
            FollowUpTask::create([
                'student_id' => $student->id,
                'assigned_to' => $i % 2 === 0 ? $staff['staff@crm.test'] : $staff['staff2@crm.test'],
                'created_by' => $adminId,
                'title' => ['Call for career update', 'Invite to reunion', 'Share job opening', 'Collect feedback'][$i % 4],
                'description' => 'Follow up with ' . $english . ' regarding recent updates.',
                'priority' => ['low', 'medium', 'high'][$i % 3],
                'due_date' => Carbon::now()->addDays((($i * 3) % 21) - 7),
                'reminder_enabled' => $i % 2 === 0,
                'reminder_at' => Carbon::now()->addDays((($i * 3) % 21) - 8)->setTime(9, 0),
                'status' => $i % 5 === 0 ? 'completed' : ($i % 7 === 0 ? 'in_progress' : 'pending'),
                'result' => $i % 5 === 0 ? 'Student responded positively; updated career info.' : null,
                'completed_at' => $i % 5 === 0 ? Carbon::now()->subDays(2) : null,
            ]);

            StudentNote::create([
                'student_id' => $student->id,
                'content' => 'Initial note: ' . $english . ' graduated in ' . $gradYear . ' from the ' . $courseCode . ' program.',
                'is_private' => false,
                'created_by' => $adminId,
            ]);
        }

        // Events
        $events = [
            ['title' => 'Alumni Reunion 2026', 'event_type' => 'reunion', 'start_datetime' => Carbon::now()->addWeeks(3)->setTime(18, 0), 'location' => 'Phnom Penh Main Campus Hall', 'status' => 'published'],
            ['title' => 'Career Talk: Tech Industry Trends', 'event_type' => 'career_talk', 'start_datetime' => Carbon::now()->addDays(10)->setTime(14, 0), 'location' => 'Online (Zoom)', 'status' => 'published'],
            ['title' => 'UI/UX Workshop', 'event_type' => 'workshop', 'start_datetime' => Carbon::now()->addWeeks(6)->setTime(9, 0), 'location' => 'Siem Reap Branch', 'status' => 'published'],
            ['title' => 'Mentorship Program Kickoff', 'event_type' => 'mentorship', 'start_datetime' => Carbon::now()->subWeeks(2)->setTime(10, 0), 'location' => 'Phnom Penh Main Campus', 'status' => 'completed'],
            ['title' => 'Community Cleanup Volunteer Day', 'event_type' => 'volunteer', 'start_datetime' => Carbon::now()->addMonth()->setTime(7, 30), 'location' => 'Riverside Park', 'status' => 'draft'],
        ];
        foreach ($events as $idx => $e) {
            $event = AlumniEvent::create(array_merge($e, [
                'description' => 'An opportunity for alumni to connect, share experiences, and grow together.',
                'end_datetime' => Carbon::parse($e['start_datetime'])->addHours(3),
                'registration_deadline' => Carbon::parse($e['start_datetime'])->subDays(2)->toDateString(),
                'max_participants' => 30 + $idx * 20,
                'organizer' => 'I-Tech Media Center',
                'target_group' => 'All alumni',
                'feedback' => $e['status'] === 'completed' ? 'Well received; alumni requested more frequent sessions.' : null,
                'created_by' => $adminId,
            ]));
            foreach (array_slice($studentIds, $idx * 4, 8) as $sid) {
                EventRegistration::create([
                    'alumni_event_id' => $event->id,
                    'student_id' => $sid,
                    'status' => $e['status'] === 'completed' ? (['attended', 'no_show'][$sid % 2]) : 'registered',
                    'registered_by' => $adminId,
                ]);
            }
        }

        // Opportunities
        $opps = [
            ['title' => 'Junior Web Developer', 'organization' => 'Pathmazing', 'opportunity_type' => 'job', 'target_course' => 'WEB', 'requirements' => "PHP/Laravel basics, team player.\nGraduated within last 3 years.", 'publication_status' => 'published'],
            ['title' => 'Graphic Design Internship', 'organization' => 'Sabay Digital', 'opportunity_type' => 'internship', 'target_course' => 'GD', 'requirements' => 'Portfolio required, Adobe suite skills.', 'publication_status' => 'published'],
            ['title' => 'Australia Study Scholarship', 'organization' => 'Australia Awards', 'opportunity_type' => 'scholarship', 'target_course' => null, 'requirements' => 'Bachelor degree, IELTS 6.5+, work experience preferred.', 'publication_status' => 'published'],
            ['title' => 'Digital Marketing Bootcamp', 'organization' => 'Google Asia', 'opportunity_type' => 'training', 'target_course' => 'DM', 'requirements' => 'Open to all alumni interested in marketing.', 'publication_status' => 'published'],
            ['title' => 'We own the future of digital.', 'organization' => 'I-Tech Media Center', 'opportunity_type' => 'volunteer', 'target_course' => null, 'requirements' => 'Alumni willing to mentor current students.', 'publication_status' => 'draft'],
        ];
        foreach ($opps as $idx => $o) {
            $opp = Opportunity::create(array_merge($o, [
                'description' => 'Great opportunity for qualified alumni to advance their career.',
                'application_deadline' => Carbon::now()->addWeeks(2 + $idx),
                'contact_info' => 'hr@' . strtolower(str_replace(' ', '', $o['organization'])) . '.com',
                'application_url' => 'https://example.com/apply/' . ($idx + 1),
                'target_graduation_year' => $idx % 2 === 0 ? '2022,2023,2024' : null,
                'created_by' => $adminId,
            ]));
            foreach (array_slice($studentIds, $idx * 3, 5) as $sid) {
                OpportunityApplication::create([
                    'opportunity_id' => $opp->id,
                    'student_id' => $sid,
                    'source' => ['matched', 'referred', 'self_applied'][$sid % 3],
                    'status' => ['interested', 'applied', 'interviewing'][$sid % 3],
                    'created_by' => $adminId,
                ]);
            }
        }
    }
}
