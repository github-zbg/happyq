<?php
  // Lists a queue and does some calc on serve time
  header("Content-Type:text/html; charset=UTF-8");
  require('mysql_util.php');

  $queue_name = $_GET['queue_name'];

  $db = GetMysqlConnect();

  // Selects those in the queue which hasn't been served yet.
  $list_queue_sql = "SELECT q1.RequestId, q1.State, q1.QueueTime, r1.ProduceTime
    FROM Queues q1, ResourcePool r1, Servings s1
    WHERE
        q1.ResourceName = '$queue_name' AND
        q1.RequestId = s1.RequestId AND
        s1.ResourceId = r1.ResourceId
    ORDER BY q1.RequestId ASC";

  $list_queue_result = $db->query($list_queue_sql) or die($db->error);

  $result_array = array();

  while ($row = mysqli_fetch_assoc($list_queue_result)) {
    array_push($result_array, $row);
  }

  // Returns the queue in JSON format
  echo json_encode($result_array);

