<?php
namespace PubSub;

class Topic {
	private $topics = [];
	private $subscribers = [];
	private $container;
	private $cache = false;

	public function __construct ($container) {
		$this->container = $container;
	}

	public function cacheSet ($cache) {
		$this->cache = (array)$cache;
	}

	public function load ($root) {
		if ($this->cache === false || !is_array($this->cache) || !isset($this->cache['topics']) || !is_array($this->cache['topics']) || count($this->cache['topics']) == 0) {
			return;
		}
		if (isset($this->cache['topics']) && is_array($this->cache['topics'])) {
			foreach ($this->cache['topics'] as $topic => $subscribers) {
				foreach ($subscribers as $subscriber => $services) {
					$this->topics[$topic][$subscriber] = $services;
				}
			}
		}
		$listersBuild = $root . '/../subscribers/_build.php';
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

	public function publish ($topic, array $context=[]) {
		$context = new \ArrayObject((array)$context);
		if (!isset($this->topics[$topic]) || !is_array($this->topics[$topic]) || empty($this->topics[$topic])) {
			return;
		}
		foreach ($this->topics[$topic] as $subscriber => $dependencies) {
			if (!isset($this->subscribers[$subscriber])) {
				throw new \Exception('Listener not defined or built for topic: ' . $topic);
			}
			$services = [];
			$services[] = $context;
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