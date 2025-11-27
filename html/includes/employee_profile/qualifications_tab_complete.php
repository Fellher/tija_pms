<?php
/**
 * Qualifications Tab - Education, Experience, Skills, Licenses, Certifications
 * Uses URL-based sub-tab navigation for persistent state
 */

// Get current sub-tab from URL or default to 'education'
$currentSubTab = (isset($_GET['subtab']) && !empty($_GET['subtab'])) ? Utility::clean_string($_GET['subtab']) : 'education';

// Define sub-tabs array using linkArray structure
$qualificationSubTabsArray = [
    (object)[
        "title" => "Education",
        "icon" => "ri-book-line",
        "slug" => "education",
        "active" => $currentSubTab == "education",
        "description" => "Educational qualifications and degrees"
    ],
    (object)[
        "title" => "Work Experience",
        "icon" => "ri-briefcase-line",
        "slug" => "experience",
        "active" => $currentSubTab == "experience",
        "description" => "Previous employment history"
    ],
    (object)[
        "title" => "Skills",
        "icon" => "ri-tools-line",
        "slug" => "skills",
        "active" => $currentSubTab == "skills",
        "description" => "Professional skills and competencies"
    ],
    (object)[
        "title" => "Certifications",
        "icon" => "ri-medal-line",
        "slug" => "certifications",
        "active" => $currentSubTab == "certifications",
        "description" => "Professional certifications"
    ],
    (object)[
        "title" => "Licenses",
        "icon" => "ri-shield-check-line",
        "slug" => "licenses",
        "active" => $currentSubTab == "licenses",
        "description" => "Professional licenses"
    ]
];

// Validate current sub-tab
$validSubTab = false;
foreach ($qualificationSubTabsArray as $subTab) {
    if ($subTab->slug == $currentSubTab) {
        $validSubTab = true;
        break;
    }
}

// If invalid, default to first sub-tab
if (!$validSubTab) {
    $currentSubTab = 'education';
}

?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-award-line me-2"></i>Qualifications & Experience</h5>
</div>

<!-- Sub-Tabs Navigation - URL-based like main profile tabs -->
<ul class="nav nav-pills qualification-sub-tabs mb-3" role="tablist">
    <?php foreach ($qualificationSubTabsArray as $subTab):
        $subTabUrl = "?s={$s}&p={$p}&uid={$employeeID}&tab=qualifications&subtab={$subTab->slug}";
    ?>
    <li class="nav-item" role="presentation">
        <a href="<?= $subTabUrl ?>"
           class="nav-link <?= $subTab->active ? 'active' : '' ?>"
           id="<?= $subTab->slug ?>-subtab"
           title="<?= $subTab->description ?>">
            <i class="<?= $subTab->icon ?> me-1"></i><?= $subTab->title ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<!-- Sub-Tab Content - Rendered based on current sub-tab -->
<div class="qualification-content">
    <?php if ($currentSubTab == 'education'): ?>
    <!-- Education Section -->
    <div class="qualification-section" data-subtab="education">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Educational Background</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="alert('Add education modal will be implemented')">
                <i class="ri-add-line me-1"></i> Add Education
            </button>
            <?php endif; ?>
        </div>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            Educational qualifications will be displayed here. Database integration in progress.
        </div>
    </div>

    <?php elseif ($currentSubTab == 'experience'): ?>
    <!-- Work Experience Section -->
    <div class="qualification-section" data-subtab="experience">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Previous Employment</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="alert('Add experience modal will be implemented')">
                <i class="ri-add-line me-1"></i> Add Experience
            </button>
            <?php endif; ?>
        </div>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            Work experience from previous employers will be displayed here.
        </div>
    </div>

    <?php elseif ($currentSubTab == 'skills'): ?>
    <!-- Skills Section -->
    <div class="qualification-section" data-subtab="skills">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Skills</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="alert('Add skill modal will be implemented')">
                <i class="ri-add-line me-1"></i> Add Skill
            </button>
            <?php endif; ?>
        </div>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            Skills and competencies will be displayed here.
        </div>
    </div>

    <?php elseif ($currentSubTab == 'certifications'): ?>
    <!-- Certifications Section -->
    <div class="qualification-section" data-subtab="certifications">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Certifications</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="alert('Add certification modal will be implemented')">
                <i class="ri-add-line me-1"></i> Add Certification
            </button>
            <?php endif; ?>
        </div>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            Professional certifications will be displayed here.
        </div>
    </div>

    <?php elseif ($currentSubTab == 'licenses'): ?>
    <!-- Licenses Section -->
    <div class="qualification-section" data-subtab="licenses">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Professional Licenses</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="alert('Add license modal will be implemented')">
                <i class="ri-add-line me-1"></i> Add License
            </button>
            <?php endif; ?>
        </div>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            Professional licenses (driving, practice licenses, etc.) will be displayed here.
        </div>
    </div>

    <?php endif; ?>
</div>

<style>
/* Sub-tabs styling - maintain look and feel */
.qualification-sub-tabs {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 8px;
}

.qualification-sub-tabs .nav-link {
    color: #6c757d;
    font-weight: 500;
    border-radius: 6px;
    padding: 8px 16px;
    margin: 0 4px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.qualification-sub-tabs .nav-link:hover {
    background-color: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.qualification-sub-tabs .nav-link.active {
    background-color: #007bff;
    color: #fff;
    font-weight: 600;
}

.qualification-sub-tabs .nav-link i {
    font-size: 1rem;
}

.qualification-section {
    animation: fadeInSubTab 0.3s ease-in;
}

@keyframes fadeInSubTab {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .qualification-sub-tabs .nav-link {
        padding: 6px 12px;
        font-size: 0.875rem;
        margin: 2px;
    }

    .qualification-sub-tabs .nav-link i {
        display: none;
    }
}
</style>

<script>
// Sub-tab state management
document.addEventListener('DOMContentLoaded', function() {
    console.log('Qualifications Sub-Tab:', '<?= $currentSubTab ?>');

    // Optional: Add console log when clicking sub-tabs
    const subTabLinks = document.querySelectorAll('.qualification-sub-tabs .nav-link');
    subTabLinks.forEach(link => {
        link.addEventListener('click', function() {
            console.log('Navigating to sub-tab:', this.getAttribute('title'));
        });
    });
});
</script>

