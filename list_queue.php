<?php
  // Lists a queue and sets the first one in the queue to be 'Processing'
  header("Content-Type:text/html; charset=UTF-8");
  require('mysql_util.php');

  $queue_name = $_GET['queue_name'];

  $db = GetMysqlConnect();

  // Selects those in the queue which hasn't been served yet.
  $list_queue_sql = "SELECT * 
    FROM Queues q1
    WHERE q1.ResourceName='$queue_name'
        AND (q1.State='Wait' OR q1.State='Processing')
        AND NOT EXISTS (
            SELECT s1.RequestId
            FROM Servings s1
            WHERE s1.RequestId = q1.RequestId
        )
    ORDER BY q1.RequestId ASC";

  // Sets the first one in the previous query to be 'Processing'
  $set_processing_sql = "UPDATE Queues
    SET State='Processing'
    WHERE RequestId IN (
        SELECT RequestId FROM (
            $list_queue_sql
            LIMIT 1
        ) AS TB
    )";

  // Sets the first one in queue to 'Processing'
  $db->query($set_processing_sql) or die('set ' . $db->error);

  $list_queue_result = $db->query($list_queue_sql) or die($db->error);

  $result_array = array();

  while ($row = mysqli_fetch_assoc($list_queue_result)) {
    array_push($result_array, $row);
  }

  // Returns the queue in JSON format
  echo json_encode($result_array);

