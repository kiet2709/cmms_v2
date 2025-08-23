<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Router extends CI_Router
{
    /**
     * Override để ép CI3 nhận controller/method qua query string
     * cho mọi HTTP method (GET/POST/PUT/DELETE).
     */
    protected function _set_request($segments = array())
    {
        if ($this->enable_query_strings)
        {
            $controller = isset($_REQUEST[$this->config->item('controller_trigger')])
                ? $_REQUEST[$this->config->item('controller_trigger')] : null;

            $method = isset($_REQUEST[$this->config->item('function_trigger')])
                ? $_REQUEST[$this->config->item('function_trigger')] : 'index';

            if ($controller)
            {
                $this->set_class($controller);
                $this->set_method($method);
                return;
            }
        }

        // fallback về logic mặc định
        parent::_set_request($segments);
    }
}
