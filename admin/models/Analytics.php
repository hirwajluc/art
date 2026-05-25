<?php
require_once 'config/database.php';

class Analytics {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        // Total registrations
        $query = "SELECT COUNT(*) as total FROM registrations";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_registrations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total submissions
        $query = "SELECT COUNT(*) as total FROM submissions";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_submissions'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Submissions by status
        $query = "SELECT status, COUNT(*) as count FROM submissions GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats['pending_submissions'] = 0;
        $stats['approved_submissions'] = 0;
        $stats['rejected_submissions'] = 0;
        
        foreach($statusCounts as $status) {
            $stats[$status['status'] . '_submissions'] = $status['count'];
        }
        
        // Registration by category
        $query = "SELECT category, COUNT(*) as count FROM registrations GROUP BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['registrations_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Submissions by category
        $query = "SELECT category, COUNT(*) as count FROM submissions GROUP BY category";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['submissions_by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Recent registrations (last 7 days)
        $query = "SELECT DATE(registrationDate) as date, COUNT(*) as count 
                  FROM registrations 
                  WHERE registrationDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                  GROUP BY DATE(registrationDate) 
                  ORDER BY date";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['recent_registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
    
    public function getCountryStats() {
        $query = "SELECT nationality, COUNT(*) as count 
                  FROM registrations 
                  GROUP BY nationality 
                  ORDER BY count DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>