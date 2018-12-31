<?php

namespace Bloomkit\Core\Template\Tests;

use PHPUnit\Framework\TestCase;
use Bloomkit\Core\Http\HttpApplication;

class TemplateManagerTest extends TestCase
{
    public function testGetTemplate()
    {
        $app = new HttpApplication();
        $templateMngr = $app->getTemplateManager();
        $template = $templateMngr->getTemplate();
        $this->assertEquals($template, 'default');
    }
}
