-- 01-enums.sql (Partie 1)
-- Bilingual enumeration definitions for Holberton Rituals
-- Version: 2.0.0
-- Made with Love & Passion by: Fassih & Zoé
-- Date: 2024-02-11

-- =============================
-- Enumeration Tables
-- =============================

-- Curriculum types
CREATE TABLE IF NOT EXISTS enum_curriculum_type (
    value VARCHAR(20) PRIMARY KEY,
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    duration_months INT NOT NULL,
    ritual_frequency_sod INT NOT NULL,
    ritual_frequency_standup INT NOT NULL,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_curriculum_type (value, description_fr, description_en, display_order, duration_months, ritual_frequency_sod, ritual_frequency_standup, emoji) VALUES 
    ('fundamentals', 'Formation fondamentale de 9 mois', 'Fundamentals - 9 months program', 1, 9, 3, 4, '[F]'),
    ('specialization', 'Spécialisation avancée de 9 mois', 'Advanced specialization - 9 months program', 2, 9, 1, 4, '[S]'),
    ('apprenticeship', 'Spécialisation alternance avancée de 24 mois', 'Advanced apprenticeship - 24 months program', 3, 24, 1, 1, '[A]');

-- Ritual types
CREATE TABLE IF NOT EXISTS enum_ritual_type (
    value VARCHAR(20) PRIMARY KEY,
    display_name_fr VARCHAR(30),
    display_name_en VARCHAR(30),
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    duration_minutes INT NOT NULL,
    time_slot TIME NOT NULL,
    requires_feedback BOOLEAN DEFAULT FALSE,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_ritual_type (value, display_name_fr, display_name_en, description_fr, description_en, display_order, duration_minutes, time_slot, requires_feedback, emoji) VALUES 
    ('sod', 'SOD', 'SOD', 'Speaker Of the Day (aka Share Or Die) - Présentation technique', 'Speaker Of the Day (aka Share Or Die) - Technical presentation', 1, 15, '11:30:00', TRUE, '[SOD]'),
    ('standup', 'Stand-up', 'Stand-up', 'Point quotidien de la cohorte', 'Daily cohort meeting', 2, 20, '11:45:00', FALSE, '[SU]');

-- User roles
CREATE TABLE IF NOT EXISTS enum_user_role (
    value VARCHAR(20) PRIMARY KEY,
    display_name_fr VARCHAR(30),
    display_name_en VARCHAR(30),
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    can_be_revoked BOOLEAN DEFAULT TRUE,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_user_role (value, display_name_fr, display_name_en, description_fr, description_en, display_order, can_be_revoked, emoji) VALUES 
    ('SUPER_ADMIN', 'Super Administrateur', 'Super Administrator', 'Contrôle total du système avec droits exclusifs', 'Full system control with exclusive rights', 0, FALSE, '[SA]'),
    ('ADMIN', 'Administrateur', 'Administrator', 'Administration système avec restrictions', 'System administration with restrictions', 1, TRUE, '[A]'),
    ('STAFF', 'Staff', 'Staff', 'Staff Holberton - Gestion quotidienne', 'Holberton Staff - Daily management', 2, TRUE, '[T]'),
    ('STUDENT', 'Étudiant', 'Student', 'Étudiant en formation', 'Student in training', 3, TRUE, '[S]');

-- Days of the week
CREATE TABLE IF NOT EXISTS enum_day_of_week (
    value VARCHAR(20) PRIMARY KEY,
    display_name_fr VARCHAR(20),
    display_name_en VARCHAR(20),
    display_order INT,
    is_working_day BOOLEAN DEFAULT TRUE,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_day_of_week (value, display_name_fr, display_name_en, display_order, emoji) VALUES 
    ('monday', 'Lundi', 'Monday', 1, '[MON]'),
    ('tuesday', 'Mardi', 'Tuesday', 2, '[TUE]'),
    ('wednesday', 'Mercredi', 'Wednesday', 3, '[WED]'),
    ('thursday', 'Jeudi', 'Thursday', 4, '[THU]'),
    ('friday', 'Vendredi', 'Friday', 5, '[FRI]');

-- Unavailability statuses
CREATE TABLE IF NOT EXISTS enum_unavailability_status (
    value VARCHAR(20) PRIMARY KEY,
    display_name_fr VARCHAR(30),
    display_name_en VARCHAR(30),
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    requires_action BOOLEAN DEFAULT FALSE,
    notification_priority INT,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_unavailability_status (value, display_name_fr, display_name_en, description_fr, description_en, display_order, requires_action, notification_priority, emoji) VALUES 
    ('pending', 'En attente', 'Pending', 'Demande soumise, en attente de validation', 'Request submitted, waiting for approval', 1, TRUE, 1, '[...]'),
    ('validated', 'Validée', 'Approved', 'Demande acceptée par le staff', 'Request approved by staff', 2, FALSE, 2, '[OK]'),
    ('rejected', 'Rejetée', 'Rejected', 'Demande refusée par le staff', 'Request rejected by staff', 3, FALSE, 2, '[KO]');

-- Notification types
CREATE TABLE IF NOT EXISTS enum_notification_type (
    value VARCHAR(30) PRIMARY KEY,
    display_name_fr VARCHAR(50),
    display_name_en VARCHAR(50),
    description_fr TEXT,
    description_en TEXT,
    priority INT,
    display_order INT,
    expiration_hours INT,
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_notification_type (value, display_name_fr, display_name_en, description_fr, description_en, priority, display_order, expiration_hours, emoji) VALUES 
    ('unavailability_request', 'Demande d''indisponibilité', 'Unavailability Request', 'Nouvelle demande à traiter', 'New request to process', 1, 1, 48, '[REQ]'),
    ('sod_feedback', 'Feedback SOD', 'SOD Feedback', 'Feedback à donner sur un SOD', 'SOD feedback to provide', 2, 2, 24, '[SOD]'),
    ('standup_feedback', 'Feedback Stand-up', 'Stand-up Feedback', 'Rapport de stand-up à remplir', 'Stand-up report to fill', 2, 3, 24, '[SU]'),
    ('ritual_reminder', 'Rappel Rituel', 'Ritual Reminder', 'Rappel de participation à un rituel', 'Ritual participation reminder', 3, 4, 4, '[!]');

-- Cohort pause types
CREATE TABLE IF NOT EXISTS enum_cohort_pause_type (
    value VARCHAR(30) PRIMARY KEY,
    display_name_fr VARCHAR(50),
    display_name_en VARCHAR(50),
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    min_duration_days INT NOT NULL,
    max_duration_days INT NOT NULL,
    scope VARCHAR(20) NOT NULL,  -- 'cohort', 'group', 'individual'
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_cohort_pause_type (value, display_name_fr, display_name_en, description_fr, description_en, display_order, min_duration_days, max_duration_days, scope, emoji) VALUES 
    ('school_holiday', 'Vacances scolaires', 'School Holiday', 'Période de vacances officielles', 'Official holiday period', 1, 7, 30, 'cohort', '[HOL]'),
    ('group_project', 'Projet de groupe', 'Group Project', 'Période de projet collaboratif majeur', 'Major collaborative project period', 2, 3, 14, 'group', '[PRJ]'),
    ('special_event', 'Événement spécial', 'Special Event', 'Hackathon, conférences, etc.', 'Hackathon, conferences, etc.', 3, 1, 7, 'individual', '[EVT]');

-- Metric types for statistics
CREATE TABLE IF NOT EXISTS enum_metric_type (
    value VARCHAR(30) PRIMARY KEY,
    display_name_fr VARCHAR(50),
    display_name_en VARCHAR(50),
    description_fr TEXT,
    description_en TEXT,
    display_order INT,
    is_individual BOOLEAN DEFAULT TRUE,
    calculation_period VARCHAR(20) NOT NULL,  -- 'daily', 'weekly', 'monthly'
    emoji VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO enum_metric_type (value, display_name_fr, display_name_en, description_fr, description_en, display_order, is_individual, calculation_period, emoji) VALUES 
    ('sod_participation', 'Participation SOD', 'SOD Participation', 'Nombre de SOD effectués par étudiant', 'Number of SODs per student', 1, TRUE, 'monthly', '[SOD]'),
    ('standup_master', 'Animation Stand-up', 'Stand-up Mastering', 'Nombre de fois Scrum Master', 'Number of times as Scrum Master', 2, TRUE, 'monthly', '[SU]'),
    ('ritual_balance', 'Équilibre des rituels', 'Ritual Balance', 'Répartition équitable des passages', 'Fair distribution of participation', 3, FALSE, 'weekly', '[BAL]'),
    ('cohort_activity', 'Activité de cohorte', 'Cohort Activity', 'Taux d''activité global par cohorte', 'Global activity rate per cohort', 4, FALSE, 'monthly', '[ACT]');

/*
Notes:
- Full bilingual support (EN/FR)
- ASCII art replaces emojis for better compatibility
- Enhanced role hierarchy with SUPER_ADMIN
- Updated ritual timings and durations
- Added scope for pause types
- Enhanced metric calculations
- Maintained display order for UI
- Priority system for notifications
- Timestamps for tracking
*/

-- End of 01-enums.sql