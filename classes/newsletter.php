<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Grav;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use Grav\Common\File\CompiledYamlFile;
use RocketTheme\Toolbox\Session\Message;

/**
 * Class Newsletter
 */
class Newsletter
{
    /**
     * @var Grav
     */
    protected $grav;

    /**
     * Newsletter constructor.
     * @param Grav $grav
     */
    public function __construct(Grav $grav)
    {
        $this->grav = $grav;
    }

    public function subscribers()
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->grav['locator'];

        $fullPath = $locator->findResource('user://' . $this->grav['config']['plugins.newsletter.data_dir.subscribers']);
        $iterator = new \DirectoryIterator($fullPath);

        $subscribers = [];
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $name = $file->getBasename();
            $subscribers[$name] = CompiledYamlFile::instance($fullPath . DS . $name)->content();
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
