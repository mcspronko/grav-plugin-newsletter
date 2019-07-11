<?php

namespace Grav\Plugin\Newsletter;

use Grav\Common\Grav;
use Grav\Plugin\Form;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;
use Grav\Common\Twig\Twig;

class SubscribeController
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
     * @var Form
     */
    public $form;
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
     * @var Newsletter
     */
    protected $newsletter;

    /**
     * @var array
     */
    protected $params;

    /**
     * SubscribeController constructor.
     * @param Grav $grav
     * @param $action
     * @param $form
     * @param $params
     * @param $newsletter
     */
    public function __construct(Grav $grav, $action, $form, $params, $newsletter)
    {
        $this->grav = $grav;
        $this->action = $action;
        $this->form = $form;
        $this->params = $params;
        $this->newsletter = $newsletter;
    }

    public function execute()
    {
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

        return $success;
    }

    public function doSubscribe()
    {
        $prefix = !empty($params['fileprefix']) ? $params['fileprefix'] : '';
        $format = !empty($params['dateformat']) ? $params['dateformat'] : 'Ymd-His-u';
        $ext = !empty($params['extension']) ? '.' . trim($params['extension'], '.') : '.txt';
        $filename = !empty($params['filename']) ? $params['filename'] : '';
        $operation = !empty($params['operation']) ? $params['operation'] : 'create';

        if (!$filename) {
            $filename = $prefix . $this->udate($format) . $ext;
        }

        /** @var Twig $twig */
        $twig = $this->grav['twig'];
        $vars = array(
            'form' => $this->form
        );

        $locator = $this->grav['locator'];
        $path = $locator->findResource('user://data', true);
        $fullFileName = $path . DS . $this->form->name . DS . $filename;

        $file = File::instance($fullFileName);

        if ($operation == 'create') {
            $body = $twig->processString(
                !empty($params['body']) ? $params['body'] : '{% include "forms/subscriber.txt.twig" %}',
                $vars
            );
            $file->save($body);
        } elseif ($operation == 'add') {
            $vars = $vars['form']->value()->toArray();

            foreach ($this->form->fields as $field) {
                if (isset($field['process']) && isset($field['process']['ignore']) && $field['process']['ignore']) {
                    unset($vars[$field['name']]);
                }
            }

            if (file_exists($fullFileName)) {
                $data = Yaml::parse($file->content());
                if (count($data) > 0) {
                    array_unshift($data, $vars);
                } else {
                    $data[] = $vars;
                }
            } else {
                $data[] = $vars;
            }

            $file->save(Yaml::dump($data));
        }

        $this->setMessage(isset($this->params['success_message']) ? $this->params['success_message'] : 'Thank you!');
    }


    /**
     * Add message into the session queue.
     *
     * @param string $msg
     * @param string $type
     */
    public function setMessage($msg, $type = 'info')
    {
        $this->newsletter->json_response = [
            'type' => 'success',
            'message' => $msg
        ];
    }

    /**
     * Create unix timestamp for storing the data into the filesystem.
     *
     * @param string $format
     * @param int $utimestamp
     * @return string
     */
    private function udate($format = 'u', $utimestamp = null)
    {
        if (is_null($utimestamp)) {
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', \sprintf('%06d', $milliseconds), $format), $timestamp);
    }
}