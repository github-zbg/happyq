<?php header("Content-Type:text/html; charset=UTF-8");

  require('mysql_util.php');

  // Url: take.php?id=xxx
  // Process request to take a resource from queue, by setting the state
  // to 'Done'.

  $db = GetMysqlConnect();

  // Add to queue and Returns the request id.
  function TakeInQueue($request_id) {
    global $db;
    $update_sql = "update Queues set State = 'Done' "
      . "where RequestId = $request_id";
    if ($db->query($update_sql) !== true) {
      die("Fail to set to 'Done' for request: $request_id" . $db->error);
    }
  }

  $request_id = $_REQUEST['id'];
  if (!isset($request_id) || $request_id <= 0) {
    die("A valid request id is expected");
  }
  TakeInQueue($request_id);
