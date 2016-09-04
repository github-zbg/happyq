<?php header("Content-Type:text/html; charset=UTF-8");

  require('mysql_util.php');

  // Url: queue.php?queue_name=xxx&id=xxx.
  // if query with id, check the State of a given request id in the queue.
  // if no id, check number of people ahead.

  // Returns a Json object: {
  //   success: bool, success/fail of the query.
  //   error: string, error message if fail.
  //   state: string,
  //   ahead: int, number of people ahead(including this id).

  $queue_name = $_GET['queue_name'];

  $response = array();
  if ($queue_name == null) {
    $response['success'] = false;
    $response['error'] = 'No queue name';
    die(json_encode($response, JSON_UNESCAPED_UNICODE));
  }

  $db = GetMysqlConnect();

  function QueryWithId($id) {
    global $db, $queue_name, $response;
    // Get this id's state together with all ids waiting ahead of it.
    $query = "select RequestId, State from Queues "
      . "where ResourceName = '$queue_name' "
      . "  and (RequestId = $id or RequestId < $id and State in ('Wait', 'Processing')) "
      . "order by RequestId desc ";  // Rank this id top.
    $sql_result = $db->query($query);
    if ($sql_result === false) {
      $response['success'] = false;
      $response['error'] = "Query error: $query";
      die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
    if ($sql_result->num_rows == 0) {
      $response['success'] = false;
      $response['error'] = "No record found. This id: $id is not in the queue";
      die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
    $row = $sql_result->fetch_assoc();
    if ($row['RequestId'] != $id) {  // This id must be at top.
      $response['success'] = false;
      $response['error'] = "This id: $id is not in the queue";
      die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
    $response['success'] = true;
    $response['state'] = $row['State'];
    $response['ahead'] = $sql_result->num_rows;
  }

  function QueryQueue() {
    global $db, $queue_name, $response;
    // Get this id's state together with all ids waiting ahead of it.
    $query = "select count(RequestId) as Number from Queues "
      . "where ResourceName = '$queue_name' "
      . "  and State in ('Wait', 'Processing') ";
    $sql_result = $db->query($query);
    if ($sql_result === false || $sql_result->num_rows == 0) {
      $response['success'] = false;
      $response['error'] = "Query error: $query";
      die(json_encode($response, JSON_UNESCAPED_UNICODE));
    }
    $row = $sql_result->fetch_assoc();
    $response['success'] = true;
    $response['ahead'] = $row['Number'];
  }

  $id = $_GET['id'];
  if ($id == null) {
    QueryQueue();
  } else {
    QueryWithId($id);
  }

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
