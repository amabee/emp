<?php

class SystemController
{
  private $db;

  public function __construct()
  {
    $this->db = getDBConnection();
    if (!$this->db) {
      throw new Exception("Database connection failed");
    }
  }

  public function getSystemDetails()
  {
    $stmt = $this->db->prepare("SELECT * FROM company_info LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
?>

