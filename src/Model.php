<?php
/**
 * Opine\PubSubBuild
 *
 * Copyright (c)2013, 2014 Ryan Mahoney, https://github.com/Opine-Org <ryan@virtuecenter.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Opine\PubSub;

use Exception;
use Symfony\Component\Yaml\Yaml;

class Model
{
    private $root;
    private $bundleModel;

    public function __construct($root, $bundleModel)
    {
        $this->root = $root;
        $this->bundleModel = $bundleModel;
        $this->cacheFile = $this->root.'/../var/cache/topics.json';
    }

    public function readDiskCache()
    {
        $topics = [];
        if (!file_exists($this->cacheFile)) {
            return [];
        }
        $topics = json_decode(file_get_contents($this->cacheFile), true);
        if (!isset($topics['topics'])) {
            return [];
        }

        return $topics['topics'];
    }

    public function build()
    {
        $topics = $this->topics();
        file_put_contents($this->cacheFile, json_encode($topics, JSON_PRETTY_PRINT));

        return $topics;
    }

    private function topics()
    {
        $config = [];
        $this->topicsInclude(__DIR__.'/../available/topics.yml', $config);
        $this->bundleTopicsInclude($config);
        foreach (glob($this->root.'/../config/topics/*.yml') as $filename) {
            $this->topicsInclude($filename, $config);
        }

        return $config;
    }

    private function topicsInclude($file, &$config)
    {
        $topics = $this->topicsRead($file);
        if (!isset($topics['topics']) || !is_array($topics['topics']) || count($topics['topics']) == 0) {
            return;
        }
        foreach ($topics['topics'] as $name => $topic) {
            $config['topics'][$name] = $topic;
        }
    }

    private function bundleTopicsInclude(&$config)
    {
        $bundles = $this->bundleModel->bundles();
        if (!is_array($bundles) || count($bundles) == 0) {
            return;
        }
        foreach ($bundles as $bundleName => $bundle) {
            foreach (glob($bundle['root'].'/../config/topics/*.yml') as $filename) {
                $this->topicsInclude($filename, $config);
            }
        }
    }

    private function topicsRead($topicConfig)
    {
        if (!file_exists($topicConfig)) {
            return;
        }
        $config = Yaml::parse(file_get_contents($topicConfig));
        if ($config == false) {
            throw new Exception('Can not parse YAML file: '.$topicConfig);
        }

        return $config;
    }
}
