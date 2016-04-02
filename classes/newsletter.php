<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Data;
use Grav\Common\Grav;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use Grav\Common\File\CompiledYamlFile;
use RocketTheme\Toolbox\Session\Message;

class Newsletter
{
    protected $grav;

    public function __construct(Grav $grav)
    {
        $this->grav = $grav;
    }

    public function subscribers()
    {
        // Initialize subscriber class.
        require_once __DIR__ . '/subscriber.php';

        /** @var ResourceLocatorInterface $locator */
        $locator = $this->grav['locator'];

        $dataDir = $locator->findResource('user://data/newsletter/subscribers');
        $fullPath = $dataDir;
        $iterator = new \DirectoryIterator($fullPath);

        $subscribers = [];
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $name = $file->getBasename();
            $subscribers[$name] = CompiledYamlFile::instance($dataDir . DS . $name)->content();
        }

        return $subscribers;
    }

    public function data($subscriber)
    {
        $obj = new Subscriber($subscriber);

        return $obj;
    }

    public function setMessage($msg, $type = 'info')
    {
        /** @var Message $messages */
        $messages = $this->grav['messages'];
        $messages->add($msg, $type);
    }
}