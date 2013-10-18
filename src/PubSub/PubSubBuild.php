<?php
namespace PubSub;

class PubSubBuild {
	public function build ($root) {
		$cache = [];
		$listersCache = $root . '/../subscribers/_build.php';
		$files = glob($root . '/../subscribers/*.php');
		if (count($files) == 0) {
			file_put_contents($listersCache, '<?php' . "\n" . 'return [];');
			return;
		}
		$cache = '<?php' . "\n" . 'return [' . "\n";
		foreach ($files as $subscribers) {
			$name = basename($subscribers, '.php');
			if ($name == '_build') {
				continue;
			}
			$cache .= "'" . $name . "' => " . substr(trim(ltrim(trim(file_get_contents($subscribers)), '<?php')), 6, -1) . ',' . "\n\n";
		}
		$cache = substr($cache, 0, -3);
		$cache .= "\n" . '];';
		file_put_contents($listersCache, $cache);
		echo exec('php -l ' . $listersCache), "\n";
	}
}