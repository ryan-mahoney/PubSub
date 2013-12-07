<?php
return function ($context, $twitter) {
	$tweets = $twitter->externalFetch($context['value'], $context['expire'], $context['type'], true);
	if (is_array($tweets) && count($tweets) > 0) {
		$twitter->save($context['type'], $context['value'], $tweets);
	}
};