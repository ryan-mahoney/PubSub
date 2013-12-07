<?php
return function ($context, $twitter) {
	$twitter->externalFetch($context['value'], $context['expire'], $context['type']);
};