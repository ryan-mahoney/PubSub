<?php
return function ($context, $post) {
	if (!isset($_SESSION) || !isset($_SESSION['user']) || !isset($_SESSION['user']['_id'])) {
		return;
	}
	$keys = [];
	if (isset($context['formMarker'])) {
		$keys[] = $context['formMarker'];
	}
	$keys[] = 'user_id';
	$post->set($keys, $_SESSION['user']['_id']);
};