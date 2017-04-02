<?php
namespace Bloomkit\Core\Application;

interface ServiceProviderInterface
{    
    public function __construct(Application $app);
    
    public function register();
}