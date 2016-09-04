<?php header("Content-Type:text/html; charset=UTF-8");

  require('mysql_util.php');

  define("SERVER_HOST", "138.68.22.29");

  // Url: generate_qr_code.php?queue_name=xxx
  // Generate QR code to the queue.

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

  // Returns an img tag of QR code.
  function GenerateQR($resource_name, $size) {
    // Encode the url to reduce page load in wechat.
    $resource_name = urlencode($resource_name);
    $url_in_qr = "http://" . SERVER_HOST
        . "/request.php?queue_name=$resource_name";

    // Use Google Chart API
    // E.g. http://www.helloweba.com/view-blog-247.html
    $encoded_url = urlencode($url_in_qr);
    $level = 'L';  // one of 'L', 'M', 'Q', 'H'
    $margin = '0';
    $img_src = 'http://chart.apis.google.com/chart?cht=qr'
      . '&chs=' . $size . 'x' . $size
      . '&chld=' . $level . '|' . $margin
      . '&chl=' . $encoded_url;

    return '<img src="' . $img_src . '"'
      . ' width="' . $size . '"'
      . ' height="' . $size . '"'
      . ' />';
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

  $size = 400;  // width and height
  $qr_code_url = GenerateQR($resource_name, $size);
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script src='queue.js'></script>
</head>

<body>

<!-- whole page move to center -->
<div id="whole" style="text-align:center">

<?php
  echo '<h1>';
  echo 'Scan to queue on ' . $resource_name . ' <br/>';
  echo '</h1>';
  echo $qr_code_url;  // show QR img
?>

<p/>

<!-- The queue state is updated in this div -->
<h2>
<div id='queueState_div'></div>
</h2>
<script type='text/javascript'>
<?php
  // Run js to show queue status.
  echo "ShowQueueStatus(\"$resource_name\", \"queueState_div\");";
?>
</script>

</div>

</body>
</html>
