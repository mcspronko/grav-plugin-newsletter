<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Data\Data;
use Grav\Common\File\CompiledYamlFile;
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
     * @param string $email
     * @param array $post
     * @return Subscriber
     */
    public function load($email, array $post = [])
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->grav['locator'];

        // force lowercase of username
        $email = strtolower($email);

        /** @var  $content */
        $filePath = $locator->findResource('user://data/newsletter/subscribers/' . $email . YAML_EXT);
        $file = CompiledYamlFile::instance($filePath);
        $subscriber = new Subscriber(array_merge($file->content(), $post));
        if ($subscriber) {
            $subscriber->file($file);
        }

        return $subscriber;
    }

    /**
     * Get file object to the page.
     *
     * @return MarkdownFile|null
     */
    public function getFileObject()
    {
        if ($this->filename) {
            $filename = $this->getLocator()->findResource('user://data/newsletter/subscribers/');
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
     * @return string
     */
    public function getFilePath()
    {
        return $this->getLocator()->findResource('user://data/newsletter/subscribers/' . $this->filename . YAML_EXT);
    }

    /**
     * @return ResourceLocatorInterface
     */
    protected function getLocator()
    {
        return $this->grav['locator'];
    }
}
