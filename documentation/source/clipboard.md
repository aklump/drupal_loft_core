# Clipboard Library

This module provides a copy to clipboard on click API.  Here is an example implementation.

1. Add the library `loft_core/clipboard`.
1. Add the following to a clickable element `data-loft-core-clipboard`, whose value is the value to be copied to the clipboard.

## Optional attributes

1. Add `data-loft-core-clipboard-reveal` with a value that will temporarily replace the inner html of the clicked element.  After a short delay the clicked element's original inner HTML will be returned.
1. Control the reveal duration by setting `data-loft-core-clipboard-reveal-duration` to a millisecond value.
