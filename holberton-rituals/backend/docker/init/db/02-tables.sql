-- 02-tables.sql
-- Main tables structure for Holberton Rituals
-- Version: 2.0.0
-- Made with Love & Passion by: Fassih & Zoé
-- Date: 2024-02-11

-- =============================
-- Users & Authentication
-- =============================

CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    slack_id VARCHAR(100),
    preferred_language VARCHAR(2) DEFAULT 'fr',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    failed_login_attempts INT DEFAULT 0,
    last_password_change TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    CONSTRAINT fk_user_role FOREIGN KEY (role) 
        REFERENCES enum_user_role(value),
    CONSTRAINT uq_user_email UNIQUE (email),
    INDEX idx_user_role (role),
    INDEX idx_user_active (is_active)
) ENGINE=InnoDB;

-- =============================
-- Cohorts Management
-- =============================

CREATE TABLE IF NOT EXISTS cohorts (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    name VARCHAR(50) NOT NULL,
    curriculum_type VARCHAR(20) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    slack_channel VARCHAR(100),
    slack_workspace_id VARCHAR(100),
    timezone VARCHAR(50) DEFAULT 'Europe/Paris',
    pause_periods JSON COMMENT 'Format: [{type, start_date, end_date, scope, affected_students}]',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cohort_type FOREIGN KEY (curriculum_type) 
        REFERENCES enum_curriculum_type(value),
    CONSTRAINT check_cohort_dates CHECK (end_date >= start_date),
    INDEX idx_cohort_dates (start_date, end_date),
    INDEX idx_cohort_active (is_active)
) ENGINE=InnoDB;

-- =============================
-- Students Management
-- =============================

CREATE TABLE IF NOT EXISTS students (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    user_id CHAR(36) NOT NULL,
    current_cohort_id CHAR(36) NOT NULL,
    curriculum_type VARCHAR(20) NOT NULL,
    sod_count INT DEFAULT 0,
    standup_count INT DEFAULT 0,
    last_sod_date DATE NULL,
    last_standup_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_user FOREIGN KEY (user_id) 
        REFERENCES users(id),
    CONSTRAINT fk_student_cohort FOREIGN KEY (current_cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT fk_student_curriculum FOREIGN KEY (curriculum_type) 
        REFERENCES enum_curriculum_type(value),
    CONSTRAINT uq_student_user UNIQUE (user_id),
    INDEX idx_student_counts (sod_count, standup_count),
    INDEX idx_student_last_dates (last_sod_date, last_standup_date),
    INDEX idx_student_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_cohort_history (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    student_id CHAR(36) NOT NULL,
    cohort_id CHAR(36) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_history_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT fk_history_cohort FOREIGN KEY (cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT check_history_dates CHECK (end_date IS NULL OR end_date >= start_date),
    INDEX idx_history_dates (start_date, end_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cohort_ritual_schedule (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    cohort_id CHAR(36) NOT NULL,
    ritual_type VARCHAR(20) NOT NULL,
    day_of_week VARCHAR(20) NOT NULL,
    scheduled_time TIME NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    effective_from DATE NOT NULL,
    effective_until DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by CHAR(36) NOT NULL,
    CONSTRAINT fk_schedule_cohort FOREIGN KEY (cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT fk_schedule_ritual FOREIGN KEY (ritual_type) 
        REFERENCES enum_ritual_type(value),
    CONSTRAINT fk_schedule_day FOREIGN KEY (day_of_week) 
        REFERENCES enum_day_of_week(value),
    CONSTRAINT fk_schedule_creator FOREIGN KEY (created_by) 
        REFERENCES users(id),
    CONSTRAINT uq_cohort_ritual_schedule UNIQUE (cohort_id, ritual_type, day_of_week, effective_from),
    INDEX idx_schedule_dates (effective_from, effective_until),
    INDEX idx_schedule_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_unavailability (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    student_id CHAR(36) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    pause_type VARCHAR(30) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    validated_by CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_unavail_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT fk_unavail_validator FOREIGN KEY (validated_by) 
        REFERENCES users(id),
    CONSTRAINT fk_unavail_status FOREIGN KEY (status) 
        REFERENCES enum_unavailability_status(value),
    CONSTRAINT fk_unavail_pause_type FOREIGN KEY (pause_type) 
        REFERENCES enum_cohort_pause_type(value),
    CONSTRAINT check_unavail_dates CHECK (end_date >= start_date),
    INDEX idx_unavail_dates (start_date, end_date),
    INDEX idx_unavail_status (status)
) ENGINE=InnoDB;

-- =============================
-- Rituals Management
-- =============================

CREATE TABLE IF NOT EXISTS sod_drawings (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    student_id CHAR(36) NOT NULL,
    presentation_date DATE NOT NULL,
    presentation_time TIME NOT NULL DEFAULT '11:30:00',
    evaluator_id CHAR(36) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    replacement_for_id CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sod_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT fk_sod_evaluator FOREIGN KEY (evaluator_id) 
        REFERENCES students(id),
    CONSTRAINT fk_sod_replacement FOREIGN KEY (replacement_for_id) 
        REFERENCES sod_drawings(id),
    CONSTRAINT uq_sod_date_student UNIQUE (presentation_date, student_id),
    INDEX idx_sod_dates (presentation_date),
    INDEX idx_sod_completion (is_completed)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS standup_assignments (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    student_id CHAR(36) NOT NULL,
    assignment_date DATE NOT NULL,
    meeting_time TIME NOT NULL DEFAULT '11:45:00',
    cohort_id CHAR(36) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    replacement_for_id CHAR(36) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_standup_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT fk_standup_cohort FOREIGN KEY (cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT fk_standup_replacement FOREIGN KEY (replacement_for_id) 
        REFERENCES standup_assignments(id),
    CONSTRAINT uq_standup_date_cohort UNIQUE (assignment_date, cohort_id),
    INDEX idx_standup_dates (assignment_date),
    INDEX idx_standup_completion (is_completed)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback_templates (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    ritual_type VARCHAR(20) NOT NULL,
    template JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    version INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_template_type FOREIGN KEY (ritual_type) 
        REFERENCES enum_ritual_type(value),
    INDEX idx_template_active (is_active),
    INDEX idx_template_version (version)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedbacks (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    ritual_type VARCHAR(20) NOT NULL,
    template_id CHAR(36) NOT NULL,
    evaluator_id CHAR(36) NOT NULL,
    evaluated_id CHAR(36) NOT NULL,
    feedback_data JSON NOT NULL,
    presentation_url TEXT NULL,
    session_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedback_type FOREIGN KEY (ritual_type) 
        REFERENCES enum_ritual_type(value),
    CONSTRAINT fk_feedback_template FOREIGN KEY (template_id) 
        REFERENCES feedback_templates(id),
    CONSTRAINT fk_feedback_evaluator FOREIGN KEY (evaluator_id) 
        REFERENCES students(id),
    CONSTRAINT fk_feedback_evaluated FOREIGN KEY (evaluated_id) 
        REFERENCES students(id),
    INDEX idx_feedback_dates (session_date),
    INDEX idx_feedback_participants (evaluator_id, evaluated_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS standup_reports (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    cohort_id CHAR(36) NOT NULL,
    report_date DATE NOT NULL,
    scrum_master_id CHAR(36) NOT NULL,
    bugs_report TEXT,
    common_difficulties TEXT,
    shared_tips TEXT,
    conclusion TEXT,
    additional_notes TEXT,
    projects_of_week JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_standup_report_cohort FOREIGN KEY (cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT fk_standup_report_master FOREIGN KEY (scrum_master_id) 
        REFERENCES students(id),
    CONSTRAINT uq_standup_report_date_cohort UNIQUE (cohort_id, report_date),
    INDEX idx_standup_report_date (report_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_daily_reports (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    standup_report_id CHAR(36) NOT NULL,
    student_id CHAR(36) NOT NULL,
    is_absent BOOLEAN DEFAULT FALSE,
    is_on_site BOOLEAN DEFAULT FALSE,
    achievements TEXT,
    today_goals TEXT,
    needs_help BOOLEAN DEFAULT FALSE,
    problem_nature TEXT,
    other_remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_daily_report_standup FOREIGN KEY (standup_report_id) 
        REFERENCES standup_reports(id),
    CONSTRAINT fk_daily_report_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT uq_daily_report_student UNIQUE (standup_report_id, student_id),
    INDEX idx_student_attendance (is_absent, is_on_site)
) ENGINE=InnoDB;

-- =============================
-- Statistics & Analytics
-- =============================

CREATE TABLE IF NOT EXISTS ritual_statistics (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    cohort_id CHAR(36) NOT NULL,
    ritual_type VARCHAR(20) NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    stats_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_stats_cohort FOREIGN KEY (cohort_id) 
        REFERENCES cohorts(id),
    CONSTRAINT fk_stats_ritual_type FOREIGN KEY (ritual_type) 
        REFERENCES enum_ritual_type(value),
    CONSTRAINT uq_stats_period UNIQUE (cohort_id, ritual_type, period_start),
    INDEX idx_stats_period (period_start, period_end)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS student_progress (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    student_id CHAR(36) NOT NULL,
    ritual_type VARCHAR(20) NOT NULL,
    month_date DATE NOT NULL,
    participation_count INT DEFAULT 0,
    successful_presentations INT DEFAULT 0,
    helpful_contributions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_progress_student FOREIGN KEY (student_id) 
        REFERENCES students(id),
    CONSTRAINT fk_progress_ritual_type FOREIGN KEY (ritual_type) 
        REFERENCES enum_ritual_type(value),
    CONSTRAINT uq_student_monthly_progress UNIQUE (student_id, ritual_type, month_date),
    INDEX idx_progress_date (month_date)
) ENGINE=InnoDB;

-- =============================
-- Notifications System
-- =============================

CREATE TABLE IF NOT EXISTS notifications (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    user_id CHAR(36) NOT NULL,
    type VARCHAR(30) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link TEXT,
    is_read BOOLEAN DEFAULT FALSE,
    is_urgent BOOLEAN DEFAULT FALSE,
    channels JSON NOT NULL DEFAULT '["in_app"]',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    CONSTRAINT fk_notif_user FOREIGN KEY (user_id) 
        REFERENCES users(id),
    CONSTRAINT fk_notif_type FOREIGN KEY (type) 
        REFERENCES enum_notification_type(value),
    INDEX idx_notif_user_unread (user_id, is_read),
    INDEX idx_notif_expiry (expires_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notification_preferences (
    id CHAR(36) PRIMARY KEY DEFAULT UUID(),
    user_id CHAR(36) NOT NULL,
    notification_type VARCHAR(30) NOT NULL,
    email_enabled BOOLEAN DEFAULT TRUE,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    slack_enabled BOOLEAN DEFAULT TRUE,
    quiet_hours JSON NULL COMMENT '{"start": "HH:MM", "end": "HH:MM", "timezone": "Europe/Paris"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pref_user FOREIGN KEY (user_id) 
        REFERENCES users(id),
    CONSTRAINT fk_pref_notif_type FOREIGN KEY (notification_type) 
        REFERENCES enum_notification_type(value),
    CONSTRAINT uq_user_notif_pref UNIQUE (user_id, notification_type)
) ENGINE=InnoDB;

-- End of 02-tables.sql