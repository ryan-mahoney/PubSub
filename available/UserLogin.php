<?php
return function ($context, $post, $authentication) {
    if (!isset($context['dbURI']) || empty($context['dbURI'])) {
        throw new \Exception('Context does not contain a dbURI');
    }
    if (!isset($context['formMarker'])) {
        throw new \Exception('Form marker not set in post');
    }
    $document = $post->{$context['formMarker']};
    if ($document === false || empty($document)) {
        throw new \Exception('Document not found in post');
    }
    if (!isset($document['email']) || empty($document['email'])) {
        $post->errorFieldSet($context['formMarker'], 'Email missing');

        return;
    }
    if (!isset($document['password']) || empty($document['password'])) {
        $post->errorFieldSet($context['formMarker'], 'Password missing');

        return;
    }
    $found = $authentication->login($document['email'], $document['password']);
    if ($found === false) {
        $post->errorFieldSet($context['formMarker'], 'Incorrect login credentials.');
        if (filter_var($document['email'], FILTER_VALIDATE_EMAIL) === false) {
            $post->errorFieldSet($context['formMarker'], 'Bad email address?');
        }

        return;
    }
    $post->statusSaved();
};
