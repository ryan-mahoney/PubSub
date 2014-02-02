<?php
return function ($context, $db) {
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
    
    try {
        $result = $db->mapReduce($map, $reduce, [
            'mapreduce' => $context['collection'],
            'out' => $context['collection'] . '_tags'
        ]);
    } catch (\Exception $e) {
        $result = false;
    }

    return $result;
};