<?php

  define("DB_HOST", "localhost");
  define("DB_USER", "happyq");
  define("DB_PWD", "happyq");
  define("DB_NAME", "HappyQ_DB");

  // Returns a connect to mysql, using mysqli lib.
  function GetMysqlConnect() {
    $con = new mysqli(DB_HOST, DB_USER, DB_PWD, DB_NAME);
    if ($con->connect_error) {
      die('Can not connect DB: ' . $con->connect_error);
    }
    if (!$con->query("set names utf8")) {
      die('Can not set charset to utf8: ' . $con->error);
    }
    return $con;
  }

