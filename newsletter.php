<?php

namespace Grav\Plugin;

use Composer\Autoload\ClassLoader;
use Grav\Common\Page\Types;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Framework\Route\Route;
use Grav\Plugin\Admin\Admin;
use Grav\Plugin\Newsletter\SubscriberController;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Newsletter\SubscribersProvider;
use RocketTheme\Toolbox\ResourceLocator\ResourceLocatorInterface;
use RocketTheme\Toolbox\StreamWrapper\Stream;

/**
 * Class NewsletterPlugin
 */
class NewsletterPlugin extends Plugin
{
    /**
     * @var Admin
     */
    private $admin;

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
            'onGetPageBlueprints' => ['onGetPageBlueprints', 0],
            'onAdminTwigSiteVariables' => ['onAdminTwigSiteVariables', 0],
            'onTask.subscriber.enable' => ['subscriberController', 0],
            'onTask.subscriber.disable' => ['subscriberController', 0],
            'onFormProcessed' => ['createSubscriberController', 0],
            'onPageInitialized'    => ['onPageInitialized', 0],
        ];
    }

    /**
     * [onPluginsInitialized:1000] Composer autoload.
     *
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
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
     * Process POST operations
     */
    public function onPageInitialized()
    {
        $this->admin = $this->grav['admin'];

        // Handle tasks.
        $this->admin->task = $task = $this->grav['task'];
        if ($task) {
            // Make local copy of POST.
            $post = $this->grav['uri']->post();
            $this->initializeController($task, $post);
        }
    }

    /**
     * @param string $task
     * @param array $post
     */
    protected function initializeController($task, $post)
    {
        $controller = new SubscriberController();
        $controller->initialize($this->grav, $task, $post);
        $controller->execute();
        $controller->redirect();
    }

    /**
     * @param Event $event
     */
    public function onAdminTwigTemplatePaths(Event $event)
    {
        $event['paths'] = [__DIR__ . '/themes/admin/templates'];
    }

    /**
     * @param Event $event
     */
    public function onGetPageBlueprints(Event $event)
    {
        /** @var Types $types */
        $types = $event->types;
        $types->scanBlueprints('plugins://' . $this->name . '/blueprints');
    }

    /**
     * Load Twig variables
     */
    public function onTwigSiteVariables()
    {
        if ($this->isAdmin()) {
            $subscribers = new SubscribersProvider($this->grav);

            $this->getTwig()->plugins_hooked_nav = [
                "PLUGIN_NEWSLETTER.MENU_LABEL"  => [
                    'route' => $this->config->get('plugins.newsletter.admin.route'),
                    'icon' => $this->config->get('plugins.newsletter.admin.menu_icon'),
                    'badge' => [
                        'updates' => false,
                        'count' => count($subscribers->get())
                    ]
                ]
            ];

            /** @var Route $route */
            $route = $this->grav['route'];

            if ($route->getRoute() === '/admin/newsletter') {
                $subscribers = new SubscribersProvider($this->grav);
                $this->getTwig()->twig_vars['audience'] = $subscribers->get();
            }
        }
    }

    /**
     * @return Twig
     */
    protected function getTwig()
    {
        return $this->grav['twig'];
    }
}
