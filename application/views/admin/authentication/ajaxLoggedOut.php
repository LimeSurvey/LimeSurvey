<?php
/**
 * Modal content shown by the adminbasics AjaxHelper when an ajax call fails because the user is logged out.
 * "Log in" reloads the current page, which redirects to the login form and back to the page after login.
 */
?>
<div class="modal-header">
    <h2 class="modal-title h5"><?php eT("You have been logged out"); ?></h2>
</div>
<div class="modal-body">
    <p role="alert"><?php eT("Please log in again. Changes on this page that have not been saved yet will be lost."); ?></p>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><?php eT("Close"); ?></button>
    <a href="" class="btn btn-primary"><?php eT("Log in"); ?></a>
</div>
