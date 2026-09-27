<?php

namespace App\Services\ResumeParser\Training;

/**
 * Content used to build synthetic resumes.
 *
 * This exists because the database cannot supply it: there are a dozen
 * experience rows in total and the seeder gives every single one the same
 * description, so rendering real data through many layouts would teach the
 * model one sentence rather than the concept of a duty bullet.
 *
 * Literal arrays in the style of this project's seeders. Deliberately spans
 * every program PLV offers, not just IT, and deliberately includes skills that
 * are absent from the skills table — otherwise the model learns the shortcut
 * "a skills line is a line containing known skill names", which collapses the
 * moment a real resume lists something unseeded.
 */
class SyntheticCorpus
{
    /** Section heading wordings, so the model never keys on one spelling. */
    public const HEADINGS = [
        'summary' => ['PROFESSIONAL SUMMARY', 'Summary', 'CAREER OBJECTIVE', 'Objective', 'Profile', 'ABOUT ME'],
        'skills' => ['SKILLS', 'Technical Skills', 'CORE COMPETENCIES', 'Key Skills', 'Skills & Tools', 'AREAS OF EXPERTISE'],
        'experience' => ['WORK EXPERIENCE', 'Professional Experience', 'EMPLOYMENT HISTORY', 'Experience', 'Work History', 'RELEVANT EXPERIENCE'],
        'projects' => ['PROJECTS', 'Key Projects', 'ACADEMIC PROJECTS', 'Selected Projects', 'Project Experience'],
        'certifications' => ['CERTIFICATIONS', 'Certifications & Trainings', 'LICENSES & CERTIFICATIONS', 'Trainings', 'SEMINARS ATTENDED', 'Professional Development'],
        'education' => ['EDUCATION', 'Educational Background', 'ACADEMIC BACKGROUND', 'Education & Training'],
        'contact' => ['CONTACT', 'Contact Information', 'DETAILS', 'Get In Touch'],
    ];

    public const FIRST_NAMES = [
        'Maria', 'Juan', 'Ana', 'Jose', 'Trisha', 'Mark', 'Bea', 'Paolo', 'Kristine', 'Ryan',
        'Angelica', 'Miguel', 'Joyce', 'Carlo', 'Danica', 'Rafael', 'Nicole', 'Emmanuel', 'Sofia', 'Lance',
    ];

    public const LAST_NAMES = [
        'Dela Cruz', 'Santos', 'Reyes', 'Bautista', 'Garcia', 'Mendoza', 'Torres', 'Aquino',
        'Ramos', 'Villanueva', 'Castillo', 'Navarro', 'Domingo', 'Salazar', 'Ocampo', 'Panganiban',
    ];

    public const CITIES = [
        'Valenzuela City', 'Quezon City', 'Manila', 'Caloocan City', 'Malabon City',
        'Navotas City', 'Marikina City', 'Pasig City', 'Bulacan', 'Meycauayan',
    ];

    public const STREETS = [
        'Maligaya St.', 'Gen. Luna St.', 'Rizal Ave.', 'Mabini St.', 'Bonifacio St.',
        'Sampaguita St.', 'Narra St.', 'Acacia St.', 'Ilang-Ilang St.', 'Kalayaan Ave.',
    ];

    /** Job titles spanning every college, keyed by a loose field so duties stay plausible. */
    public const JOB_TITLES = [
        'it' => [
            'Junior Web Developer', 'Backend Developer', 'Frontend Developer', 'Software Engineer',
            'IT Support Specialist', 'Quality Assurance Tester', 'Systems Administrator',
            'Database Administrator', 'Mobile App Developer', 'Data Analyst', 'Technical Support Associate',
        ],
        'education' => [
            'Elementary School Teacher', 'High School Mathematics Teacher', 'Teaching Assistant',
            'Academic Tutor', 'Curriculum Development Assistant', 'School Librarian', 'Guidance Associate',
        ],
        'accountancy' => [
            'Junior Accountant', 'Accounting Assistant', 'Bookkeeper', 'Accounts Payable Clerk',
            'Audit Associate', 'Payroll Assistant', 'Tax Associate',
        ],
        'business' => [
            'Administrative Assistant', 'Human Resources Assistant', 'Marketing Associate',
            'Sales Representative', 'Operations Assistant', 'Customer Service Representative',
            'Purchasing Assistant', 'Business Development Associate',
        ],
        'engineering' => [
            'Junior Civil Engineer', 'Electrical Engineering Assistant', 'CAD Operator',
            'Site Engineer', 'Maintenance Technician', 'Project Engineering Assistant',
        ],
        'social' => [
            'Community Development Worker', 'Social Welfare Assistant', 'Barangay Program Coordinator',
            'Psychometrician', 'Research Assistant', 'Public Information Assistant',
        ],
    ];

    /** Duty templates with slots, so descriptions vary lexically rather than repeating. */
    public const DUTY_TEMPLATES = [
        'it' => [
            'Developed {count} {artifact} used by the {team} team',
            'Built and maintained {artifact} using {tool}',
            'Fixed {count} reported defects in the {system}',
            'Wrote automated tests covering the {system}',
            'Optimised slow database queries and added missing indexes',
            'Integrated {tool} into the existing {system}',
            'Documented {artifact} for the {team} team',
            'Migrated legacy {artifact} to {tool}',
            'Reviewed pull requests and mentored {count} interns',
            'Deployed releases and monitored errors after each rollout',
        ],
        'education' => [
            'Prepared daily lesson plans for {count} sections',
            'Taught {subject} to {count} students per class',
            'Checked and recorded student output in the class record',
            'Facilitated remedial sessions for struggling learners',
            'Coordinated with parents during regular consultations',
            'Prepared instructional materials for {subject}',
            'Assisted in the preparation of school programmes',
        ],
        'accountancy' => [
            'Prepared monthly financial statements and schedules',
            'Reconciled {count} bank accounts every month',
            'Processed accounts payable and supplier disbursements',
            'Assisted in the annual external audit',
            'Filed BIR returns and maintained supporting documents',
            'Maintained the general ledger using {tool}',
            'Prepared payroll for {count} employees',
        ],
        'business' => [
            'Handled customer enquiries through phone and email',
            'Prepared weekly sales reports for management',
            'Maintained employee records and {count} personnel files',
            'Assisted in recruitment and onboarding of new hires',
            'Coordinated with suppliers on purchase orders',
            'Encoded transactions into {tool}',
            'Organised company events and staff activities',
        ],
        'engineering' => [
            'Prepared construction drawings using {tool}',
            'Assisted in site inspection and progress monitoring',
            'Prepared quantity estimates and material take-offs',
            'Checked compliance with approved plans and specifications',
            'Performed preventive maintenance on {count} units',
            'Prepared daily accomplishment reports for the site',
        ],
        'social' => [
            'Conducted home visits and prepared case reports',
            'Facilitated community assemblies in {count} barangays',
            'Assisted in the implementation of livelihood programmes',
            'Encoded beneficiary records and prepared summaries',
            'Administered and scored psychological assessments',
            'Assisted in data gathering for ongoing research',
        ],
    ];

    public const DUTY_SLOTS = [
        'count' => ['two', 'three', 'five', 'several', '10', '12', '20', '30'],
        'artifact' => ['reporting endpoints', 'internal dashboards', 'REST APIs', 'admin modules',
            'data pipelines', 'user interfaces', 'automated reports', 'monitoring scripts'],
        'team' => ['finance', 'operations', 'registrar', 'HR', 'marketing', 'support', 'management'],
        'tool' => ['Laravel', 'MySQL', 'React', 'Git', 'Microsoft Excel', 'QuickBooks', 'AutoCAD', 'SPSS', 'Figma'],
        'system' => ['enrolment system', 'inventory system', 'payroll system', 'booking portal',
            'records system', 'point-of-sale system'],
        'subject' => ['Mathematics', 'English', 'Science', 'Filipino', 'Araling Panlipunan', 'ICT'],
    ];

    public const ORGANISATIONS = [
        'Acme Digital Inc.', 'Northbridge Solutions Corp.', 'Valenzuela City Hall',
        'Metro Retail Group', 'Pioneer Logistics Inc.', 'Sunrise Manufacturing Corp.',
        'BrightPath Academy', 'St. Jerome Learning Center', 'Golden Harvest Foods Inc.',
        'Cityline Construction Corp.', 'DataForge Analytics', 'Summit BPO Services',
        'Greenfield Agri Ventures', 'Union Bank Branch Office', 'Maharlika Cooperative',
        'Department of Social Welfare and Development', 'Philippine Red Cross',
        'TechnoServe Philippines', 'Horizon Realty Inc.', 'Bayanihan Foundation',
        'Meridian Hospital', 'Lakeside Public Library', 'Craftline Apparel Corp.',
        'Silverline Freight Services', 'Nexus Software Labs', 'Kapatiran Community Center',
    ];

    /** @return array<int,array{name:string,issuer:string,type:string}> */
    public const CERTIFICATIONS = [
        ['name' => 'Civil Service Professional Eligibility', 'issuer' => 'Civil Service Commission', 'type' => 'certification'],
        ['name' => 'Licensure Examination for Teachers', 'issuer' => 'Professional Regulation Commission', 'type' => 'certification'],
        ['name' => 'National Certificate II in Computer Systems Servicing', 'issuer' => 'TESDA', 'type' => 'certification'],
        ['name' => 'AWS Cloud Practitioner', 'issuer' => 'Amazon Web Services', 'type' => 'certification'],
        ['name' => 'Microsoft Office Specialist', 'issuer' => 'Microsoft', 'type' => 'certification'],
        ['name' => 'Google Data Analytics Certificate', 'issuer' => 'Coursera', 'type' => 'certification'],
        ['name' => 'Cisco Certified Network Associate', 'issuer' => 'Cisco', 'type' => 'certification'],
        ['name' => 'Certified Bookkeeper', 'issuer' => 'Institute of Certified Bookkeepers', 'type' => 'certification'],
        ['name' => 'Basic Occupational Safety and Health', 'issuer' => 'DOLE', 'type' => 'training'],
        ['name' => 'Data Privacy Act Awareness Training', 'issuer' => 'National Privacy Commission', 'type' => 'training'],
        ['name' => 'Cybersecurity Fundamentals Workshop', 'issuer' => 'DICT', 'type' => 'training'],
        ['name' => 'Financial Literacy Training', 'issuer' => 'Bangko Sentral ng Pilipinas', 'type' => 'training'],
        ['name' => 'First Aid and Basic Life Support', 'issuer' => 'Philippine Red Cross', 'type' => 'training'],
        ['name' => 'Classroom Management Seminar', 'issuer' => 'Department of Education', 'type' => 'seminar'],
        ['name' => 'Career Development Seminar', 'issuer' => 'Pamantasan ng Lungsod ng Valenzuela', 'type' => 'seminar'],
        ['name' => 'Industry Immersion Seminar', 'issuer' => 'PLV Extension Office', 'type' => 'seminar'],
        ['name' => 'Research Writing Workshop', 'issuer' => 'PLV Research Office', 'type' => 'seminar'],
        ['name' => 'Leadership and Values Formation', 'issuer' => 'Valenzuela City Youth Office', 'type' => 'seminar'],
    ];

    public const SUMMARY_TEMPLATES = [
        'Aspiring {role} with hands-on experience in {focus}. Eager to contribute to a growing organisation.',
        '{degree} graduate with {years} years of experience in {focus}. Known for {trait} and attention to detail.',
        'Detail-oriented {role} skilled in {focus}. Seeking a role where I can continue developing professionally.',
        'Results-driven {role} with a background in {focus} and a strong commitment to {trait}.',
        'Recent {degree} graduate seeking an entry-level {role} position. Strong foundation in {focus}.',
        'Dedicated professional with practical experience in {focus}, looking to apply {trait} in a dynamic team.',
        'Hardworking {role} experienced in {focus}. Committed to continuous learning and quality output.',
    ];

    public const SUMMARY_SLOTS = [
        'role' => ['developer', 'accountant', 'teacher', 'administrative assistant', 'engineer',
            'analyst', 'customer service associate', 'community worker'],
        'focus' => ['web application development', 'financial reporting', 'classroom instruction',
            'office administration', 'construction supervision', 'data analysis',
            'client support', 'community programmes', 'records management'],
        'trait' => ['reliability', 'strong communication', 'teamwork', 'problem solving',
            'adaptability', 'time management'],
        'years' => ['two', 'three', 'four', 'five'],
        'degree' => ['BSIT', 'BSBA', 'BSA', 'BSEd', 'BSCE', 'AB Psychology'],
    ];

    /**
     * Skills the skills table does not contain. Without these the model can
     * cheat by keying on gazetteer hits alone.
     */
    public const OFF_GAZETTEER_SKILLS = [
        'Kubernetes', 'Docker', 'Redis', 'GraphQL', 'Tailwind CSS', 'Vue.js', 'Svelte', 'Kotlin',
        'Swift', 'Rust', 'Terraform', 'Jenkins', 'Power BI', 'Tableau', 'SAP', 'Oracle Financials',
        'Xero', 'Zoho Books', 'Lesson Planning', 'Classroom Management', 'Curriculum Design',
        'Payroll Processing', 'Bank Reconciliation', 'Inventory Management', 'Procurement',
        'Client Onboarding', 'Technical Writing', 'Public Speaking', 'Event Coordination',
        'Case Management', 'Community Organising', 'Statistical Analysis', 'Survey Design',
        'AutoCAD Civil 3D', 'Revit', 'Quantity Surveying', 'Occupational Safety', 'Quality Control',
        'Negotiation', 'Conflict Resolution', 'Mentoring', 'Stakeholder Management',
    ];

    public const PROGRAMS = [
        'it' => 'Bachelor of Science in Information Technology',
        'education' => 'Bachelor of Secondary Education',
        'accountancy' => 'Bachelor of Science in Accountancy',
        'business' => 'Bachelor of Science in Business Administration',
        'engineering' => 'Bachelor of Science in Civil Engineering',
        'social' => 'Bachelor of Arts in Psychology',
    ];

    public const COLLEGES = [
        'it' => 'College of Engineering and Information Technology',
        'engineering' => 'College of Engineering and Information Technology',
        'education' => 'College of Education',
        'accountancy' => 'College of Accountancy and Business Administration',
        'business' => 'College of Accountancy and Business Administration',
        'social' => 'College of Arts and Sciences',
    ];

    public const SCHOOLS = [
        'Pamantasan ng Lungsod ng Valenzuela (PLV)',
        'Pamantasan ng Lungsod ng Valenzuela',
        'PLV - Valenzuela City',
    ];

    public const FIELDS = ['it', 'education', 'accountancy', 'business', 'engineering', 'social'];
}
