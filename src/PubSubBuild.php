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
namespace Opine;

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
        $config = [];
        $this->topicsInclude($this->root . '/../vendor/opine/pubsub/available/topics.yml', $config);
        $this->bundleTopicsInclude($config);
        $this->topicsInclude($this->root . '/../subscribers/topics.yml', $config);
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
        $this->standardSubscribersInclude($cache);
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

    private function standardSubscribersInclude (&$cache) {
        $subscribers = $this->root . '/../vendor/opine/pubsub/available';
        if (!file_exists($subscribers)) {
            return;
        }
        $files = glob($subscribers . '/*.php');
        if (!is_array($files) || count($files) == 0) {
            return;
        }
        foreach ($files as $subscriber) {
            $name = basename($subscriber, '.php');
            if ($name == '_build') {
                return;
            }
            $cache .= $this->subcriberRead($name, $subscriber);
        }
    }

    private function subcriberRead ($name, $path) {
        return "'" . $name . "' => " . substr(trim(ltrim(trim(file_get_contents($path)), '<?php')), 6, -1) . ',' . "\n\n";
    }
}