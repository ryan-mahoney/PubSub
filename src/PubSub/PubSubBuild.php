<?php
namespace PubSub;

class PubSubBuild {
	private $root;
	private $yamlSlow;

	public function __construct ($root, $yamlSlow) {
		$this->root = $root;
		$this->yamlSlow = $yamlSlow;
	}

	public function build () {
		$this->subscribers();
		return $this->topics();
	}

	public function topics () {
		$topicConfig = $this->root . '/../subscribers/topics.yml';
		$config = $this->topicsRead($topicConfig);
		$this->bundleTopicsInclude($config);
		return $config;
	}

	public function bundleTopicsInclude (&$config) {
		$bundleCache = $this->root . '/../bundles/cache.json';
		if (!file_exists($bundleCache)) {
			return;
		}
		$bundles = (array)json_decode(file_get_contents($bundleCache), true);
		if (!is_array($bundles) || count($bundles) == 0) {
			return;
		}
		foreach ($bundles as $bundle) {
			$bundleTopics = $this->root . '/../bundles/' . $bundle . '/subscribers/topics.yml';
			if (!file_exists($bundleTopics)) {
				continue;
			}
			$bundleConfig = $this->topicsRead($bundleTopics);
			if (!isset($bundleConfig['topics']) || !is_array($bundleConfig['topics']) || count($bundleConfig['topics']) == 0) {
				continue;
			}
			foreach ($bundleConfig['topics'] as $name => $topic) {
				$config['topics'][$name] = $topic;
			}
		}
	}

	private function topicsRead ($topicConfig) {
		if (!file_exists($topicConfig)) {
			return;
		}
		if (function_exists('yaml_parse_file')) {
			$config = yaml_parse_file($topicConfig);
		} else {
			$config = $this->yamlSlow->parse($topicConfig);
		}
		if ($config == false) {
			throw new \Exception('Can not parse YAML file: ' . $topicConfig);
		}
		return $config;
	}

	public function subscribers () {
		$cache = [];
		$listersCache = $this->root . '/../subscribers/_build.php';
		if (file_exists($listersCache)) {
			unlink($listersCache);
		}
		$files = glob($this->root . '/../subscribers/*.php');
		if (count($files) == 0) {
			file_put_contents($listersCache, '<?php' . "\n" . 'return [];');
		}
		$cache = '<?php' . "\n" . 'return [' . "\n";
		foreach ($files as $subscriber) {
			$name = basename($subscriber, '.php');
			if ($name == '_build') {
				continue;
			}
			$cache .= $this->subcriberRead($name, $subscriber);
		}
		$this->bundleSubscribersInclude($cache);
		$cache = substr($cache, 0, -3);
		$cache .= "\n" . '];';
		file_put_contents($listersCache, $cache);
		ob_start();
		echo exec('php -l ' . $listersCache);
		$buffer = ob_get_clean();
		if (substr_count($buffer, 'No syntax errors detected in') == 1) {
			echo 'Good: Topic build file is passing PHP error check.', "\n";
		} else {
			echo 'Problem: Topic build file is not passing PHP error check.', "\n";
		}
	}

	private function bundleSubscribersInclude (&$cache) {
		$bundleCache = $this->root . '/../bundles/cache.json';
		if (!file_exists($bundleCache)) {
			return;
		}
		$bundles = (array)json_decode(file_get_contents($bundleCache), true);
		if (!is_array($bundles) || count($bundles) == 0) {
			return;
		}
		foreach ($bundles as $bundle) {
			$bundleSubscribers = $this->root . '/../bundles/' . $bundle . '/subscribers';
			if (!file_exists($bundleSubscribers)) {
				continue;
			}
			$files = glob($bundleSubscribers . '/*.php');
			if (!is_array($files) || count($files) == 0) {
				continue;
			}
			foreach ($files as $subscriber) {
				$name = basename($subscriber, '.php');
				if ($name == '_build') {
					continue;
				}
				$cache .= $this->subcriberRead($name, $subscriber);
			}
		}
	}

	private function subcriberRead ($name, $path) {
		return "'" . $name . "' => " . substr(trim(ltrim(trim(file_get_contents($path)), '<?php')), 6, -1) . ',' . "\n\n";
	}
}