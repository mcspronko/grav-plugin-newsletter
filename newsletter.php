<?php

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Common\Uri;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Newsletter\Newsletter as CustomNewsletter;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use RocketTheme\Toolbox\StreamWrapper\Stream;

/**
 * Class NewsletterPlugin
 */
class NewsletterPlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100001],
                ['setup', 100000],
                ['onPluginsInitialized', 1000]
            ],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
            'onTwigInitialized' => ['onTwigInitialized', 0],
            'onAdminTwigSiteVariables' => ['onAdminTwigSiteVariables', 0],
            'onTask.subscriber.enable' => ['subscriberController', 0],
            'onTask.subscriber.disable' => ['subscriberController', 0],
            'onFormProcessed' => ['createSubscriberController', 0]
        ];
    }

    /**
     * [onPluginsInitialized:1000] Composer autoload.
     *
     * @return ClassLoader
     */
    public function autoload()
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Creates data directory for the newsletter
     */
    public function setup()
    {
        /** @var ResourceLocatorInterface $locator */
        $locator = $this->grav['locator'];
        $subscribersDirectory = 'user://' . $this->config['plugins.newsletter.data_dir.subscribers'];

        if (!$locator->findResource($subscribersDirectory)) {
            $stream = new Stream();
            $stream->mkdir($subscribersDirectory, 0770, true);
        }
    }

    /**
     * Plugin initialization
     */
    public function onPluginsInitialized()
    {

    }

    public function onTwigInitialized()
    {
        if ($this->isAdmin()) {
        }
    }

    /**
     * @param Event $event
     */
    public function onAdminTwigTemplatePaths(Event $event)
    {
        $event['paths'] = [__DIR__ . '/themes/admin/templates'];
    }

    /**
     * Load Twig variables
     */
    public function onTwigSiteVariables()
    {
        /** @var Twig $twig */
        $twig = $this->grav['twig'];

        if ($this->isAdmin()) {
            $twig->plugins_hooked_nav = [
                "PLUGIN_NEWSLETTER.MENU_LABEL"  => [
                    'route' => $this->config->get('plugins.newsletter.admin.route'),
                    'icon' => $this->config->get('plugins.newsletter.admin.menu_icon')
                ]
            ];

            $subscribers = new CustomNewsletter($this->grav);
            $twig->twig_vars['audience'] = $subscribers->subscribers();
        }
    }
}
