<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Grav;
use RocketTheme\Toolbox\File\MarkdownFile;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use \FilesystemIterator;

/**
 * Class SubscribersProvider
 */
class SubscribersProvider
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

    /**
     * @return array
     */
    public function get()
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->grav['locator'];

        $fullPath = $locator->findResource('user://' . $this->grav['config']['plugins.newsletter.data_dir.subscribers']);
        $iterator = new FilesystemIterator($fullPath);

        $subscribers = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $filename = $file->getFilename();
            $content = MarkdownFile::instance($file->getPathname());
            $subscribers[$filename] = $content->content();
        }

        return $subscribers;
    }
}
