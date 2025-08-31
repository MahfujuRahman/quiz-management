<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Management\CourseManagement\Course\Models\Model as Course;
use App\Modules\Management\CourseManagement\CourseCategory\Models\Model as CourseCategory;

class CourseDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample course categories first
        $categories = [
            [
                'title' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Learn modern web development technologies',
                'status' => 'active'
            ],
            [
                'title' => 'Mobile App Development',
                'slug' => 'mobile-app-development', 
                'description' => 'Build mobile applications for iOS and Android',
                'status' => 'active'
            ],
            [
                'title' => 'Data Science',
                'slug' => 'data-science',
                'description' => 'Master data analysis and machine learning',
                'status' => 'active'
            ]
        ];

        foreach ($categories as $categoryData) {
            CourseCategory::firstOrCreate(
                ['slug' => $categoryData['slug']], 
                $categoryData
            );
        }

        // Create sample courses
        $courses = [
            [
                'title' => 'Complete Web Development Bootcamp',
                'description' => 'Learn HTML, CSS, JavaScript, React, Node.js and more',
                'short_description' => 'Become a full-stack web developer in 6 months',
                'price' => 15000,
                'discount_price' => 12000,
                'duration' => 6,
                'duration_type' => 'months',
                'level' => 'beginner',
                'language' => 'bengali',
                'status' => 'active',
                'is_published' => true,
                'published_at' => now(),
                'type' => 'regular'
            ],
            [
                'title' => 'React Native Mobile App Development',
                'description' => 'Build cross-platform mobile apps with React Native',
                'short_description' => 'Create iOS and Android apps with one codebase',
                'price' => 18000,
                'discount_price' => 15000,
                'duration' => 4,
                'duration_type' => 'months',
                'level' => 'intermediate',
                'language' => 'english',
                'status' => 'active',
                'is_published' => true,
                'published_at' => now(),
                'type' => 'premium'
            ],
            [
                'title' => 'Data Science with Python',
                'description' => 'Master data analysis, visualization and machine learning',
                'short_description' => 'Become a data scientist with Python',
                'price' => 20000,
                'discount_price' => 16000,
                'duration' => 8,
                'duration_type' => 'months',
                'level' => 'advanced',
                'language' => 'bengali',
                'status' => 'active',
                'is_published' => true,
                'published_at' => now(),
                'type' => 'professional'
            ],
            [
                'title' => 'Digital Marketing Mastery',
                'description' => 'Learn SEO, Social Media Marketing, Google Ads and more',
                'short_description' => 'Master digital marketing strategies',
                'price' => 12000,
                'discount_price' => 10000,
                'duration' => 3,
                'duration_type' => 'months',
                'level' => 'beginner',
                'language' => 'bengali',
                'status' => 'active',
                'is_published' => true,
                'published_at' => now(),
                'type' => 'regular'
            ],
            [
                'title' => 'UI/UX Design Fundamentals',
                'description' => 'Design beautiful and user-friendly interfaces',
                'short_description' => 'Learn design principles and tools',
                'price' => 14000,
                'discount_price' => 11000,
                'duration' => 5,
                'duration_type' => 'months',
                'level' => 'beginner',
                'language' => 'bengali',
                'status' => 'active',
                'is_published' => true,
                'published_at' => now(),
                'type' => 'regular'
            ]
        ];

        $webDevCategory = CourseCategory::where('slug', 'web-development')->first();
        $mobileCategory = CourseCategory::where('slug', 'mobile-app-development')->first();
        $dataCategory = CourseCategory::where('slug', 'data-science')->first();

        foreach ($courses as $index => $courseData) {
            // Assign categories
            if ($index == 0) {
                $courseData['course_category_id'] = $webDevCategory->id;
            } elseif ($index == 1) {
                $courseData['course_category_id'] = $mobileCategory->id;
            } elseif ($index == 2) {
                $courseData['course_category_id'] = $dataCategory->id;
            } else {
                $courseData['course_category_id'] = $webDevCategory->id;
            }

            Course::create($courseData);
        }
    }
}
