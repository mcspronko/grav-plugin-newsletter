<?php

namespace Grav\Plugin;

use Grav\Common\Page\Collection;
use Grav\Common\Plugin;
use Grav\Common\Twig\Twig;
use Grav\Common\Uri;
use Grav\Common\Taxonomy;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Newsletter;

class NewsletterPlugin extends Plugin
{
    /**
     * @var Newsletter\Newsletter
     */
    protected $newsletter;

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 1000],
            'onTask.subscriber.enable' => ['subscriberController', 0],
            'onTask.subscriber.disable' => ['subscriberController', 0],
            'onFormProcessed' => ['createSubscriberController', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        // Autoload classes
        $autoload = __DIR__ . '/vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new \Exception('Newsletter Plugin failed to load. Composer dependencies not met.');
        }
        require_once $autoload;

        $this->newsletter = new Newsletter\Newsletter($this->grav);

        $route = $this->config->get('plugins.newsletter.admin.route');
        $icon = $this->config->get('plugins.newsletter.admin.menu_icon');

        /** @var Twig $twig */
        $twig = $this->grav['twig'];
        $twig->plugins_hooked_nav = [
            "PLUGIN_NEWSLETTER.MENU_LABEL"  => [
                'route' => $route,
                'icon' => $icon
            ]
        ];

//        if ($route && $route == $uri->path()) {
            $this->enable([
                'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            ]);
//        }
    }

    public function onAdminTwigTemplatePaths(Event $event)
    {
        $paths = $event['paths'];
        $paths[] = __DIR__ . '/themes/admin/templates';

        $event['paths'] = $paths;
    }

    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];

        $twig->twig_vars['newsletter'] = $this->newsletter;
    }

    public function subscriberController()
    {
        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $task = !empty($_POST['task']) ? $_POST['task'] : $uri->param('task');
        $task = substr($task, strlen('subscriber.'));
        $post = !empty($_POST) ? $_POST : $uri->params(null, true);

        if (method_exists('Grav\Common\Utils', 'getNonce')) {
            if ($task == 'enable') {
                if (!isset($post['login-form-nonce']) || !Utils::verifyNonce($post['login-form-nonce'], 'login-form')) {
                    $this->grav['messages']->add($this->grav['language']->translate('PLUGIN_LOGIN.ACCESS_DENIED'), 'info');
                    $this->authenticated = false;
                    $twig = $this->grav['twig'];
                    $twig->twig_vars['notAuthorized'] = true;
                    return;
                }
            }
        }
        $post['_redirect'] = 'admin/newsletter';
        $controller = new Newsletter\SubscriberController($this->grav, $task, $post);
        $controller->execute();
        $controller->redirect();
    }

    /**
     * Create subscriber
     *
     * @param Event $event
     */
    public function createSubscriberController(Event $event)
    {
        $form = $event['form'];
        $action = $event['action'];
        $params = $event['params'];

        switch ($action) {
            case 'subscribe':
                $controller = new Newsletter\SubscribeController($this->grav, $action, $form, $params, $this->newsletter);
                $controller->execute();
                break;
        }
    }
}