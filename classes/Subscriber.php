<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Data\Data;
use Grav\Common\Grav;
use RocketTheme\Toolbox\File\MarkdownFile;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;

/**
 * Subscriber
 */
class Subscriber extends Data
{
    /**
     * @var Grav
     */
    protected $grav;

    /**
     * @var string
     */
    protected $filename;

    /**
     * Subscriber constructor.
     * @param Grav $grav
     * @param array $items
     * @param null $blueprints
     */
    public function __construct(Grav $grav, array $items = [], $blueprints = null)
    {
        $this->grav = $grav;
        parent::__construct($items, $blueprints);
    }

    /**
     * Get file object to the page.
     *
     * @return MarkdownFile|null
     */
    public function getFileObject()
    {
        if ($this->filename) {
            $filename = $this->getLocator()->findResource('user://' . $this->grav['config']['plugins.newsletter.data_dir.subscribers']);
            $filename .= '/' . $this->filename . '.md';
            return MarkdownFile::instance($filename);
        }

        return null;
    }

    /**
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * @return ResourceLocatorInterface
     */
    protected function getLocator()
    {
        return $this->grav['locator'];
    }
}
