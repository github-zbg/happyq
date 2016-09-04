<?php header("Content-Type:text/html; charset=UTF-8");

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $queue_name = trim($_POST['queue_name']);
    $queue_name = urlencode($queue_name);
    if (strlen($queue_name) > 0) {
      $button = $_POST['submit'];
      // Redirect
      if ($button == "Search Queue") {
        header("location: request.php?queue_name=$queue_name");
        exit;
      } else if ($button == "Publish Queue") {
        header("location: generate_qr_code.php?queue_name=$queue_name");
        exit;
      }
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body>

<!-- whole page move to center -->
<div id="whole" style="text-align:center">

<!-- use the script in the same page, htmlspecialchars() for safety -->
<form action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
  ResourceName: <input type="text" name="queue_name"/> <br/>
  <p/>
  <input type="submit" name="submit" value="Search Queue">
  <input type="submit" name="submit" value="Publish Queue">
</form>

</div>

</body>

</html>
