-- Types ENUM
CREATE TYPE curriculum_type AS ENUM ('fundamentals', 'specialization');
CREATE TYPE user_role AS ENUM ('admin', 'staff', 'student');
CREATE TYPE day_of_week AS ENUM ('monday', 'tuesday', 'wednesday', 'thursday', 'friday');
CREATE TYPE unavailability_status AS ENUM ('pending', 'validated', 'rejected');
CREATE TYPE ritual_type AS ENUM ('sod', 'standup');
CREATE TYPE notification_type AS ENUM ('unavailability_request', 'sod_feedback', 'standup_feedback', 'ritual_reminder');

-- Users & Authentication
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    slack_id VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    last_login TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Cohorts
CREATE TABLE cohorts (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    curriculum_type curriculum_type NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    slack_channel VARCHAR(100),
    slack_workspace_id VARCHAR(100),
    pause_periods JSONB,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Students
CREATE TABLE students (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    current_cohort_id INTEGER REFERENCES cohorts(id),
    sod_count INTEGER DEFAULT 0,
    standup_count INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id)
);

-- Student Cohort History (pour les transferts)
CREATE TABLE student_cohort_history (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id),
    cohort_id INTEGER REFERENCES cohorts(id),
    start_date DATE NOT NULL,
    end_date DATE,
    reason TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Ritual Days Configuration
CREATE TABLE cohort_ritual_days (
    id SERIAL PRIMARY KEY,
    cohort_id INTEGER REFERENCES cohorts(id),
    day day_of_week NOT NULL,
    ritual_type ritual_type NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(cohort_id, day, ritual_type)
);

-- Student Unavailability
CREATE TABLE student_unavailability (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status unavailability_status DEFAULT 'pending',
    validated_by INTEGER REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- SOD Drawings
CREATE TABLE sod_drawings (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id),
    presentation_date DATE NOT NULL,
    evaluator_id INTEGER REFERENCES students(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(presentation_date, student_id)
);

-- Standup Assignments
CREATE TABLE standup_assignments (
    id SERIAL PRIMARY KEY,
    student_id INTEGER REFERENCES students(id),
    assignment_date DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(assignment_date, student_id)
);

-- Feedback Templates
CREATE TABLE feedback_templates (
    id SERIAL PRIMARY KEY,
    ritual_type ritual_type NOT NULL,
    template JSONB NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Feedbacks
CREATE TABLE feedbacks (
    id SERIAL PRIMARY KEY,
    ritual_type ritual_type NOT NULL,
    template_id INTEGER REFERENCES feedback_templates(id),
    evaluator_id INTEGER REFERENCES students(id),
    evaluated_id INTEGER REFERENCES students(id),
    feedback_data JSONB NOT NULL,
    presentation_url TEXT,
    session_date DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Public Holidays
CREATE TABLE public_holidays (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(date)
);

-- System Configurations
CREATE TABLE configurations (
    id SERIAL PRIMARY KEY,
    key VARCHAR(50) UNIQUE NOT NULL,
    value JSONB,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Ritual Statistics (à ajouter avant les indexes)
CREATE TABLE ritual_statistics (
    id SERIAL PRIMARY KEY,
    cohort_id INTEGER REFERENCES cohorts(id),
    type ritual_type NOT NULL,
    period_start TIMESTAMP WITH TIME ZONE NOT NULL,
    period_end TIMESTAMP WITH TIME ZONE NOT NULL,
    stats_data JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- In-App Notifications (à ajouter avant les indexes)
CREATE TABLE in_app_notifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    type notification_type NOT NULL,
    title VARCHAR(255),
    message TEXT,
    link TEXT,
    is_read BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE
);

-- Indexes
CREATE INDEX idx_sod_drawings_date ON sod_drawings(presentation_date);
CREATE INDEX idx_standup_assignments_date ON standup_assignments(assignment_date);
CREATE INDEX idx_student_unavailability_dates ON student_unavailability(start_date, end_date);
CREATE INDEX idx_public_holidays_date ON public_holidays(date);

-- À ajouter à la fin du fichier
INSERT INTO configurations (key, value, description) VALUES
('sod_notification_delays', '{"before_days": [7, 3, 1]}', 'Délais de notification avant un SOD'),
('min_days_between_sod', '18', 'Délai minimum entre deux passages SOD'),
('slack_channels', '{"general": "C12345", "announcements": "C67890"}', 'Configuration des channels Slack');