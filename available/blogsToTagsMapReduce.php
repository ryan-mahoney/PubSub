<?php
return function ($db) {
	//call map-reduce to generate tags
	$map = <<<MAP
		function() {
			if (!this.tags) {
				return;
			}
			for (var i=0; i < this.tags.length; i++) {
				emit(this.tags[i], 1);
			}
		}
MAP;
		
	$reduce = <<<REDUCE
		function(key, values) {
			var count = 0;
			for (var i = 0; i < values.length; i++) {
				count += values[i];
			}
			return count;
		}
REDUCE;
		
	return $db->mapReduce($map, $reduce, [
		'mapreduce' => 'blogs',
		'out' => 'blogsTags'
	]);
};