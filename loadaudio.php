<?php

  // Enable client side cache.
  $file_timestamp = filemtime('ready.mp3.b64');
  $tsstring = gmdate('D, d M Y H:i:s ', $file_timestamp) . 'GMT';
  header("Last-Modified: $tsstring");
  header("Cache-Control: public, max-age=900");  // cache 15 min
  // Enable server side cache.
  if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
      $file_timestamp <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    header($_SERVER["SERVER_PROTOCOL"] . " 304 Not modified");
    exit;
  }

  echo file_get_contents('ready.mp3.b64');
