<section id="features" class="section-padding bg-light">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Powerful Features for Better Learning</h2>
        <div class="row text-center">
            <?php
            $features = [
                ["icon" => "fas fa-book-open", "title" => "Interactive Learning Tools", "description" => "Engage with dynamic content and quizzes that adapt to your learning pace."],
                ["icon" => "fas fa-comments", "title" => "Collaborative Study Groups", "description" => "Connect with peers, share insights, and learn together in a supportive environment."],
                ["icon" => "fas fa-chart-line", "title" => "Progress Tracking", "description" => "Monitor your achievements and identify areas for improvement with detailed analytics."],
                ["icon" => "fas fa-chalkboard-teacher", "title" => "Expert Instructors", "description" => "Learn from industry professionals and experienced educators with proven track records."],
                ["icon" => "fas fa-mobile-alt", "title" => "Accessible Anywhere", "description" => "Study on the go with our mobile-friendly platform, available on all your devices."],
                ["icon" => "fas fa-award", "title" => "Certification", "description" => "Earn certificates upon course completion to showcase your skills and enhance your resume."]
            ];

            foreach ($features as $feature):
            ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="p-4 bg-white shadow rounded h-100">
                    <i class="<?php echo htmlspecialchars($feature['icon']); ?> feature-icon"></i>
                    <h5 class="mt-3 fw-semibold"><?php echo htmlspecialchars($feature['title']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($feature['description']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

