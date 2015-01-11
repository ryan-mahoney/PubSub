<?php
namespace Opine;

use PHPUnit_Framework_TestCase;
use Opine\Container\Service as Container;
use Opine\Config\Service as Config;
use ArrayObject;

class PubSubTest extends PHPUnit_Framework_TestCase
{
    private $container;
    private $topic;

    public function setup()
    {
        $root = __DIR__.'/../public';
        $config = new Config($root);
        $config->cacheSet();
        $this->container = Container::instance($root, $config, $root.'/../config/containers/test-container.yml');
        $model = $this->container->get('pubSubModel');
        $model->build();
        $this->topic = $this->container->get('topic');
        $cache = $model->readDiskCache();
        $this->topic->cacheSet($cache);
    }

    public function testTopic()
    {
        $context = new ArrayObject(['abc' => 123]);
        $this->topic->publish('Test', $context);
        $this->assertTrue('def' === $context['test2']);
    }

    public function testSubscribe()
    {
        $this->topic->subscribe('Test', 'pubsubTest@someMethod2');
        $context = new ArrayObject(['www' => 123]);
        $this->topic->publish('Test', new ArrayObject($context));
        $this->assertTrue('qrs' === $context['test3']);
    }
}
