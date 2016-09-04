<?php header("Content-Type:text/html; charset=UTF-8");

  require('mysql_util.php');

  // Return resource name on success, null otherwise.
  function CreateResouceAndQueue() {
    $resource_name = trim($_POST['resource_name']);
    if ($resource_name == "") {
      die('Resource name is required');
    }

    // "Custom" column is a json string.
    $customize = array();
    foreach($_POST as $key => $value) {
      $prefix = strtolower(substr($key, 0, 6));
      $value = trim($value);
      if ($prefix == "custom" && strlen($value) > 0) {
        array_push($customize, $value);
      }
    }
    // Add JSON_UNESCAPED_UNICODE to avoid escaping Chinese so that we can
    // retrieve Chinese back from DB.
    $customize = json_encode($customize, JSON_UNESCAPED_UNICODE);

    $cancelable = strtolower($_POST['queue_cancelable']);
    if ($cancelable != "true") {
      $cancelable = "false";
    }

    $db = GetMysqlConnect();

    // Create resource
    $create_resource_sql = "insert into Resources (Name, Custom)
        values ('$resource_name', '$customize')";
    // Create resource queue
    // Pass $cancelable as true/false instead of 'true'/'false'.
    $create_queue_sql = "insert into ResourceQueues (Name, Cancelable)
        values ('$resource_name', $cancelable)";

    // Transaction
    $db->query('begin');
    if ($db->query($create_resource_sql) &&
        $db->query($create_queue_sql)) {
      $db->query('commit');
      return $resource_name;
    }
    // Fail
    echo 'Create resource error: ' . $db->error;
    $db->query('rollback');
    return null;
  }

  // Use the php in the same page to process the request.
  // Can not use "GET", as it is the default and the script will be triggered
  // at the first pass.
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $resource_name = CreateResouceAndQueue();
    if ($resource_name == null) {
      exit;  // prevent from running other code
    }
    // redirect to serve page.
    header("location: serve.php?queue_name=$resource_name");
  }
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script src="resource_customize.js"></script>
</head>

<body>

<!-- whole page move to center -->
<div id="whole" style="text-align:center">

<!-- use the script in the same page, htmlspecialchars() for safety -->
<form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
  Resource: <input type="text" name="resource_name"/> <br/>
  <p/>

  Customize:
  <div id="customize"> </div>
  <input type="button" value="[+]" onclick="AddCustomize('customize')"/>
  <p/>

  Queue: <input type="checkbox" name="queue_cancelable" value="true"/> Cancelable <br/>
  <p/>

  <input type="submit" value="Create">
</form>

</div>

</body>
</html>
