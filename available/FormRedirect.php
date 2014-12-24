<?php
return function ($context, $form) {
    if (!isset($_SESSION) || !isset($_SESSION['acl_redirect'])) {
        return;
    }
    if (!isset($context['formMarker'])) {
        return;
    }
    $myForm = $form->stored($context['formMarker']);
    if ($myForm === false) {
        return;
    }
    $myForm->after = 'redirect';
    $myForm->redirect = $_SESSION['acl_redirect'];
};
