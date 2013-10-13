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
			foreach ($config['topics'] as $callback => $services) {
				$this->topics[$topic][] = [$callback => $services];
			}
		}
		$listersBuild = $root . '/subscribers/_build.php';
		if (file_exists($listersBuild)) {
			$this->listeners = require $listersBuild;
		}
		$this->show();
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

	public function publish ($topic, $args=[]) {
		if (!isset($this->topics[$topic])) {
			return;
		}
		foreach ($this->topics[$topic] as $callback => $arguments) {
			if (!isset($this->listeners[$callback])) {
				continue;
			}
			$args = [];
			foreach ($arguments as $argument) {
				$args[] = $this->container->{$argument};
			}
			$response = call_user_func_array($this->listeners[$callback], $args);
			if ($response === false) {
				break;
			}
		}
	}
}