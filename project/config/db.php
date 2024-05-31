<?php
class Database {
  private const HOST = "localhost";
  private const USER = "root";
  private const PASSWORD = "";
  private const NAME = "extradeon";

  public static function connect() {
    try {
      return new PDO("mysql:host=" . self::HOST . ";dbname=" . self::NAME, self::USER, self::PASSWORD);
    } catch (PDOException $e) {
      die("Connection failed: " . $e->getMessage());
    }
  }
}
?>