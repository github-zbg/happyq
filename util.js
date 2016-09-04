/**
 * Various utils
 */

// Disable all inputs of a form, given the form id.
function DisableInputOfForm(formId) {
  var form = document.getElementById(formId);
  DisableInputOfElement(form);
}

// Disable all inputs in the element.
function DisableInputOfElement(parentElement) {
  var limit = parentElement.children.length;
  if (limit && limit > 0) {
    for (var i = 0; i < limit; i++) {
      element = parentElement.children[i];
      if (element instanceof HTMLInputElement) {
        element.disabled = true;
      } else {
        DisableInputOfElement(element);
      }
    }
  }
}
