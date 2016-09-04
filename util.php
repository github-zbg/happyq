<?php

  // Generate a new UID based on server time, remote address and request time.
  function GenerateUid() {
    $prefix = $_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_TIME'];
    $uid = md5(uniqid($prefix, true));
    $uuid = substr($uid, 0, 8) . '-'
      . substr($uid, 8, 4) . '-'
      . substr($uid, 12, 4) . '-'
      . substr($uid, 16, 4) . '-'
      . substr($uid, 20, 12);
    return $uuid;
  }

