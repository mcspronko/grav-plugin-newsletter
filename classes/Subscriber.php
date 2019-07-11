<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Data\Data;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\Grav;
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
}