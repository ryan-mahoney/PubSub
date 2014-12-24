<?php
return function ($context, $db) {
    $map = <<<MAP
        function() {
            if (!this.tags) {
                emit('none', this.body);
                return;
            }
            for (var i=0; i < this.tags.length; i++) {
                emit(this.tags[i], this.body);
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

    $db->mapReduce($map, $reduce, [
        'mapreduce' => 'blurbs',
        'out' => 'blurbs_by_tag'
    ]);
};
