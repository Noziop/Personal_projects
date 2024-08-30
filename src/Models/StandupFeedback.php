<?php

namespace App\Models;

use PDO;

class StandupFeedback
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(array $data)
    {
        $sql = "INSERT INTO standup_feedback (cohort_id, date, absent, on_site, achievements, today_goals, need_help, problem_nature, other_remarks, content, scrum_master_names, bugs_report, cohort_difficulties, shared_tips, conclusion, other_reports) 
                VALUES (:cohort_id, :date, :absent, :on_site, :achievements, :today_goals, :need_help, :problem_nature, :other_remarks, :content, :scrum_master_names, :bugs_report, :cohort_difficulties, :shared_tips, :conclusion, :other_reports)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM standup_feedback WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByCohortAndDate($cohortId, $date)
    {
        $sql = "SELECT * FROM standup_feedback WHERE cohort_id = :cohort_id AND date = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['cohort_id' => $cohortId, 'date' => $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByDateRange($startDate, $endDate)
    {
        $sql = "SELECT * FROM standup_feedback WHERE date BETWEEN :start_date AND :end_date ORDER BY date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, array $data)
    {
        $sql = "UPDATE standup_feedback SET 
                cohort_id = :cohort_id, 
                date = :date, 
                absent = :absent, 
                on_site = :on_site, 
                achievements = :achievements, 
                today_goals = :today_goals, 
                need_help = :need_help, 
                problem_nature = :problem_nature, 
                other_remarks = :other_remarks, 
                content = :content, 
                scrum_master_names = :scrum_master_names, 
                bugs_report = :bugs_report, 
                cohort_difficulties = :cohort_difficulties, 
                shared_tips = :shared_tips, 
                conclusion = :conclusion, 
                other_reports = :other_reports 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM standup_feedback WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}