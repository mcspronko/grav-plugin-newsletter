<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Grav;
use Grav\Common\Utils;

class SubscriberController
{
    /**
     * @var \Grav\Common\Grav
     */
    public $grav;

    /**
     * @var string
     */
    public $action;

    /**
     * @var array
     */
    public $post;
    /**
     * @var string
     */
    protected $redirect;

    /**
     * @var int
     */
    protected $redirectCode;

    /**
     * @var string
     */
    protected $prefix = 'do';

    /**
     * @param Grav   $grav
     * @param string $action
     * @param array  $post
     */
    public function __construct(Grav $grav, $action, $post = null)
    {
        $this->grav = $grav;
        $this->action = $action;
        $this->post = $post;
    }

    public function execute()
    {
        // Set redirect if available.
        if (isset($this->post['_redirect'])) {
            $redirect = $this->post['_redirect'];
            unset($this->post['_redirect']);
        }

        $success = false;
        $method = $this->prefix . ucfirst($this->action);

        if (!method_exists($this, $method)) {
            throw new \RuntimeException('Page Not Found', 404);
        }

        try {
            $success = call_user_func([$this, $method]);
        } catch (\RuntimeException $e) {
            $this->setMessage($e->getMessage());
        }

        if (!$this->redirect && isset($redirect)) {
            $this->setRedirect($redirect);
        }

        return $success;
    }

    public function doEnable()
    {
        if (!isset($this->post['subscriber'])) {
            $this->setMessage('Email should be provided');
        }

        $email = $this->post['subscriber'];
        $subscriber = Subscriber::load($email, ['subscribed' => true]);
        if (!$subscriber) {
            $this->setMessage('Subscriber with given email does not exist.');
        }
        $subscriber->save();
        $this->setMessage('Subscriber has been enabled.');
    }

    public function doDisable()
    {
        if (!isset($this->post['subscriber'])) {
            $this->setMessage('Email should be provided');
        }

        $email = $this->post['subscriber'];
        $subscriber = Subscriber::load($email, ['subscribed' => false]);
        if (!$subscriber) {
            $this->setMessage('Subscriber with given email does not exist.');
        }
        $subscriber->save();
        $this->setMessage('Subscriber has been disabled.');
    }

    /**
     * Add message into the session queue.
     *
     * @param string $msg
     * @param string $type
     */
    public function setMessage($msg, $type = 'info')
    {
        /** @var Message $messages */
        $messages = $this->grav['messages'];
        $messages->add($msg, $type);
    }

    /**
     * Redirects an action
     */
    public function redirect()
    {
        $redirect = $this->grav['config']->get('plugins.newsletter.admin.subscriber.enable.redirect');
        if (!$redirect) {
            $this->grav->redirect($redirect, $this->redirectCode);
        } else if ($this->redirect) {
            $this->grav->redirect($this->redirect, $this->redirectCode);
        }
    }

    /**
     * Set redirect.
     *
     * @param $path
     * @param int $code
     */
    public function setRedirect($path, $code = 303)
    {
        $this->redirect = $path;
        $this->code = $code;
    }
}