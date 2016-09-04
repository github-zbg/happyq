<?php

  require('mysql_util.php');

  $queue_name = $_GET['queue_name'];

  $db = GetMysqlConnect();

  // Get all resources that have not been served.
  $list_available_sql = "SELECT r1.ResourceId, r1.Custom FROM ResourcePool r1
    LEFT JOIN
        Servings s1
    ON  r1.ResourceId = s1.ResourceId
    WHERE s1.ResourceId IS NULL
    AND ResourceName = '$queue_name'";

  $result = $db->query($list_available_sql) or die($db->error);

  $array = array();

  while ($data = $result->fetch_assoc()) {
    $array[] = $data;
  }

  // For each available resource, serve a reqeust in the queue.
  echo json_encode($array);
  foreach ($array as $value) {
    $custom = $value['Custom'];
    $resource_id = $value['ResourceId'];

    $custom_match_condition = "(q1.Custom is null)";
    if ($custom != NULL) {
      $custom_match_condition = "(q1.Custom = '$custom')";
    }
    $match_sql = "INSERT INTO Servings (ResourceId, RequestId)
      SELECT $resource_id, q1.RequestId
      FROM Queues q1
      WHERE
        $custom_match_condition
        AND NOT EXISTS (
          SELECT s1.ResourceId
          FROM Servings s1
          WHERE s1.ResourceId = $resource_id
                OR
                s1.RequestId = q1.RequestId
        )
      LIMIT 1
    ";
    $db->query($match_sql) or die($db->error);
  }

  $update_ready_sql = "UPDATE Queues q1
    SET q1.State='Ready'
    WHERE (q1.State='Processing' OR q1.State='Wait') AND q1.RequestId IN (
        SELECT s1.RequestId FROM Servings s1
    )";

  $db->query($update_ready_sql) or die($db->error);
  echo "200 OKAY";

