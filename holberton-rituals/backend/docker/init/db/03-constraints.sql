-- 03-constraints.sql
-- Essential database constraints for data integrity
-- Version: 1.0.0
-- Made with Love & Passion by: Fassih & Zoé
-- Date: 2024-02-11
-- Keep it simple: only fundamental data integrity constraints! Business logic belongs in the service layer.

-- =============================
-- Basic Integrity Constraints
-- =============================

-- Cohorts unique name when active
ALTER TABLE cohorts
ADD CONSTRAINT uq_active_cohort_name 
UNIQUE (name, is_active);

-- No duplicate SODs
ALTER TABLE sod_drawings
ADD CONSTRAINT uq_student_sod_date 
UNIQUE (student_id, presentation_date);

-- No duplicate standups
ALTER TABLE standup_assignments
ADD CONSTRAINT uq_cohort_standup_date 
UNIQUE (cohort_id, assignment_date);

-- Basic date check
ALTER TABLE student_unavailability
ADD CONSTRAINT check_unavail_dates_v2 
CHECK (end_date >= start_date);

/*
Notes:
- Only fundamental data integrity constraints
- Business logic belongs in service layer
- Keep DB constraints minimal and clear
- Focus on data consistency, not business rules
*/