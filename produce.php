<?php header("Content-Type:text/html; charset=UTF-8");

  require('mysql_util.php');

  $queue_name = $_GET['queue_name'];
  $custom = $_GET['custom'];

  if ($queue_name == null) {
    echo "Queue name is null";
    return;
  }

  $db = GetMysqlConnect();

  $resource_sql = "SELECT * FROM Resources WHERE Name='$queue_name'";

  $resource_result = $db->query($resource_sql);
  if ($resource_result->num_rows == 0) {
    echo 'No such queue named '. $queue_name;
    return;
  }

  $resource_rows = $resource_result->fetch_assoc();
  $customizations = json_decode($resource_rows['Custom']);

  if ($customizations != null && !in_array($custom, $customizations)) {
    echo 'Invalid customization: ' . $custom;
    return;
  }

  // When resource can not customize, set custom to null.
  $custom_value = $customizations == null ? "null" : "'$custom'";
  $sql = "INSERT INTO ResourcePool (ResourceName, Custom)
          VALUES ('$queue_name', $custom_value)";
  if ($db->query($sql) !== true) {
    echo 'DB error: ' . $db->error;
    return;
  }

  $resource_id = $db->insert_id;

  echo 'Created resource with id: ' . $resource_id;

