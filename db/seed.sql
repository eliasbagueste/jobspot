-- db/seed.sql
-- =========================================================
-- FULL TEST DATA FOR JOBSPOT
-- =========================================================
-- Password for ALL users: Test1234
--
-- USERS:
--   admin@jobspot.local           → Admin
--   atlantic@jobspot.local        → Company (Atlantic Digital - verified)
--   restaurante@jobspot.local     → Company (El Rincón Group - verified)
--   corrib@jobspot.local          → Company (Corrib Construction - verified)
--   learning@jobspot.local        → Company (Galway Learning Centre - verified)
--   sinverificar@jobspot.local    → Company (StartupXYZ - NOT verified, no jobs)
--   ana.garcia@jobspot.local      → Candidate
--   carlos.lopez@jobspot.local    → Candidate
--   maria.martinez@jobspot.local  → Candidate
--   pedro.sanchez@jobspot.local   → Candidate
--   lucia.fernandez@jobspot.local → Candidate
--   david.romero@jobspot.local    → Candidate
--
-- JOBS: 10 (published and closed only)
-- APPLICATIONS: 17 (sent, reviewed, accepted, rejected)
--
-- WARNING: Deletes ALL existing data.
-- Never run this in production.
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE favorite_jobs;
TRUNCATE TABLE applications;
TRUNCATE TABLE jobs;
TRUNCATE TABLE companies;
TRUNCATE TABLE candidate_profiles;
TRUNCATE TABLE categories;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- 1. USERS  (IDs 1-12)
-- =========================================================
-- password_hash('Test1234', PASSWORD_BCRYPT)

INSERT INTO users (id, full_name, email, password_hash, role, is_active) VALUES
(1,  'Administrador',          'admin@jobspot.local',           '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'admin',     1),
(2,  'Atlantic Digital',       'atlantic@jobspot.local',        '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(3,  'Grupo El Rincón',        'restaurante@jobspot.local',     '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(4,  'Corrib Construction',    'corrib@jobspot.local',          '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(5,  'Galway Learning Centre', 'learning@jobspot.local',        '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(6,  'StartupXYZ',             'sinverificar@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'company',   1),
(7,  'Ana García',             'ana.garcia@jobspot.local',      '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(8,  'Carlos López',           'carlos.lopez@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(9,  'María Martínez',         'maria.martinez@jobspot.local',  '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(10, 'Pedro Sánchez',          'pedro.sanchez@jobspot.local',   '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(11, 'Lucía Fernández',        'lucia.fernandez@jobspot.local', '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1),
(12, 'David Romero',           'david.romero@jobspot.local',    '$2y$10$QK6iWfO37XsYzRnvD7o/2OVJo5W8of8FUSRRbzXgVd2S8CFtEOCMm', 'candidate', 1);


-- =========================================================
-- 2. CANDIDATE PROFILES
-- =========================================================

INSERT INTO candidate_profiles (user_id, phone, city, profile_summary) VALUES
(7,  '612 345 678', 'Latin Quarter (Galway)', 'Full-stack developer with 3 years of experience in PHP and Vue.js. Passionate about clean interface design and well-structured code.'),
(8,  '623 456 789', 'Salthill (Galway)',      'Hospitality professional with 4 years of experience in mid-to-upscale restaurants. Also previously trained as a technical draughtsman.'),
(9,  '634 567 890', 'Bohermore (Galway)',     'Waitress with extensive front-of-house and bar experience. English level B2, valid food handler''s certificate.'),
(10, '645 678 901', 'Knocknacarra (Galway)',  'UX/UI designer with a portfolio on Behance. Skilled in Figma and Adobe XD. Also worked as a construction labourer for two summers.'),
(11, '656 789 012', 'Newcastle (Galway)',     'Maths teacher with 5 years of experience in secondary education and tutoring academies. Degree in Mathematics from the University of Galway.'),
(12, '667 890 123', 'Mervue (Galway)',        'Chartered building surveyor with 6 years in residential construction and refurbishment. Experience in site management and team coordination.');


-- =========================================================
-- 3. CATEGORIES  (IDs 1-8)
-- =========================================================

INSERT INTO categories (id, name, slug, is_active) VALUES
(1, 'Technology',     'technology',     1),
(2, 'Hospitality',    'hospitality',    1),
(3, 'Administration',  'administration', 1),
(4, 'Retail',         'retail',         1),
(5, 'Construction',   'construction',   1),
(6, 'Education',      'education',      1),
(7, 'Healthcare',     'healthcare',     1),
(8, 'Transport',      'transport',      1);


-- =========================================================
-- 4. COMPANIES  (IDs 1-5)
-- =========================================================

INSERT INTO companies (id, owner_user_id, legal_name, brand_name, tax_id, location, description, is_verified) VALUES
(1, 2, 'Atlantic Digital Ltd.',       'Atlantic Digital',       'B12345678', 'Latin Quarter (Galway)', 'Technology company specialising in web development and digital solutions for SMEs. Team of 15 people spread across Galway, Dublin and London.', 1),
(2, 3, 'El Rincón Restaurant Group Ltd.', 'El Rincón',          'B23456789', 'Salthill (Galway)',      'Spanish restaurant with over 15 years in Galway. Mediterranean cuisine and signature tapas in the Salthill neighbourhood, one of the liveliest spots in the city.', 1),
(3, 4, 'Corrib Construction Ltd.',    'Corrib Construction',    'A34567890', 'Westside (Galway)',      'Construction company with 20 years of experience in civil works and residential building across Connacht and the west of Ireland.', 1),
(4, 5, 'Galway Learning Centre Ltd.', 'Galway Learning Centre', 'B45678901', 'Newcastle (Galway)',     'Centre for accredited and after-school tuition with over 800 students. Specialists in school support and state exam preparation.', 1),
(5, 6, 'StartupXYZ Ltd.',             'StartupXYZ',             NULL,        'Eyre Square (Galway)',  'Seed-stage tech startup building a sustainable urban mobility app.', 0);


-- =========================================================
-- 5. JOB LISTINGS  (IDs 1-10)
-- =========================================================
-- Only published and closed statuses, consistent with the current flow.
--
-- Atlantic Digital:       3 published + 1 closed
-- El Rincón:              2 published + 1 closed
-- Corrib Construction:    2 published
-- Galway Learning Centre: 1 published
-- StartupXYZ:             no jobs (unverified company)

INSERT INTO jobs (id, company_id, category_id, title, description, location, contract_type, workday, modality, salary_min, salary_max, status, published_at) VALUES

-- Atlantic Digital
(1, 1, 1, 'PHP Developer',
'We are looking for a PHP Developer with real experience in production projects to join our backend team.

RESPONSIBILITIES:
- Developing new features in PHP (Laravel 10/11) following SOLID principles
- Designing and implementing RESTful APIs consumed by React frontends
- Optimising SQL queries on MySQL and PostgreSQL databases
- Code reviews and pair programming
- Integration with Stripe, Redsys and third-party APIs
- Unit and integration testing with PHPUnit and Pest

REQUIREMENTS:
- At least 2 years of professional PHP experience
- Strong command of Laravel or Symfony
- Solid MySQL knowledge: indexes, relationships, transactions
- Familiarity with Git and agile methodologies

WHAT WE OFFER:
- Permanent contract
- 100% remote with weekly meetings
- Flexible hours
- €1,000/year training budget
- Choice of hardware: MacBook Pro M3 or ThinkPad with Linux',
'Latin Quarter (Galway)', 'permanent', 'full_time', 'remote', 26000.00, 34000.00, 'published', '2026-04-01 09:00:00'),

(2, 1, 1, 'Systems Administrator',
'Software development company looking for a Systems Administrator to strengthen its infrastructure department.

RESPONSIBILITIES:
- Administration of Linux servers (Debian/Ubuntu) and Windows Server
- Managing virtualisation environments with VMware and Proxmox
- Network configuration: VLANs, firewalls, OpenVPN and WireGuard VPN
- Administration of Apache, Nginx, MySQL, PostgreSQL, Redis
- Implementing CI/CD pipelines with GitLab CI and GitHub Actions
- Container management with Docker

REQUIREMENTS:
- Higher Vocational Training in Network Systems Administration (ASIR) or Computer Engineering
- At least 2 years administering Linux systems in production
- Networking knowledge: TCP/IP, DNS, DHCP, HTTP/S
- Scripting in Bash and/or Python

WHAT WE OFFER:
- Permanent contract
- Hybrid: 3 days remote, 2 days in the office
- 23 days of holidays
- Annual budget for training and certifications',
'Latin Quarter (Galway)', 'permanent', 'full_time', 'hybrid', 28000.00, 36000.00, 'published', '2026-04-05 10:00:00'),

(3, 1, 1, 'UX/UI Designer',
'We are looking for a UX/UI Designer to join our product team and improve the experience of our applications.

RESPONSIBILITIES:
- Designing interfaces for web and mobile applications with Figma
- Creating interactive prototypes and user flows
- Usability testing with real users
- Close collaboration with the frontend development team
- Maintaining and evolving the Design System

REQUIREMENTS:
- Portfolio with UX/UI projects (required)
- Strong command of Figma
- Knowledge of accessibility principles (WCAG)
- Basic HTML and CSS knowledge is a plus

WHAT WE OFFER:
- Permanent contract
- On-site work in the Latin Quarter
- Flexible hours
- Budget for design events and conferences',
'Latin Quarter (Galway)', 'permanent', 'full_time', 'onsite', 24000.00, 30000.00, 'published', '2026-04-10 11:00:00'),

(4, 1, 1, 'DevOps Engineer',
'This position has been filled.

We were looking for a DevOps Engineer to modernise our AWS infrastructure and automate our deployment processes.

REQUIREMENTS:
- Experience with AWS (EC2, RDS, S3, ECS)
- Terraform and Infrastructure as Code
- Kubernetes and Helm
- CI/CD pipelines

Thank you to everyone who applied.',
'Latin Quarter (Galway)', 'permanent', 'full_time', 'remote', 32000.00, 42000.00, 'closed', '2026-03-01 09:00:00'),

-- El Rincón
(5, 2, 2, 'Waiter/Waitress',
'We are looking to hire a Waiter/Waitress for our restaurant in the heart of Salthill.

RESPONSIBILITIES:
- Serving and advising customers throughout their visit
- Taking orders and managing them through the POS system
- Serving food and drinks at the table
- Preparing and maintaining the floor: mise en place
- Coordinating with the kitchen team

REQUIREMENTS:
- At least 1 year of experience in a similar role
- Basic English (French is a plus)
- Valid food handler''s certificate
- Ability to work as part of a team and under pressure

WHAT WE OFFER:
- Temporary contract with the possibility of becoming permanent
- Tips shared equally
- Staff meals included
- Two consecutive days off per week',
'Salthill (Galway)', 'temporary', 'full_time', 'onsite', 17000.00, 20000.00, 'published', '2026-04-08 09:00:00'),

(6, 2, 2, 'Barista — Specialty Coffee Shop',
'Specialty coffee shop looking for a Barista passionate about quality coffee.

RESPONSIBILITIES:
- Preparing espressos, cappuccinos, flat whites and the drinks menu
- Intermediate-level latte art (rosetta, tulip, heart)
- Adjusting grind and extraction settings by variety
- Personalised service and advice on origin and flavour profile
- Maintaining equipment: espresso machine, grinder, V60, Chemex

REQUIREMENTS:
- At least 6 months of experience as a barista or in hospitality
- Knowledge of extraction methods: espresso, filter, cold brew
- Proactive, detail-oriented attitude
- SCA training is a plus

WHAT WE OFFER:
- Part-time contract with possibility of extended hours
- Ongoing training provided by the company
- Staff discount on drinks
- Morning shift: 7:00 – 14:00',
'Salthill (Galway)', 'permanent', 'part_time', 'onsite', 15000.00, 18000.00, 'published', '2026-04-12 10:00:00'),

(7, 2, 2, 'Head Chef',
'This position has been filled internally.

We were looking for a Head Chef with proven experience in Mediterranean cuisine restaurants to lead our team of 6.',
'Salthill (Galway)', 'permanent', 'full_time', 'onsite', 28000.00, 35000.00, 'closed', '2026-03-15 09:00:00'),

-- Corrib Construction
(8, 3, 5, 'Building Surveyor',
'Construction company with 20 years of experience is looking for a chartered Building Surveyor for immediate start.

RESPONSIBILITIES:
- Overseeing execution of residential building works
- Quality control of materials and construction processes
- Coordinating subcontractors and suppliers on site
- Preparing monthly measurements and certifications

REQUIREMENTS:
- Degree in Building Surveying/Technical Architecture or Building Engineering
- Current professional accreditation (required)
- At least 3 years of experience in residential site management
- Category B driving licence
- Knowledge of AutoCAD and Presto

WHAT WE OFFER:
- Permanent contract
- Company vehicle for site travel
- 24 days of holidays
- Stable, fully-funded projects',
'Westside (Galway)', 'permanent', 'full_time', 'onsite', 30000.00, 38000.00, 'published', '2026-04-03 09:00:00'),

(9, 3, 5, 'Construction Labourer',
'Construction labourer needed for a site in Westside, immediate start.

RESPONSIBILITIES:
- General site support: loading and unloading materials, cleaning
- Using basic hand and power tools
- Assisting tradespeople with bricklaying and formwork
- Strict compliance with site safety regulations

REQUIREMENTS:
- No previous experience required (a plus if you have it)
- Category B driving licence (desirable)
- Immediate availability

WHAT WE OFFER:
- 3-month temporary contract with possibility of extension
- Salary per construction industry agreement
- Personal protective equipment provided by the company',
'Westside (Galway)', 'temporary', 'full_time', 'onsite', 16000.00, 19000.00, 'published', '2026-04-15 09:00:00'),

-- Galway Learning Centre
(10, 4, 6, 'Maths Teacher',
'School support academy looking for a Maths Teacher for in-person classes in Newcastle.

RESPONSIBILITIES:
- Teaching maths to secondary school, Leaving Cert and university students
- Personalised exam and entrance test preparation
- Tracking and reporting on each student''s progress

REQUIREMENTS:
- Degree in Mathematics, Physics or equivalent (required)
- At least 1 year of teaching experience
- Strong communication skills and patience
- Teaching qualification is a plus

WHAT WE OFFER:
- Permanent part-time contract (afternoons: 16:00 – 20:00)
- Possibility of more hours depending on demand
- Good working environment in an established team',
'Newcastle (Galway)', 'permanent', 'part_time', 'onsite', 14000.00, 18000.00, 'published', '2026-04-18 10:00:00');


-- =========================================================
-- 6. APPLICATIONS  (17 total, all statuses)
-- =========================================================
--
--  job | listing        | applications
--  ----+----------------+----------------------------------------------
--   1  | PHP            | Ana(reviewed), Carlos(rejected), Lucía(sent)
--   2  | Sysadmin       | Ana(sent), María(reviewed), David(accepted)
--   3  | UX/UI          | Ana(accepted), Pedro(rejected)
--   5  | Waiter         | Carlos(sent), María(accepted), David(rejected)
--   6  | Barista        | María(sent), Pedro(reviewed)
--   8  | Bldg Surveyor  | Carlos(sent), David(reviewed)
--   9  | Labourer       | Pedro(sent)
--  10  | Maths teacher  | Lucía(sent)
--  ----+----------------+----------------------------------------------
--  No applications: 4 (DevOps-closed), 7 (Head Chef-closed)

INSERT INTO applications (job_id, candidate_user_id, status, message, applied_at) VALUES

-- Ana García → PHP (reviewed)
(1, 7, 'reviewed',
'Hi, my name is Ana García and I''ve spent the last 3 years developing with PHP and Laravel at an agency in the Latin Quarter. I''ve worked with MySQL, REST APIs and Vue.js on the frontend. I''m looking for a more technical project where I can keep growing. I have a GitHub portfolio with several of my own projects.',
'2026-04-10 10:30:00'),

-- Ana García → Sysadmin (sent)
(2, 7, 'sent',
'Good morning, although my background is mainly in development, I have training in Linux systems administration and have managed the servers for the projects I''ve worked on. I''m very interested in this position.',
'2026-04-15 09:15:00'),

-- Ana García → UX/UI (accepted)
(3, 7, 'accepted',
'I''m a developer with a strong interest in interface design. I use Figma daily to build prototypes before implementation. Here''s a link to my portfolio: behance.net/anagarcia.',
'2026-04-12 11:00:00'),

-- Carlos López → PHP (rejected)
(1, 8, 'rejected',
'Hi, I''m Carlos López. I have a year of experience with vanilla PHP and some CodeIgniter. I''m currently learning Laravel on my own and would like to move to a bigger team.',
'2026-04-11 16:45:00'),

-- Carlos López → Waiter (sent)
(5, 8, 'sent',
'Good afternoon, I have 4 years of experience in mid-to-upscale restaurants in Galway. I''m used to working the floor with high customer volume and hold a valid food handler''s certificate.',
'2026-04-17 12:00:00'),

-- Carlos López → Building Surveyor (sent)
(8, 8, 'sent',
'Although my main background is in hospitality, I trained as a technical draughtsman and worked two summers as a site assistant. I''d like to get back into that field.',
'2026-04-20 10:30:00'),

-- María Martínez → Sysadmin (reviewed)
(2, 9, 'reviewed',
'Good morning, I''m María Martínez. I have 2 years of experience administering Linux servers at a logistics company in Galway. I manage Ubuntu Server, Apache and MySQL environments. I''m currently learning Docker and hold the LPIC-1.',
'2026-04-08 09:00:00'),

-- María Martínez → Waiter (accepted)
(5, 9, 'accepted',
'Hi, I''ve spent 3 years working front-of-house in various restaurants in Galway and Dublin. My English is B2 level, which has let me serve international customers. I''m looking for stability in Galway.',
'2026-04-18 14:00:00'),

-- María Martínez → Barista (sent)
(6, 9, 'sent',
'I love specialty coffee and have been training myself for a while. I have experience with semi-professional espresso machines and know the most common filter methods.',
'2026-04-21 11:30:00'),

-- Pedro Sánchez → UX/UI (rejected)
(3, 10, 'rejected',
'I''m a graphic designer with an interest in UX. I have experience with Photoshop and Illustrator but have only been learning Figma for 6 months. My portfolio includes redesigns of well-known apps as practice exercises.',
'2026-04-13 17:00:00'),

-- Pedro Sánchez → Barista (reviewed)
(6, 10, 'reviewed',
'Hi, I''ve been working as a barista in a Galway coffee shop for 2 years. I use a La Cimbali espresso machine and have done a basic latte art course. I''m interested in working in a more professional environment.',
'2026-04-16 10:00:00'),

-- Pedro Sánchez → Labourer (sent)
(9, 10, 'sent',
'Good morning, I''m available for immediate start. I worked as a labourer on two sites during the summers of 2024 and 2025. I hold a category B driving licence and am used to working outdoors.',
'2026-04-22 08:30:00'),

-- Lucía Fernández → PHP (sent)
(1, 11, 'sent',
'My name is Lucía Fernández, I''m a maths teacher but have been learning web development on the side. I have knowledge of PHP, JavaScript and some Laravel through courses and personal projects.',
'2026-04-19 09:45:00'),

-- Lucía Fernández → Maths Teacher (sent)
(10, 11, 'sent',
'Hi, I''m Lucía Fernández, a maths teacher with 5 years of experience in tutoring academies and secondary education. I hold a Degree in Mathematics from the University of Galway and a teaching qualification. I''m looking for stability in Galway.',
'2026-04-19 16:00:00'),

-- David Romero → Sysadmin (accepted)
(2, 12, 'accepted',
'Good morning, I''m David Romero. I''ve spent 4 years managing my current company''s IT infrastructure: Linux servers, VPN, backups and monitoring with Zabbix. I''m looking for a full-time position dedicated to systems.',
'2026-04-07 10:00:00'),

-- David Romero → Building Surveyor (reviewed)
(8, 12, 'reviewed',
'I''m David Romero, a chartered building surveyor with 6 years of experience in residential construction and refurbishment. I''ve managed projects of up to 30 homes as site manager. I''m proficient with AutoCAD and Presto.',
'2026-04-14 11:30:00'),

-- David Romero → Waiter (rejected)
(5, 12, 'rejected',
'Hi, I know my background doesn''t quite fit what you''re looking for, but I spent a season working front-of-house a few years ago and would like to return to it while I look for work in my field.',
'2026-04-20 18:00:00');
