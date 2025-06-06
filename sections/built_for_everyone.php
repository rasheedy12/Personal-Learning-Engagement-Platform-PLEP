<section class="section-padding">
    <div class="container">
        <h2 class="text-center mb-5 fw-bold">Built For Everyone In Education</h2>
        <div class="row">
            <?php
            $user_types = [
                ["icon_bg" => "bg-purple", "icon_fa" => "fas fa-user-graduate", "title" => "For Students", "description" => "Access a wide range of courses, interactive materials, and tools to enhance your learning experience and achieve academic success."],
                ["icon_bg" => "bg-green", "icon_fa" => "fas fa-chalkboard-teacher", "title" => "For Educators", "description" => "Create engaging courses, manage your students, and leverage powerful analytics to deliver impactful education and track performance."],
                ["icon_bg" => "bg-orange", "icon_fa" => "fas fa-school", "title" => "For Institutions", "description" => "Implement a comprehensive learning solution for your organization, with custom branding and robust administrative features."]
            ];

            foreach ($user_types as $type):
            ?>
            <div class="col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex align-items-start p-4">
                        <div class="card-icon-placeholder <?php echo htmlspecialchars($type['icon_bg']); ?> me-3">
                            <i class="<?php echo htmlspecialchars($type['icon_fa']); ?> fs-5"></i>
                        </div>
                        <div>
                            <h5 class="card-title fw-semibold"><?php echo htmlspecialchars($type['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($type['description']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
