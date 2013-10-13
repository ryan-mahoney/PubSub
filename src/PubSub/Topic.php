<?php
namespace PubSub;

class Topic {
	private $topics = [];
	private $listeners = [];
	private $container;

	public function __construct ($container) {
		$this->container = $container;
	}

	public function load ($root) {
		$topicConfig = $root . '/subscribers/topics.yml';
		if (!file_exists($topicConfig)) {
			return;
		}
		if (!function_exists('yaml_parse')) {
			throw new \Exception('PHP must be compiled with YAML PECL extension');
		}
		$config = yaml_parse_file($topicConfig);
		if ($config == false) {
			throw new \Exception('Can not parse YAML file: ' . $topicConfig);
		}
		if (isset($config['topics']) && is_array($config['topics'])) {
			foreach ($config['topics'] as $topic => $listeners) {
				foreach ($listeners as $listener => $services) {
					$this->topics[$topic][$listener] = $services;
				}
			}
		}
		$listersBuild = $root . '/subscribers/_build.php';
		if (file_exists($listersBuild)) {
			$this->listeners = require $listersBuild;
		}
	}
	
	public function show () {
		print_r($this->topics);
		print_r($this->listeners);
	}

	public function subscribe ($topic, $callback, $services=[]) {
		if (!isset($this->topics[$topic])) {
			$this->topics[$topic] = [];
		}
		$this->topics[$topic][] = [$callback => $services];
	}

	public function publish ($topic) {
		if (!isset($this->topics[$topic]) || !is_array($this->topics[$topic]) || empty($this->topics[$topic])) {
			return;
		}

		foreach ($this->topics[$topic] as $listener => $dependencies) {
			if (!isset($this->listeners[$listener])) {
				throw new \Exception('Listener not defined or built for topic: ' . $topic);
			}
			$services = [];
			foreach ($dependencies as $dependency) {
				$services[] = $this->container->{$dependency};
			}
			$response = call_user_func_array($this->listeners[$listener], $services);
			if ($response === false) {
				break;
			}
		}
	}
}