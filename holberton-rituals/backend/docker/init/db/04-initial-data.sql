-- 04-initial-data.sql
-- Initial test data reflecting real Holberton School usage
-- Version: 1.0.0
-- Made with Love & Passion by: Fassih & Zoé
-- Date: 2024-02-11

-- =============================
-- Admin & Staff
-- =============================

-- Admin with password: 'admin123'
INSERT INTO users (email, password_hash, role, first_name, last_name, is_active) VALUES 
('admin@holberton.fr', '$2y$10$GkwCn6VdRrFN5BP9qYoLUeK/H9bHRHQTnHB5H5YZyVf8h4oLf7P2y', 'ADMIN', 'Admin', 'Holberton', TRUE);

-- Staff (SWE) with password: 'staff123'
INSERT INTO users (email, password_hash, role, first_name, last_name, is_active) VALUES 
('swe@holberton.fr', '$2y$10$NqmFn.HpwXVXmQX3LcGbW.zA4Uu4Ly90Zq9o3fQJhBEXrDFEJpIW2', 'STAFF', 'Sophie', 'Engineer', TRUE);

-- =============================
-- Cohorts
-- =============================

-- Fundamentals Cohorts
INSERT INTO cohorts (name, curriculum_type, start_date, end_date, slack_channel, is_active) VALUES 
('C#23', 'fundamentals', '2023-09-01', '2024-06-30', 'C23-FR', TRUE),
('C#24', 'fundamentals', '2024-01-01', '2024-09-30', 'C24-FR', TRUE),
('C#25', 'fundamentals', '2024-03-01', '2024-12-31', 'C25-FR', TRUE);

-- Specialization Cohort
INSERT INTO cohorts (name, curriculum_type, start_date, end_date, slack_channel, is_active) VALUES 
('SP#02', 'specialization', '2024-01-01', '2024-09-30', 'SP02-FR', TRUE);

-- =============================
-- C#23 Students (18 students)
-- =============================

-- Helper function (comme avant)
DELIMITER //

CREATE PROCEDURE create_test_student(
    IN p_email VARCHAR(255),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_cohort_name VARCHAR(50)
)
BEGIN
    DECLARE v_user_id CHAR(36);
    DECLARE v_cohort_id CHAR(36);
    DECLARE v_curriculum_type VARCHAR(20);
    
    -- Get cohort info
    SELECT id, curriculum_type INTO v_cohort_id, v_curriculum_type
    FROM cohorts 
    WHERE name = p_cohort_name;
    
    -- Create user
    SET v_user_id = UUID();
    INSERT INTO users (
        id, email, password_hash, role, first_name, last_name, 
        preferred_language, is_active
    ) VALUES (
        v_user_id,
        p_email,
        '$2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewFpcr/kLEL.ecP2', -- 'password123'
        'STUDENT',
        p_first_name,
        p_last_name,
        'fr',
        TRUE
    );
    
    -- Create student
    INSERT INTO students (
        id, user_id, current_cohort_id, curriculum_type,
        sod_count, standup_count, is_active
    ) VALUES (
        UUID(),
        v_user_id,
        v_cohort_id,
        v_curriculum_type,
        0, 0, TRUE
    );
END //

DELIMITER ;

-- C#23 Students
CALL create_test_student('michael.jackson@holberton.fr', 'Michael', 'Jackson', 'C#23');
CALL create_test_student('madonna.ciccone@holberton.fr', 'Madonna', 'Ciccone', 'C#23');
CALL create_test_student('prince.rogers@holberton.fr', 'Prince', 'Rogers', 'C#23');
CALL create_test_student('whitney.houston@holberton.fr', 'Whitney', 'Houston', 'C#23');
CALL create_test_student('george.michael@holberton.fr', 'George', 'Michael', 'C#23');
CALL create_test_student('cyndi.lauper@holberton.fr', 'Cyndi', 'Lauper', 'C#23');
CALL create_test_student('david.bowie@holberton.fr', 'David', 'Bowie', 'C#23');
CALL create_test_student('tina.turner@holberton.fr', 'Tina', 'Turner', 'C#23');
CALL create_test_student('phil.collins@holberton.fr', 'Phil', 'Collins', 'C#23');
CALL create_test_student('annie.lennox@holberton.fr', 'Annie', 'Lennox', 'C#23');
CALL create_test_student('billy.idol@holberton.fr', 'Billy', 'Idol', 'C#23');
CALL create_test_student('debbie.harry@holberton.fr', 'Debbie', 'Harry', 'C#23');
CALL create_test_student('robert.palmer@holberton.fr', 'Robert', 'Palmer', 'C#23');
CALL create_test_student('kate.bush@holberton.fr', 'Kate', 'Bush', 'C#23');
CALL create_test_student('simon.lebon@holberton.fr', 'Simon', 'LeBon', 'C#23');
CALL create_test_student('boy.george@holberton.fr', 'Boy', 'George', 'C#23');
CALL create_test_student('rick.astley@holberton.fr', 'Rick', 'Astley', 'C#23');
CALL create_test_student('pat.benatar@holberton.fr', 'Pat', 'Benatar', 'C#23');

-- =============================
-- C#24 Students (18 students)
-- =============================

CALL create_test_student('britney.spears@holberton.fr', 'Britney', 'Spears', 'C#24');
CALL create_test_student('justin.timberlake@holberton.fr', 'Justin', 'Timberlake', 'C#24');
CALL create_test_student('christina.aguilera@holberton.fr', 'Christina', 'Aguilera', 'C#24');
CALL create_test_student('eminem.mathers@holberton.fr', 'Marshall', 'Mathers', 'C#24');
CALL create_test_student('robbie.williams@holberton.fr', 'Robbie', 'Williams', 'C#24');
CALL create_test_student('gwen.stefani@holberton.fr', 'Gwen', 'Stefani', 'C#24');
CALL create_test_student('pink.moore@holberton.fr', 'Alecia', 'Moore', 'C#24');
CALL create_test_student('usher.raymond@holberton.fr', 'Usher', 'Raymond', 'C#24');
CALL create_test_student('avril.lavigne@holberton.fr', 'Avril', 'Lavigne', 'C#24');
CALL create_test_student('alicia.keys@holberton.fr', 'Alicia', 'Keys', 'C#24');
CALL create_test_student('beth.gibbons@holberton.fr', 'Beth', 'Gibbons', 'C#24');
CALL create_test_student('enrique.iglesias@holberton.fr', 'Enrique', 'Iglesias', 'C#24');
CALL create_test_student('shakira.ripoll@holberton.fr', 'Shakira', 'Ripoll', 'C#24');
CALL create_test_student('ricky.martin@holberton.fr', 'Ricky', 'Martin', 'C#24');
CALL create_test_student('jennifer.lopez@holberton.fr', 'Jennifer', 'Lopez', 'C#24');
CALL create_test_student('craig.david@holberton.fr', 'Craig', 'David', 'C#24');
CALL create_test_student('kylie.minogue@holberton.fr', 'Kylie', 'Minogue', 'C#24');
CALL create_test_student('mya.harrison@holberton.fr', 'Mya', 'Harrison', 'C#24');

-- =============================
-- C#25 Students (18 students)
-- =============================

CALL create_test_student('amy.winehouse@holberton.fr', 'Amy', 'Winehouse', 'C#25');
CALL create_test_student('lady.gaga@holberton.fr', 'Lady', 'Gaga', 'C#25');
CALL create_test_student('katy.perry@holberton.fr', 'Katy', 'Perry', 'C#25');
CALL create_test_student('rihanna.fenty@holberton.fr', 'Rihanna', 'Fenty', 'C#25');
CALL create_test_student('bruno.mars@holberton.fr', 'Bruno', 'Mars', 'C#25');
CALL create_test_student('adele.adkins@holberton.fr', 'Adele', 'Adkins', 'C#25');
CALL create_test_student('taylor.swift@holberton.fr', 'Taylor', 'Swift', 'C#25');
CALL create_test_student('beyonce.knowles@holberton.fr', 'Beyoncé', 'Knowles', 'C#25');
CALL create_test_student('adam.levine@holberton.fr', 'Adam', 'Levine', 'C#25');
CALL create_test_student('fergie.ferguson@holberton.fr', 'Fergie', 'Ferguson', 'C#25');
CALL create_test_student('norah.jones@holberton.fr', 'Norah', 'Jones', 'C#25');
CALL create_test_student('kelly.clarkson@holberton.fr', 'Kelly', 'Clarkson', 'C#25');
CALL create_test_student('james.blunt@holberton.fr', 'James', 'Blunt', 'C#25');
CALL create_test_student('nelly.furtado@holberton.fr', 'Nelly', 'Furtado', 'C#25');
CALL create_test_student('kesha.sebert@holberton.fr', 'Kesha', 'Sebert', 'C#25');
CALL create_test_student('jason.mraz@holberton.fr', 'Jason', 'Mraz', 'C#25');
CALL create_test_student('leona.lewis@holberton.fr', 'Leona', 'Lewis', 'C#25');
CALL create_test_student('paolo.nutini@holberton.fr', 'Paolo', 'Nutini', 'C#25');

-- =============================
-- SP#02 Students (6 students)
-- =============================

CALL create_test_student('ed.sheeran@holberton.fr', 'Ed', 'Sheeran', 'SP#02');
CALL create_test_student('sam.smith@holberton.fr', 'Sam', 'Smith', 'SP#02');
CALL create_test_student('dua.lipa@holberton.fr', 'Dua', 'Lipa', 'SP#02');
CALL create_test_student('billie.eilish@holberton.fr', 'Billie', 'Eilish', 'SP#02');
CALL create_test_student('post.malone@holberton.fr', 'Post', 'Malone', 'SP#02');
CALL create_test_student('lewis.capaldi@holberton.fr', 'Lewis', 'Capaldi', 'SP#02');

-- Clean up
DROP PROCEDURE create_test_student;

/*
Notes:
- All passwords are pre-hashed using bcrypt
- Admin password: 'admin123'
- Staff password: 'staff123'
- All students password: 'password123'
- NEVER use these credentials in production!
*/