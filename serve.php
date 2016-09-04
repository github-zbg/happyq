<?php
header("Content-Type:text/html; charset=UTF-8");
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="main.css" type="text/css">
<script src="https://code.jquery.com/jquery-latest.min.js"></script>
<script src="serve.js"></script>
</head>

<body class="serve">

<h1 class="queue_title center">
<?php
  require('mysql_util.php');
  $queue_name = $_GET['queue_name'];
  $db = GetMysqlConnect();

  // Check if queue_name is valid
  $sql = "SELECT Name FROM ResourceQueues WHERE Name='$queue_name'";
  $result = $db->query($sql) or die($db->error);

  if ($result->num_rows !== 1) {
    echo "'$queue_name' doesn't exist!<br/>Σ(;ﾟдﾟ)";
  } else {
    echo $queue_name;
  }
?>
</h1>

<div class="center">Avg serve time: <span id="avg_time"></span></div>

<div style="width:100%; display: flex; justify-content: center; margin-top:24px;">
  <div class="center">
    <h2 class="queue-table-header">Queue</h2>
    <table class="queue_table center"></table>
    <input id="serve_button" onclick="serve()" type="image" src="bell.jpg" style="width: 80px;" alt="Serve!" autofocus/>
  </div>
  <div class="center">
    <h2 class="ready-table-header">Ready</h2>
    <table class="ready_table center"></table>
  </div>
</div>

</body>
</html>
