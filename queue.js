/**
 * Periodically query the state of queue.
 */

// Send and return ajax request to check the queue state.
function CheckQueue(queue_name, id, callback) {
  console.assert(window.XMLHttpRequest);
  var xhr = new XMLHttpRequest();
  var url = "queue.php?queue_name=" + queue_name;
  if (id != null) {
    url += "&id=" + id;
  }
  xhr.onreadystatechange = function() {
    if (callback) {
      callback(xhr, id);
    }
  }

  xhr.open("GET", url, true);
  xhr.send(null);
  return xhr;
}

// Confirm "take" to server.
function TakeIt(requestId) {
  console.assert(window.XMLHttpRequest);
  var xhr = new XMLHttpRequest();
  var url = "take.php?id=" + requestId;

  xhr.open("GET", url, true);
  xhr.send(null);
  return xhr;
}

// queue_name: the queue name to wait.
// id: the request id in the queue.
// stateAreaId: the area(div) in html to display the state if it is not null.
function WaitInQueue(queue_name, id, stateAreaId) {
  CheckQueue(queue_name, id, callback);
  LoadReadyAudio();

  function callback(req, id) {
    var interval = 2000;  // every 2 seconds
    if (req.readyState == 4 || req.readyState == "complete") {
      // The response is in Json, refer to queue.php for the format.
      var result = JSON.parse(req.responseText);
      if (!result['success']) {
        UpdateQueueState(result['error'], stateAreaId);
        return;
      }
      var display = "";
      console.assert(result['ahead'] > 0);
      if (result['state'] == "Wait" || result['state'] == "Processing") {
        display = "Waiting for " + queue_name + " ... @ "
            + new Date().toLocaleTimeString() + "<br/>";
        if (result['ahead'] == 1) {
          display += "You are the first now.";
        } else {
          display += (result['ahead'] - 1).toString() + " ahead in the queue now.";
        }
        // Wait more
        window.setTimeout(
            // Has to use annoymous function to pass parameters.
            function() { CheckQueue(queue_name, id, callback); },
            interval);
      } else if (result['state'] == "Ready") {
        display = "Your " + queue_name + " is ready @ "
            + new Date().toLocaleTimeString() + ".<br />Take it now?";
        PlayReadyAudio();
        window.setTimeout(
            function() { ShowConfirm(id, stateAreaId); },
            1000);  // Wait a while for playing audio.
      } else {
        display = "Unexpected state: " + result['state'];
      }
      UpdateQueueState(display, stateAreaId);
    }
  }
}

// queue_name: the queue name to show status.
// stateAreaId: the area(div) in html to display the status if it is not null.
function ShowQueueStatus(queue_name, stateAreaId) {
  CheckQueue(queue_name, null, callback);

  function callback(req, noUseId) {
    var interval = 2000;  // every 2 seconds
    if (req.readyState == 4 || req.readyState == "complete") {
      // The response is in Json, refer to queue.php for the format.
      var result = JSON.parse(req.responseText);
      if (!result['success']) {
        console.log("Show queue status error: " + result['error']);
        return;
      }
      var display = "";
      if (result['ahead'] == 0) {
        display = "No one in the queue. You can be the first.";
      } else {
        display = (result['ahead']).toString() + " ahead in the queue now.";
      }
      UpdateQueueState(display, stateAreaId);
      // Keep showing
      setTimeout(
          function() { CheckQueue(queue_name, null, callback); },
          interval);
    }
  }
}

function UpdateQueueState(message, stateAreaId) {
  if (stateAreaId != null) {
    var stateArea = document.getElementById(stateAreaId);
    if (stateArea != null) {
      stateArea.innerHTML = message;
    }
  }
}

function ShowConfirm(requestId, stateAreaId) {
  var yes = document.getElementById("yes_btn");
  var no = document.getElementById("no_btn");

  yes.style.visibility = "visible";
  yes.onclick = function() {
    StopReadyAudio();
    yes.style.visibility = "hidden";
    no.style.visibility = "hidden";
    TakeIt(requestId);  // Confirm taking the resource.
    UpdateQueueState("Thank you and enjoy.", stateAreaId);
  };

  no.style.visibility = "visible";
  no.onclick = function() {
    StopReadyAudio();
    yes.style.visibility = "hidden";
    no.style.visibility = "hidden";
    UpdateQueueState("You item is ready, remember to take it.", stateAreaId);
  };
}

function LoadReadyAudio() {
  // Start downloading audio.
  console.assert(window.XMLHttpRequest);
  var xhr = new XMLHttpRequest();
  var url = "loadaudio.php";
  xhr.onreadystatechange = function() {
    if (xhr.readyState != 4 && xhr.readyState != "complete") return;
    var audio = document.getElementById("ready_audio");
    if (!audio) return;
    audio.src = "data:audio/mpeg;base64," + xhr.responseText;
    audio.load();
  }

  xhr.open("GET", url, true);
  xhr.send(null);
}

function onPlayEnded() {
  var audio = document.getElementById("ready_audio");
  if (!audio) return;
  if (shouldPlay === true) {
    // loop the play
    PlayReadyAudio();
  }
}

function PlayReadyAudio() {
  var audio = document.getElementById("ready_audio");
  if (!audio) return;
  audio.play();
}

function StopReadyAudio() {
  var audio = document.getElementById("ready_audio");
  if (!audio) return;
  shouldPlay = false;
  audio.pause();
}
