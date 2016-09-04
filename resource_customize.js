/**
 * Deal with resource customization.
 */

// Add a row for customization under a parent div.
function AddCustomize(parentDivId) {
  var now = Date.now();  // milliseconds since epoch.
  var parentDiv = document.getElementById(parentDivId);
  // Create a new div to hold a row, aka "input" and "remove".
  var newDiv = document.createElement("div");

  // Create "input"
  var newInput = document.createElement("input");
  newInput.type = "text";
  // Append timestamp to make the name in parameter unique.
  // Otherwise PHP overwrites the previous one.
  newInput.name = "custom_" + now.toString();
  newInput.id = "custom_" + now.toString();
  newDiv.appendChild(newInput);

  // Create "remove" button
  var delNode = document.createElement("input");
  delNode.type = "button";
  delNode.value = "[-]";
  delNode.onclick = function() {
    parentDiv.removeChild(newDiv);
  }
  newDiv.appendChild(delNode);

  // add a new line
  var newline = document.createElement("br");
  newDiv.appendChild(newline);

  // Add row
  parentDiv.appendChild(newDiv);
}

// Load a customization in Json under a parent div.
function LoadCustomize(parentDivId, customJson, customized) {
  var custom = JSON.parse(customJson);
  var parentDiv = document.getElementById(parentDivId);
  for (var c in custom) {
    var value = custom[c];
    // c is 0, 1, 2, ...
    var span = document.createElement("span");
    span.id = "option";

    var radio = document.createElement("input");
    radio.type = "radio";
    radio.name = "custom";  // set to the same name as a group
    radio.value = value;
    if (c == 0 && customized == "") {  // choose the first one if no customized item by default
      radio.checked = "checked";
    }
    if (customized != null && value == customized) {
      radio.checked = "checked";  // choose the customized one
    }
    span.appendChild(radio);

    var textDiv = document.createElement("div");
    textDiv.id = "option-text";
    var text = document.createTextNode(value);
    textDiv.appendChild(text);
    span.appendChild(textDiv);
    if (radio.checked) {
      span.className="checked";
    }

    span.onclick = function() {
      if (this.childNodes[0].disabled != true) {
        var spans = document.getElementsByClassName("checked");
        for (var i = 0; i < spans.length; i++) {
          spans[i].className="";
        }
        this.className="checked";
        this.childNodes[0].checked = true;
      }
    };

    parentDiv.appendChild(span);
  }
}
