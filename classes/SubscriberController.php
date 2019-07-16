<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Grav;
use Grav\Plugin\Admin\AdminBaseController;
use RocketTheme\Toolbox\Event\Event;
use DateTime;
use Exception;

/**
 * Class SubscriberController
 */
class SubscriberController extends AdminBaseController
{
    /**
     * @var Grav
     */
    public $grav;

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
     * @param Grav $grav
     * @param string $task
     * @param array|null $post
     */
    public function initialize(Grav $grav, string $task, $post = null)
    {
        $this->grav = $grav;
        $this->task = $task;
        $this->post = $post;
        $this->admin = $grav['admin'];
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

    /**
     * Handles subscriber creation task
     *
     * @return bool True if the action was performed.
     */
    public function taskSaveNewSubscriber()
    {
        if (!$this->authorizeTask('saveNewSubscriber', $this->dataPermissions())) {
            return false;
        }

        $data = (array)$this->post['data'];
        $this->grav['twig']->twig_vars['current_form_data'] = $data;

        /** @var Subscriber $subscriber */
        $subscriber = new Subscriber($this->grav);

        try {
            $this->prepareSubscriber($subscriber);
            $subscriber->save();
//            $obj->validate();

        } catch (\Exception $e) {
            $this->admin->setMessage($e->getMessage(), 'error');

            return false;
        }

        if ($subscriber) {
            // Event to manipulate data before saving the object
            $this->grav->fireEvent('onAdminSave', new Event(['object' => &$subscriber]));
            $this->admin->setMessage($this->admin::translate('PLUGIN_ADMIN.SUCCESSFULLY_SAVED'), 'info');
            $this->grav->fireEvent('onAdminAfterSave', new Event(['object' => $subscriber]));
        }

        $this->setRedirect($this->admin->base . '/newsletter');

        return true;
    }

    /**
     * @param Subscriber $subscriber
     * @throws Exception
     */
    protected function prepareSubscriber(Subscriber $subscriber)
    {
        $input = (array)$this->post['data'];

        if (isset($input['email']) && !empty($input['email'])) {
            $filename = preg_replace('|.*/|', '', strtolower($input['email']));
            $subscriber->setFilename($filename);
        }

        if (isset($input['name'], $input['email'])) {
            $subscriber->merge(
                [
                    'header' => [
                        'name' => (string)$input['name'],
                        'email' => (string)$input['email'],
                        'is_subscribed' => true,
                        'created' => new DateTime()
                    ]
                ]
            );

            $subscriber->file($subscriber->getFileObject());
        }
    }
}
