<?php
namespace App;

use PDO;

class RateLimiter
{
    private PDO $db;
    private int $maxAttempts;
    private int $lockoutTime; // en segundos

    public function __construct(PDO $db, int $maxAttempts = 5, int $lockoutTime = 900)
    {
        $this->db = $db;
        $this->maxAttempts = $maxAttempts;
        $this->lockoutTime = $lockoutTime;
    }

    public function recordAttempt(string $username, string $ip, bool $success): void
    {
        $stmt = $this->db->prepare("INSERT INTO login_attempts (username, ip_address, successful) VALUES (:username, :ip, :success)");
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':success', $success, PDO::PARAM_BOOL);
        $stmt->execute();
    }

    public function isLockedOut(string $ip): bool
    {
        // Usando sintaxis de intervalo de Postgres
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM login_attempts 
            WHERE ip_address = :ip 
            AND successful = FALSE 
            AND attempt_time > (CURRENT_TIMESTAMP - ( :lockout || ' seconds')::interval )
        ");
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':lockout', $this->lockoutTime, PDO::PARAM_INT);
        $stmt->execute();
        
        $attempts = $stmt->fetchColumn();
        return $attempts >= $this->maxAttempts;
    }
}
