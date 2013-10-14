<?php
namespace PubSub;

class Topic {
	private $topics = [];
	private $subscribers = [];
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
			foreach ($config['topics'] as $topic => $subscribers) {
				foreach ($subscribers as $subscriber => $services) {
					$this->topics[$topic][$subscriber] = $services;
				}
			}
		}
		$listersBuild = $root . '/subscribers/_build.php';
		if (file_exists($listersBuild)) {
			$this->subscribers = require $listersBuild;
		}
	}
	
	public function show () {
		print_r($this->topics);
		print_r($this->subscribers);
	}

	public function subscribe ($topic, $callback, $services=[]) {
		if (!isset($this->topics[$topic])) {
			$this->topics[$topic] = [];
		}
		$this->topics[$topic][] = [$callback => $services];
	}

	public function publish ($topic, $event=[]) {
		$event = new \ArrayObject((array)$event);
		if (!isset($this->topics[$topic]) || !is_array($this->topics[$topic]) || empty($this->topics[$topic])) {
			return;
		}
		foreach ($this->topics[$topic] as $subscriber => $dependencies) {
			if (!isset($this->subscribers[$subscriber])) {
				throw new \Exception('Listener not defined or built for topic: ' . $topic);
			}
			$services = [];
			$services[] = $event;
			foreach ($dependencies as $dependency) {
				$services[] = $this->container->{$dependency};
			}
			$response = call_user_func_array($this->subscribers[$subscriber], $services);
			if ($response === false) {
				break;
			}
		}
	}
}