<?php
/**
 * Opine\Topic
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

class Topic {
    private $topics = [];
    private $subscribers = [];
    private $container;
    private $cache = false;
    private $this->root;

    public function __construct ($container) {
        $this->container = $container;
    }

    public function cacheSet ($cache) {
        $this->cache = (array)$cache;
    }

    public function load () {
        if ($this->cache === false || !is_array($this->cache) || !isset($this->cache['topics']) || !is_array($this->cache['topics']) || count($this->cache['topics']) == 0) {
            return;
        }
        if (isset($this->cache['topics']) && is_array($this->cache['topics'])) {
            foreach ($this->cache['topics'] as $topic => $subscribers) {
                if (!is_array($subscribers)) {
                    continue;
                }
                foreach ($subscribers as $subscriber => $services) {
                    $this->topics[$topic][$subscriber] = $services;
                }
            }
        }
        $subscribersBuild = $this->root . '/../subscribers/_build.php';
        if (file_exists($subscribersBuild)) {
            $this->subscribers = require $subscribersBuild;
        }
    }
    
    public function show () {
        echo 'TOPICS: ', "\n";
        foreach ($this->topics as $key => $value) {
            echo $key, "\n";
        }
        echo 'SUBSCRIBERS:', "\n";
        foreach ($this->subscribers as $key => $value) {
            echo $key, "\n";
        }
    }

    public function subscribe ($topic, $callback, $services=[]) {
        if (!isset($this->topics[$topic])) {
            $this->topics[$topic] = [];
        }
        $this->topics[$topic][$callback] = $services;
    }

    public function subscriber ($name, $callback) {
        $this->subscribers[$name] = $callback;
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