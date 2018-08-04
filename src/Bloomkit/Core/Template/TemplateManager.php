<?php
namespace Bloomkit\Core\Template;

use Bloomkit\Core\Application\Application;

class TemplateManager
{
    /**
     * @var Application
     */
    private $application;
    
    /**
     * Construktor.
     *
     * @param Application $app Application object
     */
    public function __construct(Application $app)
    {
        $this->application = $app;
    }
    
    public function getTemplate()
    {
        return $this->application->config->get('app.template', 'default');
    }
    
    public function getTemplateBasePath()
    {
        return $this->application->getBasePath().'/templates';
    }
    
    public function getCurrentTemplatePath()
    {
        return $this->getTemplateBasePath().'/'.$this->getTemplate();
    }   
    
}
