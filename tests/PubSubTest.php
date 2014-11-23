<?php
namespace Opine;

use PHPUnit_Framework_TestCase;
use Opine\Container\Service as Container;
use Opine\Config\Service as Config;

require __DIR__ . '/SomeClass.php';

class PubSubTest extends PHPUnit_Framework_TestCase {
    private $container;
    private $topic;

    public function setup () {
        $root = __DIR__ . '/../public';
        $config = new Config($root);
        $config->cacheSet();
        $this->container = new Container($root, $config, $root . '/../container.yml');
        $model = $this->container->get('pubSubModel');
        $model->build();
        $this->topic = $this->container->get('topic');
    }

    public function testTopic () {
        $this->topic->publish('Test');
    }
}