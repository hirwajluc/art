<?php
require_once 'config/database.php';

class Submission {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllSubmissions($limit = null, $offset = 0, $search = '', $status = '') {
        $whereConditions = [];
        $params = [];
        
        if (!empty($search)) {
            $whereConditions[] = "(userName LIKE :search OR userEmail LIKE :search OR artworkName LIKE :search OR userCode LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($status)) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = "WHERE " . implode(' AND ', $whereConditions);
        }
        
        $limitClause = '';
        if ($limit) {
            $limitClause = "LIMIT :limit OFFSET :offset";
        }
        
        $query = "SELECT * FROM submissions $whereClause ORDER BY submissionDate DESC $limitClause";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        
        if ($limit) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSubmissionById($id) {
        $query = "SELECT * FROM submissions WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateSubmissionStatus($id, $status, $score = null, $feedback = null, $reviewedBy = null) {
        $query = "UPDATE submissions SET 
                  status = :status, 
                  score = :score, 
                  jury_feedback = :feedback, 
                  reviewed_at = NOW(),
                  reviewed_by = :reviewed_by
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':feedback', $feedback);
        $stmt->bindParam(':reviewed_by', $reviewedBy);
        
        return $stmt->execute();
    }
    
    public function getTotalCount($search = '', $status = '') {
        $whereConditions = [];
        $params = [];

        if (!empty($search)) {
            $whereConditions[] = "(userName LIKE :search OR userEmail LIKE :search OR artworkName LIKE :search OR userCode LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if (!empty($status)) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $status;
        }

        $whereClause = '';
        if (!empty($whereConditions)) {
            $whereClause = "WHERE " . implode(' AND ', $whereConditions);
        }

        $query = "SELECT COUNT(*) as total FROM submissions $whereClause";
        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get all versions for a submission, ordered oldest → newest.
     */
    public function getVersionsBySubmissionId(int $submissionId): array {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM submission_versions
                WHERE submission_id = :sid
                ORDER BY version_number ASC
            ");
            $stmt->bindValue(':sid', $submissionId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Submission getVersions error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the latest version number for a submission.
     */
    public function getLatestVersionNumber(int $submissionId): int {
        try {
            $stmt = $this->conn->prepare("
                SELECT MAX(version_number) FROM submission_versions
                WHERE submission_id = :sid
            ");
            $stmt->bindValue(':sid', $submissionId, PDO::PARAM_INT);
            $stmt->execute();
            return (int)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Check whether the submission deadline has passed.
     * Returns true if submissions are STILL OPEN.
     */
    public function isSubmissionOpen(): bool {
        try {
            $stmt = $this->conn->prepare("
                SELECT setting_value FROM competition_settings
                WHERE setting_key = 'submission_deadline'
            ");
            $stmt->execute();
            $deadline = $stmt->fetchColumn();
            if (!$deadline) return true; // no deadline set = open
            return strtotime($deadline) > time();
        } catch (PDOException $e) {
            return true; // default open on DB error
        }
    }

    /**
     * Check whether the registration deadline has passed.
     * Returns true if registration is STILL OPEN.
     */
    public function isRegistrationOpen(): bool {
        try {
            $stmt = $this->conn->prepare("
                SELECT setting_value FROM competition_settings
                WHERE setting_key = 'registration_deadline'
            ");
            $stmt->execute();
            $deadline = $stmt->fetchColumn();
            if (!$deadline) return true;
            return strtotime($deadline) > time();
        } catch (PDOException $e) {
            return true;
        }
    }
}
?>