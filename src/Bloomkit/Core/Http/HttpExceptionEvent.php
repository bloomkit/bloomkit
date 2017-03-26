<?php
namespace Bloomkit\Core\Http;

class HttpExceptionEvent extends HttpEvent
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * Constructor
     *
     * @param HttpRequest   $request
     * @param \Exception    $exception
     */
    public function __construct(HttpRequest $request, \Exception $exception)
    {
        parent::__construct($request);
        $this->setException($exception);
    }

    /**
     * Returns the exception
     * 
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set the exception
     *
     * @param \Exception    $exception
     */
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
}
