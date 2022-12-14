<?php 

namespace Database;

class MySQLConnect {
  private $host;
  private $username;
  private $password;
  private $database;
  private $connection;

  public function __construct($host, $username, $password, $database) {
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->database = $database;
  }

  public function connect() {
    $this->connection = mysqli_connect($this->host, $this->username, $this->password, $this->database);
  }

  public function query($query) {
    return mysqli_query($this->connection, $query);
  }

  public function query_row($query) {
    return $this->connection->query($query);
  }

  public function getLastId() {
    return mysqli_insert_id($this->connection);
  }

  public function close() {
    mysqli_close($this->connection);
  }
}


