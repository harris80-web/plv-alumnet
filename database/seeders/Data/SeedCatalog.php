<?php

namespace Database\Seeders\Data;

/**
 * Text and reference data for RealisticSeeder — names, addresses, per-course
 * profiles (skills, jobs, certifications), job-description building blocks,
 * notices, testimonials, conversations and chatbot exchanges. Kept apart
 * from the seeder so the seeding logic stays readable.
 */
class SeedCatalog
{
    public const FIRST_MALE = ['Juan', 'Jose', 'Mark', 'John Paul', 'Christian', 'Carlo', 'Miguel', 'Paolo', 'Rafael', 'Gabriel', 'Joshua', 'Kenneth', 'Nathaniel', 'Adrian', 'Angelo', 'Bryan', 'Daniel', 'Emmanuel', 'Francis', 'Gerald', 'Harold', 'Ian', 'Jerome', 'Kevin', 'Lance', 'Marvin', 'Neil', 'Oliver', 'Patrick', 'Ramon', 'Ryan', 'Sean', 'Tristan', 'Vincent', 'Warren', 'Aldrin', 'Benedict', 'Carl', 'Dominic', 'Edgar', 'Ferdinand', 'Gilbert', 'Ivan', 'Jayson', 'Kristoffer', 'Leo', 'Mico', 'Noel', 'Renz', 'Rodel', 'Joel', 'Arvin', 'Cedric', 'Darwin', 'Jomar', 'Karl', 'Ronald', 'Allan', 'Jericho', 'Ralph', 'Enrico', 'Luis', 'Mateo', 'Nico', 'Elijah'];

    public const FIRST_FEMALE = ['Maria', 'Ana', 'Kristine', 'Angelica', 'Patricia', 'Camille', 'Nicole', 'Jasmine', 'Bianca', 'Andrea', 'Charlene', 'Danica', 'Erika', 'Frances', 'Hazel', 'Irene', 'Joanna', 'Katrina', 'Mae', 'Nina', 'Pauline', 'Rachelle', 'Shiela', 'Trisha', 'Vanessa', 'Ysabel', 'Aira', 'Bea', 'Cheska', 'Diane', 'Ella', 'Faith', 'Grace', 'Hannah', 'Isabel', 'Janine', 'Kyla', 'Lea', 'Mikaela', 'Nathalie', 'Princess', 'Rica', 'Sofia', 'Therese', 'Ivy', 'Jessa', 'Mary Joy', 'Rose Ann', 'Jhoanna', 'Cristina', 'Lorraine', 'Alyssa', 'Denise', 'Joyce', 'Kimberly', 'Lyka', 'Margarette', 'Stephanie', 'Clarisse', 'Venice', 'Abegail', 'Roxanne', 'Czarina', 'Gelene', 'Arlene'];

    public const LAST = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Ocampo', 'Garcia', 'Mendoza', 'Torres', 'Tomas', 'Andrada', 'Castillo', 'Flores', 'Villanueva', 'Ramos', 'Castro', 'Rivera', 'Aquino', 'Navarro', 'Salazar', 'Mercado', 'Aguilar', 'Dela Cruz', 'De Leon', 'Gonzales', 'Lopez', 'Morales', 'Pascual', 'Domingo', 'Fernandez', 'Valdez', 'Soriano', 'Manalo', 'Marquez', 'Panganiban', 'Lim', 'Tan', 'Sy', 'Gomez', 'Diaz', 'Javier', 'Santiago', 'Robles', 'Alvarez', 'Hernandez', 'Sison', 'Galang', 'Dizon', 'Tolentino', 'Mariano', 'Ignacio', 'Lacson', 'Yap', 'Concepcion', 'Padilla', 'Del Rosario', 'Evangelista', 'Francisco', 'Gutierrez', 'Jimenez', 'Legaspi', 'Macaraeg', 'Natividad', 'Pineda', 'Quiambao', 'Roxas', 'Samson', 'Tiongson', 'Umali', 'Vergara', 'Zamora', 'Agustin', 'Buenaventura', 'Cabrera', 'Dalisay', 'Esguerra', 'Fajardo', 'Guevarra', 'Hilario', 'Ilagan', 'Jacinto', 'Lagman', 'Magbanua', 'Nepomuceno', 'Ong', 'Perez', 'Quizon', 'Rosales', 'Sarmiento', 'Tuazon', 'Uy', 'Velasco', 'Abad', 'Baltazar', 'Cortez', 'Enriquez', 'Ferrer', 'Lacsamana', 'Mangubat', 'Nolasco', 'Oliveros', 'Pacheco', 'Regalado', 'Sevilla', 'Trinidad', 'Bernardo', 'Capistrano', 'Dimaculangan', 'Espiritu', 'Florentino', 'Gatdula', 'Labrador', 'Malabanan', 'Pangilinan', 'Salonga', 'Teodoro', 'Viray'];

    public const SUFFIXES = ['Jr.', 'Sr.', 'II', 'III'];

    public const BARANGAYS = ['Malinta', 'Karuhatan', 'Marulas', 'Maysan', 'Dalandanan', 'Paso de Blas', 'Lawang Bato', 'Punturin', 'Bignay', 'Canumay East', 'Canumay West', 'Gen. T. de Leon', 'Ugong', 'Mapulang Lupa', 'Lingunan', 'Polo', 'Arkong Bato', 'Bisig', 'Mabolo', 'Parada'];

    public const STREETS = ['McArthur Highway', 'Maysan Road', 'Gen. T. de Leon Road', 'Karuhatan Road', 'Paso de Blas Road', 'Ugong Road', 'Tongco Street', 'Fatima Avenue', 'Bonifacio Street', 'Rizal Avenue', 'Mabini Street', 'Padrigal Street', 'Coloong Road', 'East Service Road', 'Lawang Bato Road'];

    public const OTHER_CITIES = ['Quezon City', 'Caloocan City', 'Malabon City', 'Makati City', 'Pasig City', 'Meycauayan, Bulacan', 'Marilao, Bulacan', 'Manila', 'Taguig City', 'Mandaluyong City'];

    public const OTHER_CITY_STREETS = ['EDSA', 'Ortigas Avenue', 'Ayala Avenue', 'Commonwealth Avenue', 'Quezon Avenue', 'Shaw Boulevard', 'C-5 Road', 'Taft Avenue', 'Roxas Boulevard', 'Katipunan Avenue'];

    /** Per-course data: skills (must exist in SkillsSeeder), aligned industry, jobs to post, current-job titles, OJT/projects, certifications, summary lines. */
    public const PROGRAMS = [
        'Bachelor of Arts in Communication' => [
            'skills' => ['Content Writing', 'Public Speaking', 'Media Production', 'Digital Marketing', 'Canva', 'Adobe Photoshop', 'Communication', 'English', 'Filipino', 'Teamwork'],
            'industry' => 'Business & Finance',
            'jobs' => ['Marketing Coordinator', 'Content Writer', 'Social Media Specialist', 'Corporate Communications Assistant', 'Public Relations Associate', 'Video Editor'],
            'positions' => ['Content Writer', 'Social Media Associate', 'Copywriter', 'Communications Officer', 'Video Editor', 'PR Associate'],
            'projects' => ['Campus news broadcast (producer)', 'Short documentary on Valenzuela street food', 'Social media campaign for a student organization', 'Thesis: Media literacy among Grade 10 students'],
            'certs' => [['Google Digital Marketing & E-commerce', 'certification', 'Google / Coursera'], ['Content Creation Workshop', 'training', 'PLV Communication Society'], ['Seminar on Responsible Journalism', 'seminar', 'Philippine Press Institute']],
            'summary' => 'Communication graduate with hands-on experience in writing, editing and producing content for print, web and social media. Comfortable working with tight deadlines and turning briefs into clear, engaging stories.',
        ],
        'Bachelor of Early Childhood Education' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Public Speaking', 'Canva', 'Communication', 'Filipino', 'English', 'Microsoft Word', 'Adaptability'],
            'industry' => 'Education',
            'jobs' => ['Preschool Teacher', 'Teacher Aide', 'Learning Support Specialist', 'Academic Tutor', 'Daycare Center Coordinator'],
            'positions' => ['Preschool Teacher', 'Kindergarten Teacher', 'Teacher Aide', 'Daycare Worker', 'Learning Support Specialist'],
            'projects' => ['Practice teaching — Kindergarten class (Malinta Elementary School)', 'Play-based learning materials for numeracy', 'Storytelling program for Day Care Centers'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['Early Childhood Care and Development Training', 'training', 'ECCD Council'], ['Seminar on Inclusive Education', 'seminar', 'DepEd Valenzuela']],
            'summary' => 'Licensed early childhood educator who believes children learn best through play, routine and warm relationships. Experienced in preparing developmentally appropriate lesson plans and communicating closely with parents.',
        ],
        'Bachelor of Science in Accountancy' => [
            'skills' => ['Bookkeeping', 'Tax Preparation', 'Financial Auditing', 'Financial Modeling', 'QuickBooks', 'Microsoft Excel', 'Critical Thinking', 'Time Management'],
            'industry' => 'Business & Finance',
            'jobs' => ['Junior Accountant', 'Accounting Associate', 'Accounts Payable Clerk', 'Bookkeeper', 'Audit Associate', 'Tax Associate'],
            'positions' => ['Junior Accountant', 'Audit Associate', 'Accounts Payable Specialist', 'Bookkeeper', 'Tax Associate', 'Staff Accountant'],
            'projects' => ['OJT — accounting department of a trading company', 'Thesis: Tax compliance of micro businesses in Valenzuela', 'Bookkeeping system for a sari-sari store cooperative'],
            'certs' => [['Certified Public Accountant (CPA)', 'certification', 'Professional Regulation Commission'], ['Bookkeeping NC III', 'certification', 'TESDA'], ['Seminar on the TRAIN Law and EOPT Act', 'seminar', 'Bureau of Internal Revenue']],
            'summary' => 'Detail-oriented accountancy graduate experienced in bookkeeping, reconciliation and preparing BIR filings. Comfortable with Excel and QuickBooks and committed to accurate, on-time reporting.',
        ],
        'Bachelor of Science in Business Administration Major in Financial Management' => [
            'skills' => ['Financial Modeling', 'Bookkeeping', 'Microsoft Excel', 'QuickBooks', 'Market Research', 'Critical Thinking', 'Problem Solving'],
            'industry' => 'Business & Finance',
            'jobs' => ['Financial Analyst', 'Credit and Collection Analyst', 'Treasury Assistant', 'Bookkeeper', 'Accounting Associate'],
            'positions' => ['Financial Analyst', 'Credit Analyst', 'Treasury Assistant', 'Loans Officer', 'Collection Specialist'],
            'projects' => ['OJT — treasury department of a rural bank', 'Feasibility study: water refilling station in Karuhatan', 'Financial literacy module for senior high students'],
            'certs' => [['Financial Modeling Fundamentals', 'training', 'Corporate Finance Institute'], ['Seminar on Personal Finance and Investing', 'seminar', 'Bangko Sentral ng Pilipinas'], ['Microsoft Office Specialist: Excel', 'certification', 'Microsoft']],
            'summary' => 'Financial management graduate who enjoys turning numbers into decisions. Experienced in budgeting, cash-flow monitoring and preparing financial reports for management.',
        ],
        'Bachelor of Science in Business Administration Major in Human Resource Management' => [
            'skills' => ['Recruitment & Selection', 'Payroll Management', 'Microsoft Excel', 'Communication', 'Leadership', 'Microsoft Word', 'Teamwork'],
            'industry' => 'Business & Finance',
            'jobs' => ['HR Generalist', 'Recruitment Specialist', 'Payroll Associate', 'HR Assistant', 'Training Coordinator'],
            'positions' => ['HR Assistant', 'Recruitment Specialist', 'Payroll Associate', 'HR Generalist', 'Talent Acquisition Associate'],
            'projects' => ['OJT — HR department of a manufacturing plant', 'Employee engagement survey for a local BPO', 'Thesis: Work-life balance of hybrid employees'],
            'certs' => [['Certified Human Resource Associate', 'certification', 'PMAP'], ['Labor Code Updates Seminar', 'seminar', 'DOLE NCR'], ['Payroll Processing Training', 'training', 'PMAP']],
            'summary' => 'HR graduate with experience in end-to-end recruitment, onboarding and payroll support. People-oriented, organized and familiar with Philippine labor standards and government-mandated benefits.',
        ],
        'Bachelor of Science in Business Administration Major in Marketing Management' => [
            'skills' => ['Digital Marketing', 'Market Research', 'Canva', 'Adobe Photoshop', 'Content Writing', 'Public Speaking', 'Communication'],
            'industry' => 'Business & Finance',
            'jobs' => ['Brand Coordinator', 'Marketing Coordinator', 'Digital Marketing Specialist', 'Sales Associate', 'Market Research Analyst'],
            'positions' => ['Marketing Assistant', 'Brand Associate', 'Sales Executive', 'Digital Marketing Specialist', 'Account Executive'],
            'projects' => ['OJT — marketing team of a food manufacturer', 'Social media launch plan for a local bakery', 'Market study: consumer preferences for milk tea in Valenzuela'],
            'certs' => [['Google Ads Search Certification', 'certification', 'Google'], ['Meta Certified Digital Marketing Associate', 'certification', 'Meta'], ['Seminar on Consumer Behavior', 'seminar', 'Philippine Marketing Association']],
            'summary' => 'Marketing graduate with a mix of creative and analytical skills — from planning social media calendars to reading campaign results. Enjoys building brands that people remember.',
        ],
        'Bachelor of Science in Civil Engineering' => [
            'skills' => ['AutoCAD', 'Structural Analysis', 'Project Estimation', 'Microsoft Excel', 'Problem Solving', 'Teamwork', 'Leadership'],
            'industry' => 'Engineering & Construction',
            'jobs' => ['Junior Civil Engineer', 'Site Engineer', 'Project Estimator', 'Drafting Technician', 'Quantity Surveyor', 'Civil Engineering Intern'],
            'positions' => ['Site Engineer', 'Junior Civil Engineer', 'Quantity Surveyor', 'Project Engineer', 'Structural Draftsman'],
            'projects' => ['OJT — site engineering team, 3-storey commercial building', 'Thesis: Flood-resilient drainage design for Barangay Dalandanan', 'Structural analysis of a pedestrian footbridge'],
            'certs' => [['Registered Civil Engineer', 'certification', 'Professional Regulation Commission'], ['Construction Occupational Safety and Health (COSH)', 'training', 'DOLE-OSHC'], ['AutoCAD Certified User', 'certification', 'Autodesk']],
            'summary' => 'Civil engineering graduate with site and design experience — quantity take-offs, AutoCAD drafting and coordinating with foremen on the ground. Safety-minded and comfortable on active construction sites.',
        ],
        'Bachelor of Science in Electrical Engineering' => [
            'skills' => ['Circuit Design', 'Electrical Systems Design', 'AutoCAD', 'Project Estimation', 'Microsoft Excel', 'Problem Solving'],
            'industry' => 'Engineering & Construction',
            'jobs' => ['Electrical Maintenance Technician', 'Junior Electrical Engineer', 'Electrical Design Engineer', 'Project Estimator', 'Drafting Technician'],
            'positions' => ['Electrical Engineer', 'Maintenance Engineer', 'Electrical Design Engineer', 'Electrical Estimator', 'Plant Electrician'],
            'projects' => ['OJT — plant maintenance, food manufacturing facility', 'Thesis: Solar-powered streetlights for barangay roads', 'Electrical layout for a 2-storey residence'],
            'certs' => [['Registered Electrical Engineer', 'certification', 'Professional Regulation Commission'], ['Philippine Electrical Code Seminar', 'seminar', 'IIEE'], ['Electrical Installation and Maintenance NC II', 'certification', 'TESDA']],
            'summary' => 'Electrical engineering graduate experienced in power distribution layouts, preventive maintenance and troubleshooting. Familiar with the Philippine Electrical Code and committed to safe, efficient installations.',
        ],
        'Bachelor of Science in Information Technology' => [
            'skills' => ['PHP', 'Laravel', 'JavaScript', 'HTML & CSS', 'React', 'Python', 'Java', 'MySQL', 'Git & GitHub', 'Figma', 'Network Administration', 'Problem Solving'],
            'industry' => 'Technology',
            'jobs' => ['Backend Developer', 'Front-End Developer', 'Full Stack Developer', 'Mobile App Developer', 'QA Tester', 'IT Support Specialist', 'IT Helpdesk Associate', 'Systems Analyst', 'UI/UX Designer', 'Data Entry Specialist', 'Technical Support Engineer', 'Network Administrator'],
            'positions' => ['Junior Web Developer', 'Software Engineer', 'IT Support Specialist', 'QA Analyst', 'Network Administrator', 'Full Stack Developer', 'UI/UX Designer'],
            'projects' => ['Capstone: PLV-AlumNet alumni portal (Laravel, MySQL)', 'Inventory system for a school canteen', 'Mobile attendance app using QR codes', 'OJT — IT department, city government office'],
            'certs' => [['CCNA: Introduction to Networks', 'certification', 'Cisco Networking Academy'], ['Computer Systems Servicing NC II', 'certification', 'TESDA'], ['Laravel Bootcamp', 'training', 'Laravel PH Community'], ['Seminar on Cybersecurity Awareness', 'seminar', 'DICT']],
            'summary' => 'IT graduate who builds and maintains web applications end to end — from database design to the user interface. Comfortable with Laravel, JavaScript and Git, and quick to learn new tools.',
        ],
        'Bachelor of Science in Psychology' => [
            'skills' => ['Psychological Assessment', 'Counseling', 'Behavioral Analysis', 'SPSS', 'Critical Thinking', 'Communication', 'Microsoft Excel'],
            'industry' => 'Healthcare',
            'jobs' => ['HR Assistant', 'Guidance Associate', 'Wellness Program Coordinator', 'Psychometrician', 'Behavioral Therapist Aide', 'Recruitment Specialist'],
            'positions' => ['Psychometrician', 'HR Assistant', 'Guidance Associate', 'Behavioral Therapist', 'Recruitment Associate'],
            'projects' => ['Practicum — guidance office of a public high school', 'Thesis: Academic stress and coping among working students', 'Assisted in psychological testing for a hiring drive'],
            'certs' => [['Registered Psychometrician', 'certification', 'Professional Regulation Commission'], ['Psychological First Aid Training', 'training', 'Philippine Red Cross'], ['Seminar on Mental Health in the Workplace', 'seminar', 'Psychological Association of the Philippines']],
            'summary' => 'Psychology graduate and psychometrician with experience in administering and scoring psychological tests, assisting in counseling and supporting employee wellness programs.',
        ],
        'Bachelor of Public Administration' => [
            'skills' => ['Public Administration', 'Policy Analysis', 'Community Organizing', 'Microsoft Word', 'Microsoft Excel', 'Leadership', 'Public Speaking'],
            'industry' => 'Government & Public Service',
            'jobs' => ['Administrative Assistant', 'Records Officer', 'Policy Research Assistant', 'Public Affairs Assistant', 'Community Development Officer'],
            'positions' => ['Administrative Officer', 'Records Officer', 'Barangay Secretary', 'Policy Research Assistant', 'Executive Assistant'],
            'projects' => ['OJT — City Planning and Development Office', 'Thesis: Citizen satisfaction with barangay services', 'Assisted in a participatory budgeting consultation'],
            'certs' => [['Career Service Professional Eligibility', 'certification', 'Civil Service Commission'], ['Seminar on Local Governance and Ethics', 'seminar', 'DILG'], ['Records Management Training', 'training', 'National Archives of the Philippines']],
            'summary' => 'Public administration graduate with civil service eligibility and experience in records management, report writing and coordinating community programs.',
        ],
        'Bachelor of Science in Social Work' => [
            'skills' => ['Case Management', 'Community Organizing', 'Counseling', 'Public Speaking', 'Communication', 'Microsoft Word', 'Filipino'],
            'industry' => 'Government & Public Service',
            'jobs' => ['Case Worker', 'Community Development Officer', 'Social Welfare Assistant', 'Program Coordinator', 'Records Officer'],
            'positions' => ['Social Worker', 'Case Manager', 'Community Development Worker', 'Program Officer', 'Social Welfare Assistant'],
            'projects' => ['Field instruction — City Social Welfare and Development Office', 'Community needs assessment in Barangay Bignay', 'Feeding program coordination for 120 children'],
            'certs' => [['Registered Social Worker', 'certification', 'Professional Regulation Commission'], ['Trauma-Informed Care Training', 'training', 'DSWD'], ['Seminar on Child Protection Policies', 'seminar', 'Council for the Welfare of Children']],
            'summary' => 'Registered social worker experienced in case management, home visits and community organizing. Passionate about connecting families with the services they need.',
        ],
        'Bachelor of Secondary Education Major in English' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'English', 'Content Writing', 'Public Speaking', 'Communication'],
            'industry' => 'Education',
            'jobs' => ['English Teacher', 'Academic Tutor', 'Content Writer', 'Curriculum Assistant', 'ESL Tutor'],
            'positions' => ['English Teacher', 'ESL Teacher', 'Academic Tutor', 'Content Writer', 'Reading Specialist'],
            'projects' => ['Practice teaching — Grade 9 English', 'Reading intervention program for struggling readers', 'School paper adviser assistant'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['TESOL Certificate', 'certification', 'TESOL Philippines'], ['Seminar on Differentiated Instruction', 'seminar', 'DepEd']],
            'summary' => 'Licensed English teacher who designs engaging lessons in grammar, literature and communication. Experienced in reading intervention and in tutoring learners of different levels.',
        ],
        'Bachelor of Secondary Education Major in Filipino' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Filipino', 'Content Writing', 'Public Speaking', 'Communication'],
            'industry' => 'Education',
            'jobs' => ['Filipino Teacher', 'Academic Tutor', 'Curriculum Assistant', 'Translator', 'Instructional Aide'],
            'positions' => ['Filipino Teacher', 'Translator', 'Academic Tutor', 'Instructional Materials Writer'],
            'projects' => ['Practice teaching — Grade 8 Filipino', 'Pagsasalin ng mga kuwentong pambata', 'Buwan ng Wika program coordinator'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['Seminar sa Masining na Pagsulat', 'seminar', 'Komisyon sa Wikang Filipino'], ['Translation Workshop', 'training', 'Filipinas Institute of Translation']],
            'summary' => 'Licensed Filipino teacher with a love for language and literature. Experienced in creating culturally relevant lessons and in translation work.',
        ],
        'Bachelor of Secondary Education Major in Mathematics' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Microsoft Excel', 'Critical Thinking', 'Problem Solving', 'Communication'],
            'industry' => 'Education',
            'jobs' => ['Mathematics Teacher', 'Academic Tutor', 'Curriculum Assistant', 'Learning Support Specialist', 'Data Entry Specialist'],
            'positions' => ['Mathematics Teacher', 'Academic Tutor', 'Review Center Instructor', 'Learning Support Specialist'],
            'projects' => ['Practice teaching — Grade 10 Mathematics', 'Math remediation program using manipulatives', 'Coach, division-level Math quiz bee'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['GeoGebra for Teachers', 'training', 'GeoGebra Institute of Manila'], ['Seminar on Assessment in Mathematics', 'seminar', 'MATHTED']],
            'summary' => 'Licensed mathematics teacher who makes abstract ideas concrete through real-life problems and visual tools. Experienced in remediation and competition coaching.',
        ],
        'Bachelor of Secondary Education Major in Science' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Critical Thinking', 'Problem Solving', 'Microsoft Excel', 'Communication'],
            'industry' => 'Education',
            'jobs' => ['Science Teacher', 'Laboratory Assistant', 'Academic Tutor', 'Curriculum Assistant', 'Learning Support Specialist'],
            'positions' => ['Science Teacher', 'Laboratory Assistant', 'Academic Tutor', 'Science Coordinator'],
            'projects' => ['Practice teaching — Grade 9 Chemistry', 'Low-cost laboratory kits from recycled materials', 'Science investigatory project adviser'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['Laboratory Safety Training', 'training', 'DOST-SEI'], ['Seminar on Inquiry-Based Learning', 'seminar', 'DepEd']],
            'summary' => 'Licensed science teacher who turns the classroom into a lab — hands-on experiments, inquiry-based lessons and investigatory projects that build curiosity.',
        ],
        'Bachelor of Secondary Education Major in Social Studies' => [
            'skills' => ['Lesson Planning', 'Classroom Management', 'Curriculum Development', 'Public Speaking', 'Critical Thinking', 'Policy Analysis', 'Communication'],
            'industry' => 'Education',
            'jobs' => ['Social Studies Teacher', 'Academic Tutor', 'Curriculum Assistant', 'Research Assistant', 'Training Coordinator'],
            'positions' => ['Araling Panlipunan Teacher', 'Research Assistant', 'Academic Tutor', 'Museum Educator'],
            'projects' => ['Practice teaching — Grade 7 Araling Panlipunan', 'Local history module on Valenzuela (Polo)', 'Model United Nations club adviser assistant'],
            'certs' => [['Licensure Examination for Teachers (LET) Passer', 'certification', 'Professional Regulation Commission'], ['Seminar on Teaching Philippine History', 'seminar', 'National Historical Commission'], ['Research Methods Workshop', 'training', 'PLV Research Office']],
            'summary' => 'Licensed social studies teacher who connects history and current events to students’ own communities. Experienced in research and in facilitating student discussions.',
        ],
    ];

    /** Company name parts, by industry. */
    public const COMPANY_PREFIX = ['Northbridge', 'Bayview', 'Meridian', 'Summit', 'Horizon', 'Sunrise', 'Pacific Crest', 'Golden Gate', 'Cedarpoint', 'Harborview', 'Crescent', 'Lakeside', 'Metro North', 'Apex', 'Blue Ridge', 'Brightpath', 'Nexus', 'Orion', 'Pioneer', 'Silverleaf', 'Stonegate', 'Evergreen', 'Riverside', 'Highland', 'Kalayaan', 'Malinta', 'Karuhatan', 'Marulas', 'Maysan', 'Dalandanan', 'Polo', 'Bignay', 'Mabini', 'Luzon', 'Masagana', 'Bagong Pag-asa', 'Tanglaw', 'Sinag', 'Likha', 'Alon'];

    public const COMPANY_SUFFIX = [
        'Technology' => ['Technologies Inc.', 'Digital Solutions', 'Systems Corp.', 'Software Studio', 'IT Services', 'Data Labs', 'Web Solutions'],
        'Healthcare' => ['Medical Clinic', 'Health Services', 'Diagnostic Center', 'Wellness Center', 'General Hospital', 'Pharmacy Corp.'],
        'Business & Finance' => ['Trading Corporation', 'Financial Services', 'Marketing Agency', 'Consulting Group', 'Retail Corp.', 'Logistics Inc.', 'Lending Corp.', 'Foods Corporation', 'Manufacturing Inc.'],
        'Education' => ['Learning Center', 'Academy', 'Tutorial Services', 'Montessori School', 'Review Center', 'School of Arts and Sciences'],
        'Government & Public Service' => ['Foundation Inc.', 'Community Development Council', 'Multi-Purpose Cooperative', 'Social Services Network'],
        'Engineering & Construction' => ['Builders Inc.', 'Construction Corp.', 'Engineering Services', 'Electrical Contractors', 'Development Corp.', 'Design and Build'],
    ];

    public const CONTACT_POSITIONS = ['HR Manager', 'Talent Acquisition Lead', 'Recruitment Officer', 'HR Officer', 'Operations Manager', 'Owner', 'General Manager', 'People & Culture Lead', 'Administrative Manager', 'HR Business Partner'];

    public const COMPANY_ABOUT = [
        '%s is a growing company based in %s, serving clients across Metro Manila and nearby provinces.',
        'Founded in %d, %s has built a reputation for reliable service and for investing in the people who make it happen.',
        '%s is a locally owned business that values integrity, teamwork and continuous learning.',
        'At %s, we believe great work starts with great people. We are proud to hire fresh graduates and help them grow.',
    ];

    public const BENEFITS = ['HMO coverage from day one (with 1 free dependent)', '13th month pay and performance bonus', '15 days paid vacation and sick leave', 'Rice and transportation allowance', 'Government-mandated benefits (SSS, PhilHealth, Pag-IBIG)', 'Hybrid work setup after regularization', 'Annual training and certification budget', 'Free shuttle service from Valenzuela and Caloocan', 'Birthday leave', 'Free meals on duty', 'Clear career path and quarterly performance reviews', 'Employee discounts', 'Fully equipped office near major transport routes'];

    public const SCHEDULES = ['Monday to Friday, 8:00 AM – 5:00 PM', 'Monday to Friday, 9:00 AM – 6:00 PM', 'Monday to Saturday, 8:00 AM – 5:00 PM (half day on Saturdays)', 'Shifting schedule, 5 days a week', 'Flexible hours, 40 hours a week', 'Night shift, Monday to Friday'];

    /** Job description building blocks, by job category (picked by title keywords). */
    public const JOB_CATEGORIES = [
        'dev' => [
            'match' => '/developer|full stack|engineer\b.*software|software/i',
            'intro' => 'We are looking for a %s who enjoys building reliable, user-friendly web and mobile applications.',
            'resp' => ['Develop, test and maintain features for our web applications', 'Write clean, well-documented and testable code', 'Collaborate with designers and QA to deliver features on schedule', 'Integrate third-party APIs and payment gateways', 'Fix bugs and improve application performance', 'Participate in code reviews and sprint planning', 'Maintain database schemas and write efficient queries'],
            'qual' => ['Bachelor’s degree in Information Technology, Computer Science or related field', 'Experience with PHP/Laravel, JavaScript or a similar stack (internship or capstone projects count)', 'Familiarity with Git and REST APIs', 'Good understanding of relational databases (MySQL)', 'Fresh graduates with a strong portfolio are encouraged to apply', 'Good communication skills in English and Filipino'],
        ],
        'qa' => [
            'match' => '/\bqa\b|tester|quality/i',
            'intro' => 'Join our product team as a %s and help us ship software our customers can trust.',
            'resp' => ['Write and execute manual test cases for new features', 'Report, track and verify bugs in our issue tracker', 'Perform regression testing before every release', 'Work with developers to reproduce and isolate issues', 'Help build and maintain automated test suites', 'Document test results and release notes'],
            'qual' => ['Graduate of IT, Computer Science or any related course', 'Strong attention to detail and a curious mindset', 'Basic knowledge of SQL and web technologies', 'Experience with testing tools is a plus but not required', 'Able to work in an agile team'],
        ],
        'support' => [
            'match' => '/support|helpdesk|network|systems analyst|data entry|technical/i',
            'intro' => 'We need a dependable %s to keep our people and systems running smoothly.',
            'resp' => ['Provide first-level technical support to employees via phone, chat and walk-in', 'Install, configure and troubleshoot computers, printers and peripherals', 'Monitor network connectivity and escalate outages', 'Manage user accounts, access requests and password resets', 'Maintain an inventory of IT equipment and licenses', 'Document recurring issues and their fixes in the knowledge base', 'Encode and validate records accurately in our systems'],
            'qual' => ['Graduate of Information Technology or a related course', 'Knowledge of Windows, Microsoft 365 and basic networking', 'Customer-oriented with good communication skills', 'Willing to work on-site and on shifting schedules', 'TESDA CSS NC II or CCNA is an advantage'],
        ],
        'design' => [
            'match' => '/ui\/ux|designer|video editor|media/i',
            'intro' => 'We are hiring a creative %s to shape how our customers see and use our products.',
            'resp' => ['Create wireframes, prototypes and high-fidelity designs', 'Conduct user research and usability tests', 'Design marketing visuals and social media assets', 'Work closely with developers to implement designs accurately', 'Maintain our design system and brand guidelines'],
            'qual' => ['Portfolio showing UI, graphic or video work', 'Proficient in Figma, Canva or Adobe tools', 'Good eye for typography, color and layout', 'Able to explain design decisions clearly', 'Graduate of IT, Communication, Fine Arts or related course'],
        ],
        'accounting' => [
            'match' => '/account|bookkeeper|audit|tax|payable|treasury|credit|collection/i',
            'intro' => 'Our finance team is looking for a %s who takes pride in accurate, on-time work.',
            'resp' => ['Prepare journal entries and maintain the general ledger', 'Process accounts payable and receivable', 'Reconcile bank statements and petty cash monthly', 'Assist in preparing monthly and annual financial statements', 'Prepare BIR forms and support tax compliance', 'Assist external auditors during year-end audit', 'Maintain organized and complete supporting documents'],
            'qual' => ['Graduate of BS Accountancy, BSBA Financial Management or related course', 'CPA or CPA reviewee is an advantage', 'Proficient in Microsoft Excel (pivot tables, VLOOKUP)', 'Knowledge of QuickBooks, Xero or SAP is a plus', 'Detail-oriented, honest and able to meet deadlines'],
        ],
        'finance' => [
            'match' => '/financial analyst|analyst/i',
            'intro' => 'We are looking for a %s to help leadership make smarter, data-driven decisions.',
            'resp' => ['Prepare budgets, forecasts and variance reports', 'Analyze sales, cost and cash-flow trends', 'Build financial models for new projects and investments', 'Present findings to managers in clear, simple reports', 'Monitor key performance indicators across departments'],
            'qual' => ['Graduate of BSBA Financial Management, Accountancy, Economics or related course', 'Strong Excel and analytical skills', 'Good business writing and presentation skills', 'Fresh graduates with strong academic records are welcome'],
        ],
        'hr' => [
            'match' => '/\bhr\b|human|recruit|payroll|talent|training coordinator/i',
            'intro' => 'Our People team is growing and we need a %s who genuinely enjoys helping people grow.',
            'resp' => ['Manage end-to-end recruitment — sourcing, screening and scheduling interviews', 'Prepare job offers and handle pre-employment requirements', 'Facilitate onboarding and orientation for new hires', 'Process payroll inputs, leaves and government contributions', 'Maintain complete and confidential 201 files', 'Organize employee engagement activities and trainings'],
            'qual' => ['Graduate of BSBA HRM, Psychology or related course', 'Knowledge of the Labor Code and government-mandated benefits', 'Excellent communication and interpersonal skills', 'Proficient in Microsoft Office', 'Can handle confidential information with discretion'],
        ],
        'marketing' => [
            'match' => '/marketing|brand|sales|content|social media|communications|public relations|copy/i',
            'intro' => 'We are looking for a %s who can turn ideas into campaigns people talk about.',
            'resp' => ['Plan and execute monthly marketing and social media calendars', 'Write copy for posts, ads, newsletters and press releases', 'Coordinate with designers, suppliers and event partners', 'Track campaign performance and prepare reports', 'Support product launches, promos and in-store activations', 'Monitor competitors and market trends'],
            'qual' => ['Graduate of Marketing, Communication or related course', 'Excellent written and verbal communication in English and Filipino', 'Familiar with Meta Business Suite, Canva and basic analytics', 'Creative, organized and comfortable juggling several projects', 'With a portfolio of campaigns or writing samples'],
        ],
        'teaching' => [
            'match' => '/teacher|tutor|instructor|aide|esl|daycare|preschool|learning|curriculum|guidance|laboratory assistant/i',
            'intro' => 'We are looking for a passionate %s who wants to make a real difference in learners’ lives.',
            'resp' => ['Prepare daily lesson plans aligned with the curriculum', 'Deliver engaging lessons using varied teaching strategies', 'Assess learner progress and provide timely feedback', 'Maintain a safe, positive and inclusive classroom', 'Communicate regularly with parents and guardians', 'Prepare instructional materials and learning activities', 'Participate in school programs, trainings and meetings'],
            'qual' => ['Graduate of Education or related course (major in the subject area preferred)', 'LET passer or willing to take the next LET', 'Patient, creative and genuinely fond of working with learners', 'Good communication skills in English and Filipino', 'Fresh graduates are welcome to apply'],
        ],
        'civil' => [
            'match' => '/civil|site engineer|quantity|estimator|drafting|surveyor/i',
            'intro' => 'Our projects team needs a %s to help us deliver safe, quality builds on time and within budget.',
            'resp' => ['Prepare quantity take-offs, cost estimates and bills of materials', 'Produce and revise AutoCAD drawings and shop plans', 'Supervise daily site activities and coordinate with foremen', 'Monitor progress, materials and manpower against schedule', 'Ensure compliance with safety standards and building codes', 'Prepare daily and weekly site reports'],
            'qual' => ['Graduate of BS Civil Engineering or related course', 'Licensed Civil Engineer or board exam reviewee', 'Proficient in AutoCAD and Microsoft Excel', 'Willing to be assigned to project sites in Metro Manila and Bulacan', 'With COSH training is an advantage'],
        ],
        'electrical' => [
            'match' => '/electrical/i',
            'intro' => 'We are hiring a %s to keep our facilities powered, safe and efficient.',
            'resp' => ['Perform preventive and corrective maintenance on electrical systems', 'Troubleshoot motors, panels, lighting and control circuits', 'Prepare electrical layouts and load schedules', 'Ensure installations comply with the Philippine Electrical Code', 'Maintain maintenance logs and spare parts inventory', 'Respond to breakdowns and coordinate with contractors'],
            'qual' => ['Graduate of BS Electrical Engineering or related course', 'Registered Electrical Engineer or RME is an advantage', 'Knowledge of PEC and electrical safety practices', 'Willing to work on shifting schedules', 'Can read and interpret electrical plans'],
        ],
        'health' => [
            'match' => '/medical|clinic|health|wellness|psychometrician|therapist/i',
            'intro' => 'Our clinic team is looking for a caring and organized %s.',
            'resp' => ['Welcome patients, schedule appointments and manage the front desk', 'Maintain accurate and confidential patient records', 'Coordinate with doctors, nurses and HMO providers', 'Process billing, HMO approvals and official receipts', 'Assist in health and wellness programs for employees', 'Administer and score psychological tests (for psychometrician roles)'],
            'qual' => ['Graduate of Psychology, Nursing, Health Sciences or related course', 'Knowledge of data privacy in handling patient information', 'Warm, patient and professional with people', 'Proficient in Microsoft Office', 'Registered Psychometrician for psychometrician roles'],
        ],
        'social' => [
            'match' => '/case worker|social|community|welfare|program coordinator/i',
            'intro' => 'Help us serve families and communities as our new %s.',
            'resp' => ['Conduct intake interviews, home visits and case assessments', 'Develop and monitor case management plans', 'Coordinate referrals to partner agencies and LGUs', 'Organize community programs, trainings and outreach activities', 'Prepare case notes, reports and documentation', 'Advocate for the rights and welfare of clients'],
            'qual' => ['Graduate of BS Social Work, Psychology, Public Administration or related course', 'Registered Social Worker for case management roles', 'Compassionate, discreet and good at building trust', 'Willing to do field work within Valenzuela and nearby cities', 'Good report-writing skills'],
        ],
        'admin' => [
            'match' => '/administrative|records|policy|public affairs|research|translator|executive/i',
            'intro' => 'We are looking for an organized and reliable %s to keep our office running smoothly.',
            'resp' => ['Manage incoming and outgoing documents and correspondence', 'Maintain physical and digital records and filing systems', 'Prepare reports, memos and meeting minutes', 'Coordinate schedules, meetings and events', 'Assist in research, data gathering and policy documentation', 'Serve as point of contact for clients and partner offices'],
            'qual' => ['Graduate of Public Administration, Business Administration or any related course', 'Civil Service eligibility is an advantage', 'Excellent written communication skills', 'Proficient in Microsoft Word, Excel and Google Workspace', 'Organized, discreet and able to multitask'],
        ],
    ];

    public const NOTICE_EVENTS = [
        ['PLV Grand Alumni Homecoming 2026', 'PLV Grounds', 'Come home to PLV! Reconnect with batchmates, former professors and friends at our Grand Alumni Homecoming.', ['Opening program and recognition of outstanding alumni', 'Batch photo booths and alumni market', 'Live band and raffle prizes', 'Dinner and fellowship']],
        ['PLV Career Fair 2026', 'COED Assembly Hall', 'Meet more than 30 partner companies hiring PLV graduates for full-time, part-time and internship roles.', ['On-the-spot interviews', 'Free resume review booth', 'Company talks from partner employers', 'Bring printed copies of your resume']],
        ['Alumni Fun Run for a Cause', 'PLV Grounds', 'Lace up for our 5K and 10K fun run. Proceeds go to the PLV Alumni Scholarship Fund.', ['Assembly at 5:00 AM, gun start at 5:30 AM', 'Race kit includes singlet, bib and finisher medal', 'Free breakfast for all runners']],
        ['Alumni Christmas Gathering', 'COED Assembly Hall', 'Celebrate the season with fellow alumni — food, games, a gift exchange and a short thanksgiving program.', ['Bring a gift worth at least ₱300 for the exchange', 'Dress code: festive attire', 'Raffle and parlor games']],
        ['Tree Planting Drive at Tagalag Fishing Village', 'PLV Grounds', 'Join the PLV Alumni Association and the City Environment Office in planting 500 mangrove seedlings.', ['Assembly at PLV Grounds at 6:00 AM, shuttle provided', 'Wear comfortable clothes and closed shoes', 'Snacks and certificates of participation provided']],
        ['Alumni Sportsfest 2026', 'PLV Grounds', 'Represent your college in basketball, volleyball, badminton and chess at the annual Alumni Sportsfest.', ['Team registration closes one week before the event', 'Jerseys will be provided for registered teams', 'Champions receive trophies and cash prizes']],
        ['Recognition Night: Outstanding Alumni Awards', 'COED Assembly Hall', 'An evening honoring alumni who have made a mark in their profession and in their communities.', ['Formal attire', 'Awarding of the Gawad Pamantasan', 'Dinner and fellowship']],
        ['Blood Donation Drive', 'COED Assembly Hall', 'Give the gift of life. In partnership with the Philippine Red Cross – Valenzuela Chapter.', ['Donors must be 18–65 years old and weigh at least 50 kg', 'Have a full meal and enough sleep before donating', 'Free snacks and donor card']],
        ['Alumni Mentorship Mixer', 'COED Assembly Hall', 'Seasoned alumni meet graduating students for an afternoon of career conversations and mentoring.', ['Speed-mentoring by industry', 'Networking with snacks', 'Sign up as a mentor or mentee at the registration desk']],
        ['Batch 2016 10th Anniversary Reunion', 'COED Assembly Hall', 'Ten years since we marched! Batch 2016, let’s celebrate a decade of memories together.', ['Program, dinner and a slideshow of batch photos', 'Wear your old org shirt', 'Pre-registration is required']],
        ['PLV Foundation Day Celebration', 'PLV Grounds', 'Celebrate the founding anniversary of Pamantasan ng Lungsod ng Valenzuela with the whole PLV community.', ['Morning mass and parade', 'Booths from student and alumni organizations', 'Evening concert']],
        ['Community Outreach: Gift Giving at Bahay Kalinga', 'PLV Grounds', 'Spend a morning with children and elderly residents of Bahay Kalinga through games, storytelling and gift giving.', ['Assembly at PLV Grounds, transportation provided', 'Donations of school supplies are welcome', 'Volunteers will receive certificates']],
        ['Job Fair Orientation for Fresh Graduates', 'COED Assembly Hall', 'A short orientation for graduating students and fresh graduates before the city-wide job fair.', ['What employers look for', 'How to prepare your requirements', 'Q&A with the Public Employment Service Office']],
    ];

    public const NOTICE_SEMINARS = [
        ['Resume Writing and Interview Skills', 'Ms. Andrea Villanueva, Talent Acquisition Lead', 'Writing a resume that gets you the interview — and answering the questions that land the job', 'Learn how recruiters actually read resumes and how to prepare for the most common interview questions.'],
        ['Financial Literacy for Young Professionals', 'Mr. Paolo Mercado, CFP', 'Budgeting, saving and investing on a first salary', 'Make your first paycheck work for you — budgeting, emergency funds, and an introduction to MP2, UITFs and stocks.'],
        ['Passing the Licensure Examination', 'Prof. Gilbert Tolentino, LPT', 'Study strategies and time management for board exam takers', 'Practical review strategies from reviewers and recent topnotchers for LET, CPA, engineering and psychometrician exams.'],
        ['Introduction to Data Analytics', 'Engr. Kristine Lim', 'From spreadsheets to dashboards: data skills every graduate needs', 'A hands-on introduction to cleaning data, building pivot tables and telling stories with charts.'],
        ['Mental Health in the Workplace', 'Dr. Camille Navarro, RPsy', 'Recognizing burnout and building healthy work habits', 'Understand stress, burnout and how to ask for help — plus how to support colleagues who are struggling.'],
        ['Freelancing 101', 'Mr. Jerome Santos, Upwork Top Rated freelancer', 'Starting and growing a freelance career from the Philippines', 'How to build a portfolio, find your first clients, price your services and stay compliant with BIR requirements.'],
        ['Personal Branding on LinkedIn', 'Ms. Nicole Ramos, HR Business Partner', 'Building a professional online presence that attracts opportunities', 'Optimize your LinkedIn profile, write posts that show your expertise and grow a meaningful professional network.'],
        ['Career Shifting: Is It Right for You?', 'Mr. Ramon Castillo, Career Coach', 'How to evaluate, plan and succeed in a career change', 'Stories and frameworks from alumni who successfully moved into a new field, and what they wish they had known.'],
        ['Entrepreneurship Bootcamp', 'Ms. Patricia Gomez, Founder, Likha Crafts PH', 'From idea to registered business in Valenzuela', 'Business registration, DTI and Mayor’s permit requirements, simple bookkeeping, and pitching to funders.'],
        ['Effective Workplace Communication', 'Prof. Emmanuel De Leon', 'Writing emails, running meetings and giving feedback', 'Practical tips for clear emails, productive meetings, and giving and receiving feedback professionally.'],
    ];

    public const NOTICE_ANNOUNCEMENTS = [
        ['Alumni ID Release Schedule', '<p>Alumni IDs marked <strong>Ready to Claim</strong> may now be picked up at the <strong>University Registrar</strong>, Monday to Friday, 8:00 AM to 4:00 PM.</p><p>Please bring one valid government-issued ID. Authorized representatives must present an authorization letter and a photocopy of the alumnus’s ID.</p>'],
        ['Yearbook Distribution Now Ongoing', '<p>Printed yearbooks are now available for claiming at the <strong>Student Building, Office of Student Affairs – 2nd Floor</strong>.</p><p>Check your yearbook status on your PLV-AlumNet dashboard before going. Claiming hours: Monday to Friday, 9:00 AM to 4:00 PM.</p>'],
        ['Updated Office Hours of the Alumni Affairs Office', '<p>Starting next month, the Alumni Affairs Office will be open <strong>Monday to Friday, 8:00 AM to 5:00 PM</strong>, including lunch break.</p><p>For concerns outside office hours, you may message us through the PLV-AlumNet chatbot.</p>'],
        ['Call for Alumni Testimonials', '<p>We would love to hear your story! Share how PLV prepared you for your career by submitting a testimonial through your PLV-AlumNet profile.</p><p>Selected testimonials will be featured on the PLV-AlumNet home page and in the university’s social media pages.</p>'],
        ['Scheduled System Maintenance', '<p>PLV-AlumNet will be unavailable on <strong>Saturday from 10:00 PM to Sunday 2:00 AM</strong> for scheduled maintenance and security updates.</p><p>We apologize for the inconvenience.</p>'],
        ['Graduate Tracer Study 2026', '<p>Help us improve our programs by answering the Graduate Tracer Study. The survey takes about 10 minutes and all responses are kept confidential.</p><ul><li>Open to all PLV graduates</li><li>Deadline: end of the month</li></ul>'],
        ['Reminder: Keep Your Profile Updated', '<p>Employers use PLV-AlumNet to find qualified graduates. Keep your employment status, skills and resume up to date to receive better job matches.</p>'],
        ['Data Privacy Notice', '<p>PLV-AlumNet collects and processes personal information in accordance with the <strong>Data Privacy Act of 2012 (RA 10173)</strong>. You may review, update or request deletion of your data through your profile or by contacting the Alumni Affairs Office.</p>'],
    ];

    public const TESTIMONIALS = [
        'PLV gave me more than a diploma — it gave me the confidence to stand in front of a class on my very first day. Forever grateful to my professors in %s.',
        'I landed my first job three weeks after graduation through a posting on PLV-AlumNet. The skills I learned in %s were exactly what my employer was looking for.',
        'The OJT program pushed me out of my comfort zone. Today I am working in the same field, and I still use what I learned in my %s classes every day.',
        'Studying at PLV while working part-time was not easy, but the teachers understood and supported us. Proud to be a %s graduate!',
        'From thesis nights to board exam review, my PLV family never let me give up. Now a licensed professional — thank you, %s department!',
        'What I appreciate most is how practical our lessons were. In %s we built real projects, and those projects got me hired.',
        'PLV taught me that excellence is not about where you come from. To every Valenzuelano student: kaya natin ’to!',
        'The alumni network is real. A batchmate referred me to my current company, and now I am helping juniors get in too.',
        'Our professors in %s treated us like future colleagues, not just students. That respect stayed with me.',
        'I was a scholar of the city, and PLV made sure that opportunity counted. Now I give back by mentoring %s students.',
        'The seminars and career talks organized for alumni helped me switch careers smoothly. Salamat, PLV!',
        'Hindi ko makakalimutan ang mga gabing nag-aaral kami sa library. Worth it lahat — I am now working as a %s graduate in my dream company.',
    ];

    /** Alumni-to-alumni conversations: each is a list of lines; 'a' = starter, 'b' = other. */
    public const CONVERSATIONS = [
        [['a', 'Hi! Batchmate tayo sa PLV diba? Nakita kita sa directory.'], ['b', 'Uy oo! Kumusta ka na? Saan ka na nagwo-work?'], ['a', 'Okay naman, nasa %s na ako ngayon. Ikaw?'], ['b', 'Naghahanap pa rin ako ng stable na trabaho, pero may mga interviews ako this week.'], ['a', 'Good luck! Message mo lang ako kung may tanong ka sa interview, happy to help.'], ['b', 'Thank you so much! Will do.']],
        [['a', 'Hello! May hiring ba sa company niyo for %s?'], ['b', 'Meron, actually! Check mo yung posting namin sa job board ng AlumNet.'], ['a', 'Nakita ko na, nag-apply na ako kanina. Any tips?'], ['b', 'Ihanda mo yung portfolio mo, tsaka be ready sa practical exam.'], ['a', 'Noted! Salamat ng marami.']],
        [['a', 'Hi! Pupunta ka ba sa Alumni Homecoming?'], ['b', 'Oo naman! Sabay-sabay tayo ng barkada?'], ['a', 'Sige! Kita-kits sa PLV Grounds, 4 PM.'], ['b', 'G! Dala ako ng camera para may group picture tayo.']],
        [['a', 'Good morning! I saw your post about the review center. Is it still open for enrollment?'], ['b', 'Yes, until the end of the month. I can send you the details.'], ['a', 'Please do, thank you!'], ['b', 'Sent! Let me know if you have questions.']],
        [['a', 'Hi po! Junior niyo po ako sa %s. Pwede po ba magtanong about your work?'], ['b', 'Of course! Ano gusto mong malaman?'], ['a', 'Paano po kayo nakapasok sa current company niyo as fresh grad?'], ['b', 'Nag-apply ako sa AlumNet job board, then nagpractice ako ng interview with friends. Focus ka sa projects mo during college.'], ['a', 'Salamat po sa advice!']],
        [['a', 'Congrats sa board exam! Ang galing mo!'], ['b', 'Thank you!! Hindi ko pa rin ma-process haha'], ['a', 'Deserve mo yan. Celebrate tayo soon!'], ['b', 'Sige, libre ko na! 😄']],
        [['a', 'Hi! May group study ba kayo for the upcoming licensure exam?'], ['b', 'Meron, every Saturday sa library ng PLV. Join ka!'], ['a', 'Sige, sasama ako this Saturday.']],
        [['a', 'Hello! Nakita ko na pareho tayo ng company dati. Do you know if they are hiring again?'], ['b', 'I think yes, especially for operations. Try mo mag-message sa HR nila.'], ['a', 'Thanks for the heads up!']],
        [['a', 'Hi! I’m organizing a mentoring session for %s students. Would you like to be one of the speakers?'], ['b', 'I’d love to! When is it?'], ['a', 'Next month, Saturday morning. I’ll send the details.'], ['b', 'Great, count me in.']],
        [['a', 'Uy, natanggap mo na ba yung alumni ID mo?'], ['b', 'Ready to claim na daw sa AlumNet, kukunin ko sa Registrar bukas.'], ['a', 'Ako pending pa rin. Sige, update mo ako paano yung process.'], ['b', 'Sure, bring ka lang ng valid ID.']],
    ];

    /** Flagged messages, by reason (these trip the app's message filter). */
    public const FLAGGED = [
        'money_transfer' => ['Pwede ba magpadala ka muna ng ₱2,000 sa GCash ko? Babayaran ko next week promise.', 'Send me 5,000 via GCash para ma-process yung application mo, guaranteed hired ka.', 'Paki-transfer na lang sa bank account ko yung processing fee.'],
        'personal_info' => ['Ito number ko 0917 123 4567, text mo na lang ako dito para mabilis.', 'Pakisend ng picture ng valid ID mo at TIN para ma-refer kita.', 'My home address is 23 Mabini St., Malinta, puntahan mo na lang ako.'],
        'external_link' => ['Check mo tong link para sa mabilis na job offer: bit.ly/quick-job-offer', 'Register ka dito para sa free load: http://free-load-promo.example.net', 'Download mo tong app para kumita online: http://earn-fast.example.org'],
    ];

    /** Chatbot conversations: [question, ai answer, needs agent?, agent reply]. */
    public const CHAT_ALUMNI = [
        ['How do I claim my alumni ID?', 'Once your Alumni ID status shows "Ready to Claim", you can pick it up at the University Registrar, Monday to Friday, 8:00 AM to 4:00 PM. Please bring one valid ID.', false, null],
        ['When will the yearbook be released?', 'You can check your yearbook status on your dashboard. When it says "Ready to Claim", it can be picked up at the Student Building, Office of Student Affairs – 2nd Floor.', false, null],
        ['Why was my job application declined?', 'Employers decide based on their own requirements. You can view other matching jobs in the Job Board and keep your resume updated to improve your matches.', true, 'Hi! I checked your application — the employer filled the position with an applicant who had more field experience. I recommend applying to the two similar postings currently open. Good luck!'],
        ['Can I change the email address of my account?', 'You can update your email from your profile page by clicking Edit Profile.', true, 'Hello! I have updated your email for you. Please use your new email the next time you log in.'],
        ['I forgot my password', 'Click "Forgot Password" on the login page and follow the link sent to your email.', false, null],
        ['How can I make my profile appear in more job matches?', 'Complete your resume in the Resume Builder — add a summary, skills, experiences and certifications. Profiles with a complete resume get better matches.', false, null],
        ['Is the alumni ID free?', 'I’m not sure about that. Let me connect you with a staff member.', true, 'Hi! The Alumni ID is not free. It has a fee of ₱1,200, and replacement IDs cost the same ₱1,200. You can pay at the Cashier.'],
        ['Paano po mag-apply sa job posting?', 'Pumunta sa Job Board, piliin ang job, at i-click ang "Apply". Pipili ka kung anong resume ang gagamitin bago i-submit.', false, null],
        ['My yearbook status still says pending even though I already paid.', 'Let me connect you with a staff member who can check your record.', true, 'Hi! Your payment has been verified and your yearbook status is now updated. Thank you for your patience!'],
    ];

    public const CHAT_EMPLOYER = [
        ['How long does employer account approval take?', 'Employer accounts are usually reviewed within 1–3 working days after registration.', false, null],
        ['How do I post a job?', 'Go to the Job Board and click "Post a New Job". Fill in the details and submit — staff will review it before it goes live.', false, null],
        ['Can I edit a job post after it was approved?', 'Yes, you can edit your posting from "My Job Postings". Changes to key details may need to be reviewed again.', false, null],
        ['Can I download the list of my applicants?', 'I’m not sure about that. Let me connect you with a staff member.', true, 'Hi! Yes — open "My Job Postings" and use the Export CSV button to download your applicants list.'],
        ['Our job post was declined. What should we change?', 'Let me connect you with a staff member who can explain the review.', true, 'Hello! Your posting was declined because the salary range and company address were missing. Please add them and resubmit.'],
    ];

    public const REVIEWS = [
        5 => ['Great company to start your career. Supportive team leads and clear training.', 'Very welcoming to fresh graduates. Learned a lot in my first year.', 'Excellent work culture and benefits. Highly recommended!'],
        4 => ['Good place to grow, though workload can get heavy during peak season.', 'Nice people and fair compensation. Processes could be more organized.', 'Solid training program. Some approvals take a while.'],
        3 => ['Okay experience overall. Good for learning, but salary is just average.', 'Mixed experience — depends a lot on your department.'],
        2 => ['Long hours and slow salary release. Team was nice though.'],
        1 => ['Contract was different from what was discussed during the interview.'],
    ];
}
