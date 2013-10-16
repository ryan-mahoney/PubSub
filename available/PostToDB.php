<?php
return function ($event, $post, $db) {
	if (!isset($event['dbURI']) || empty($event['dbURI'])) {
		throw new \Exception('Event does not contain a dbURI');
	}
	if (!isset($event['formMarker'])) {
		throw new \Exception('Form marker not set in post');
	}
	$document = $post->{$event['formMarker']};
	if ($document === false || empty($document)) {
		throw new \Exception('Document not found in post');
	}
	$documentObject = $db->documentStage($event['dbURI'], $document);
	$documentObject->upsert();
	$post->statusSaved();
};