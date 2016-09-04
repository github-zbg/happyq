<?php header("Content-Type:text/html; charset=UTF-8");

  require('util.php');
  require('mysql_util.php');

  // Retrieve or set user cookie of this page.
  $current_user = $_COOKIE['User'];
  if (!isset($_COOKIE['User'])) {
    $current_user = GenerateUid();
    $one_day_later = time() + 3600 * 24;
    SetCookie("User", $current_user, $one_day_later);
  }

  // Url: request.php?queue_name=xxx
  // Let user customize a resource and add to queue.

  $db = GetMysqlConnect();

  // Returns null if no such resource.
  // Returns "[]" if no custom.
  function GetResourceCustomization($name) {
    global $db;  // declare to use the outer $db, otherwise can not see it.
    $query = "select Custom from Resources where Name = \"$name\"";
    $result = $db->query($query);
    if ($result->num_rows == 0) {
      return null;
    }
    $row = $result->fetch_assoc();
    return $row['Custom'] == null ? "[]" : $row['Custom'];
  }

  // Add to queue and Returns the request id.
  function AddToQueue($user, $queue_name, $custom) {
    global $db;
    // Insert into Queue
    $custom_value = $custom == null ? "null" : "'$custom'";
    $insert_sql = "insert into Queues (UserId, ResourceName, Custom)
        values ('$user', '$queue_name', $custom_value)";
    if ($db->query($insert_sql) !== true) {
      die("Fail to add to queue: $queue_name with query: $insert_sql, " . $db->error);
    }
    $request_id = $db->insert_id;  // Get the auto increment id.
    return $request_id;
  }

  // Returns existing waiting {RequestId, Custom} for user on a resource.
  // Returns {-1, ''} if not found.
  function RetrieveWaitingReqeust($user, $resource_name) {
    global $db;  // declare to use the outer $db, otherwise can not see it.
    $query = "select RequestId, Custom from Queues "
      . "where UserId = \"$user\" "
      . "and ResourceName = \"$resource_name\" "
      . "and State in ('Wait', 'Processing', 'Ready') ";
    $result = $db->query($query);
    if ($result->num_rows == 0) {
      return array("RequestId" => -1, "Custom" => "");
    }
    $row = $result->fetch_assoc();
    return array("RequestId" => $row['RequestId'], "Custom" => $row['Custom']);
  }

  // Check if queue_name is valid for both GET and POST request.
  $resource_name = $_REQUEST['queue_name'];
  if ($resource_name == null || $resource_name == "") {
    die('Resource name is required');
  }
  $custom_json = GetResourceCustomization($resource_name);
  if ($custom_json == null) {
    die("Resource: $resource_name is not found");
  }
  $custom = $_REQUEST['custom'];
  if ($custom != null && !in_array($custom, json_decode($custom_json))) {
    die("Custom: $custom is invalid");
  }
  // Try retrieving the existing Queue request.
  $waiting_request_id = -1;
  if (isset($_COOKIE['User'])) {  // existing user
    $result = RetrieveWaitingReqeust($current_user, $resource_name);
    $waiting_request_id = $result["RequestId"];
    // Reset $custom to the existing one.
    if ($waiting_request_id > 0) {
      $custom = $result["Custom"];
    }
  }

  // Use the php in the same page to process the request.
  // Can not use "GET", as it is the default and the script will be triggered
  // at the first pass.
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Prevent from resubmit on page reloading.
    if ($waiting_request_id <= 0) {
      $new_request_id = AddToQueue($current_user, $resource_name, $custom);
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="main.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>

<script src='resource_customize.js'></script>
<script src='util.js'></script>
<script src='queue.js'></script>
</head>

<body>

<!-- whole page move to center -->
<div id="whole" style="">

<!-- post form to the same page, htmlspecialchars() for safety -->
<form id="req_form"
      action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>"
      method="post">
  <div class="request-title">Options for <?php echo $resource_name; ?>:</div>

  <div id="customize_div"> </div>
  <script type="text/javascript">
    <?php
      // Run js function, addslashes to escape " in Json string.
      echo "LoadCustomize('customize_div', \"" . addslashes($custom_json)
        . "\", \"" . $custom . "\");";
    ?>
  </script>

  <input id="queue-button" type="submit" value="Queue">
</form>

<!-- The queue state is updated in this div -->
<div id='queueState_div'></div>
<div id="buttons">
  <div id='no_btn' style="visibility:hidden">Meh...</div>
  <div id='yes_btn' style="visibility:hidden">Yahhhh!</div>
</div>

<!-- invisible without "controls" option -->
<audio id="ready_audio" onended="onPlayEnded()">
</audio>

</div>

<script type='text/javascript'>
  // The indicator of whether the audio should be played.
  var shouldPlay = true;

  <?php
    // Try continuing waiting on existing request.
    $request_id = 0;
    if ($waiting_request_id > 0) {
      $request_id = $waiting_request_id;
    } else if (isset($new_request_id) && $new_request_id > 0) {
      $request_id = $new_request_id;
    }
    // Run js functions to wait on request.
    if ($request_id > 0) {
      echo "DisableInputOfForm('req_form');";
      echo "WaitInQueue(\"$resource_name\", $request_id, \"queueState_div\");";
    }
  ?>
</script>

</body>
</html>
