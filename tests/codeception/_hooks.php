<?php

if (! function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1)
    {
        if ('plugins_loaded' === $hook) {
            call_user_func($callback);
        }
    }
}

if (! function_exists('do_action')) {
    function do_action($hook)
    {
    }
}
