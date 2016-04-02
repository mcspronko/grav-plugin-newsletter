<?php
namespace Grav\Plugin\Newsletter;

use Grav\Common\Data\Blueprints;
use Grav\Common\Data\Data;
use Grav\Common\File\CompiledYamlFile;
use Grav\Common\GravTrait;
use Grav\Common\Utils;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
/**
 * Subscriber object
 */
class Subscriber extends Data
{
    use GravTrait;

    /**
     * Load subscriber
     *
     * Always creates user object. To check if user exists, use $this->exists().
     *
     * @param string $username
     * @return Subscriber
     */
    public static function load($email, array $post = [])
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = self::getGrav()['locator'];

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