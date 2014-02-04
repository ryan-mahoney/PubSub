<?php
return function ($context, $post, $person) {
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
    if (!isset($document['email'])) {
        $post->errorFieldSet($context['formMarker'], 'Email missing');
        return;
    }
    $found = $person->findByEmail($document['email']);
    if ($found !== false) {
        $post->errorFieldSet($context['formMarker'], 'An Account already exists for this address.');
        return;
    }
    $created = $person->create($document);
    if ($created !== true) {
        $post->errorFieldSet($context['formMarker'], $created);
        return;    
    }
    $person->groupJoin('registered');
    $person->activityAdd('register', 'Created and account.');
    $post->statusSaved();
};