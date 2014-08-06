<?php

namespace m8rge\OCF;

use phpDocumentor\Reflection\DocBlock;

abstract class OCF
{
    const OCF_SUCCESS = 0;
    const OCF_ERR_GENERIC = 1;
    const OCF_ERR_ARGS = 2;
    const OCF_ERR_UNIMPLEMENTED = 3;
    const OCF_ERR_PERM = 4;
    const OCF_ERR_INSTALLED = 5;
    const OCF_ERR_CONFIGURED = 6;
    const OCF_NOT_RUNNING = 7;
    const OCF_RUNNING_MASTER = 8;
    const OCF_FAILED_MASTER = 9;

    protected $version = '0.1';
    protected $language = 'en';

    /**
     * Sentry dsn url
     * @var string
     */
    public $sentryDSN = '';

    /**
     * @var \Raven_Client
     */
    protected $ravenClient;

    public function initProperties()
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            if (isset($_ENV['OCF_RESKEY_' . $property->name])) {
                $this->{$property->name} = $_ENV['OCF_RESKEY_' . $property->name];
            }
        }
    }

    public function initSentry()
    {
        if ($this->sentryDSN) {
            $this->ravenClient = new \Raven_Client($this->sentryDSN);

            $error_handler = new \Raven_ErrorHandler($this->ravenClient);
            $error_handler->registerExceptionHandler();
            $error_handler->registerErrorHandler();
            $error_handler->registerShutdownFunction();
        }
    }

    /**
     * @param string $method
     */
    public function run($method)
    {
        $this->initProperties();
        $this->initSentry();

        $method = 'action-' . $method;
        $method = $this->convertToCamelCase($method);
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * @timeout 10
     */
    public function actionStart()
    {

    }

    /**
     * @timeout 10
     */
    public function actionStop()
    {

    }

    /**
     * @timeout 10
     * @interval 10
     */
    public function actionMonitor()
    {

    }

    /**
     * @timeout 5
     */
    public function actionValidateAll()
    {

    }

    public function actionMetaData()
    {
        $reflection = new \ReflectionClass($this);
        $classPhpDoc = new DocBlock($reflection->getDocComment());

        $ra = new \SimpleXMLElement('<!DOCTYPE resource-agent SYSTEM "ra-api-1.dtd"><resource-agent />');
        $ra->addAttribute('name', $reflection->getShortName());
        $ra->addAttribute('version', $this->version);
        $ra->addChild('version', $this->version);
        $this->setDescriptions($classPhpDoc, $ra);

        if ($properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC)) {
            $parameters = $ra->addChild('parameters');
            foreach ($properties as $property) {
                $propertyPhpDoc = new DocBlock($property->getDocComment());

                $parameter = $parameters->addChild('parameter');
                $parameter->addAttribute('name', $property->name);
                $unique = $this->getTag($propertyPhpDoc, 'unique');
                $parameter->addAttribute('unique', json_decode($unique) ? '1' : '0');
                $parameter->addAttribute('required', $property->getValue($this) === null ? '1' : '0');

                $this->setDescriptions($propertyPhpDoc, $parameter);

                if ($tags = $propertyPhpDoc->getTagsByName('var')) {
                    /** @var DocBlock\Tag\VarTag $tag */
                    $tag = reset($tags);
                    if ($tag instanceof DocBlock\Tag\VarTag) {
                        $content = $parameter->addChild('content');
                        $content->addAttribute('type', $this->convertToOcfType($tag->getType()));
                        if ($property->getValue($this)) {
                            $content->addAttribute('default', $property->getValue($this));
                        }
                    }
                }
            }
        }

        $actions = $ra->addChild('actions');
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $declaringClass = $method->getDeclaringClass();
            if ($declaringClass->name != __CLASS__ && strpos($method->name, 'action') === 0) {
                $action = $actions->addChild('action');
                $actionName = str_replace('action', '', $method->name);
                $actionName[0] = strtolower($actionName[0]);
                $actionName = $this->convertFromCamelCase($actionName);
                $action->addAttribute('name', $actionName);

                $actionPhpDoc = new DocBlock($method);
                if ($timeout = $this->getTag($actionPhpDoc, 'timeout')) {
                    $action->addAttribute('timeout', $timeout);
                }
                if ($interval = $this->getTag($actionPhpDoc, 'interval')) {
                    $action->addAttribute('interval', $interval);
                }
            }
        }

        echo $ra->asXML();
    }

    /**
     * @param $string
     * @return string|null
     */
    protected function convertToCamelCase($string)
    {
        return preg_replace_callback('/-(\w)/', function ($matches) {
                return strtoupper($matches[1]);
            }, strtolower($string));
    }

    protected function convertFromCamelCase($string)
    {
        return preg_replace_callback('/([A-Z])/', function ($matches) {
                return '-' . strtolower($matches[1]);
            }, $string);
    }

    /**
     * @param string $phpDocType
     * @return string
     */
    protected function convertToOcfType($phpDocType)
    {
        $type = 'string';

        if ($phpDocType == 'int' || $phpDocType == 'integer') {
            $type = 'integer';
        } elseif ($phpDocType == 'bool' || $phpDocType == 'boolean') {
            $type = 'boolean';
        }

        return $type;
    }

    /**
     * @param DocBlock $docBlock
     * @param string $name
     * @return null|string
     */
    protected function getTag($docBlock, $name)
    {
        $tags = $docBlock->getTagsByName($name);
        if ($tags) {
            /** @var DocBlock\Tag $tag */
            $tag = reset($tags);
            return $tag->getContent();
        }

        return null;
    }

    /**
     * @param DocBlock $docBlock
     * @param \SimpleXMLElement $xmlElement
     */
    protected function setDescriptions($docBlock, $xmlElement)
    {
        if ($docBlock->getShortDescription()) {
            $shortDesc = $xmlElement->addChild('shortdesc', $docBlock->getShortDescription());
            $shortDesc->addAttribute('lang', $this->language);
        }
        if ($docBlock->getLongDescription()->getContents()) {
            $longDesc = $xmlElement->addChild('longdesc', $docBlock->getLongDescription()->getContents());
            $longDesc->addAttribute('lang', $this->language);
        }
    }
}