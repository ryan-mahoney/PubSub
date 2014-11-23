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
        $this->topic->cacheSet($model->readDiskCache());
    }

    public function testTopic () {
        $context = ['abc' => 123];
        $this->topic->publish('Test', $context);
        $this->assertTrue('def' === $context['test2']);
    }

    public function testSubscribe () {
        $this->topic->subscribe('Test', 'pubsubTest@someMethod2');
        $context = ['www' => 123];
        $this->topic->publish('Test', $context);
        $this->assertTrue('qrs' === $context['test3']);
    }
}