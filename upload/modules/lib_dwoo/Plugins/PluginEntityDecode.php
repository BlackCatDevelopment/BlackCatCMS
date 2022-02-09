<?php

namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

class PluginEntityDecode extends Plugin
{
    public function process($string) {
        return html_entity_decode($string);
    }
}