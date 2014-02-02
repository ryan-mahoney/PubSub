<?php
return function ($context, $collection) {
    $collection->statsSet($context['dbURI']);
};