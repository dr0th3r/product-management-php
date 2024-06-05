<?php
class Database {
  // Change these constants to match your database configuration
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

enum ComparisonOperator: string {
  case Equals = "=";
  case NotEquals = "<>";
  case GreaterThan = ">";
  case GreaterThanOrEquals = ">=";
  case LessThan = "<";
  case LessThanOrEquals = "<=";
  case Like = "LIKE";
  case Between = "BETWEEN";
}

//true because we want similar behavior to hash set
enum Order: string {
  case Asc = "asc";
  case Desc = "desc";
}
?>