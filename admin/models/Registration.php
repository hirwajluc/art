<?php
require_once 'config/database.php';

class Registration {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllRegistrations($limit = null, $offset = 0, $search = '') {
        try {
            // Validate parameters
            $offset = max(0, (int)$offset);
            if ($limit !== null) {
                $limit = max(1, (int)$limit);
            }
            
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE fullName LIKE :search OR email LIKE :search OR userCode LIKE :search";
                $params[':search'] = "%$search%";
            }
            
            $limitClause = '';
            if ($limit) {
                $limitClause = "LIMIT :limit OFFSET :offset";
            }
            
            $query = "SELECT * FROM registrations $whereClause ORDER BY registrationDate DESC $limitClause";
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
        } catch (PDOException $e) {
            error_log("Registration getAllRegistrations error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRegistrationById($id) {
        try {
            $query = "SELECT * FROM registrations WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Registration getRegistrationById error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalCount($search = '') {
        try {
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = "WHERE fullName LIKE :search OR email LIKE :search OR userCode LIKE :search";
                $params[':search'] = "%$search%";
            }
            
            $query = "SELECT COUNT(*) as total FROM registrations $whereClause";
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Registration getTotalCount error: " . $e->getMessage());
            return 0;
        }
    }
}
?>