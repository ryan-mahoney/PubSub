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

class Model {
    private $root;
    private $yamlSlow;
    private $bundleModel;

    public function __construct ($root, $yamlSlow, $bundleModel) {
        $this->root = $root;
        $this->yamlSlow = $yamlSlow;
        $this->bundleModel = $bundleModel;
    }

    public function build () {
        return $this->topics();
    }

    public function topics () {
        $config = [];
        $this->topicsInclude(__DIR__ . '/../available/topics.yml', $config);
        $this->bundleTopicsInclude($config);
        $this->topicsInclude($this->root . '/../topics.yml', $config);
        return $config;
    }

    public function topicsInclude ($file, &$config) {
        $topics = $this->topicsRead($file);
        if (!isset($topics['topics']) || !is_array($topics['topics']) || count($topics['topics']) == 0) {
            return;
        }
        foreach ($topics['topics'] as $name => $topic) {
            $config['topics'][$name] = $topic;
        }
    }

    public function bundleTopicsInclude (&$config) {
        $bundles = $this->bundleModel->cacheRead();
        if (!is_array($bundles) || count($bundles) == 0) {
            return;
        }
        foreach ($bundles as $bundleName => $bundle) {
            $bundleTopics = $this->root . '/../bundles/' . $bundleName . '/topics.yml';
            if (!file_exists($bundleTopics)) {
                continue;
            }
            $this->topicsInclude($bundleTopics, $config);
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
            throw new Exception('Can not parse YAML file: ' . $topicConfig);
        }
        return $config;
    }
}