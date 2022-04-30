<?php

namespace Techkken\Messages\Payload;

/**
 * Class Attachment
 */
class Template
{
    const TEMPLATE_TYPE_GENERIC = 'generic';
    

    /**
     * @var array
     */
    private $elements =array();

    /**
     * @var string
     */
    private $template_type;

    
     
    /**
     * Attachment constructor.
     * @param string $template_type
     * @param array  $elements
     */
    public function __construct($template_type, $elements = array())
    {
        $this->template_type = $template_type;
        $this->elements = $elements;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->template_type;
    }

    /**
     * @param string $type
     */
    public function setType($template_type)
    {
        $this->template_type = $template_type;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $payload
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

 
    /**
     * @return array
     */
    public function getData()
    {
        $data = [
            'payload' => [
                'template_type' => $this->template_type,
                'elements' => $this->elements
            ]
        ];

        return $data;
    }
}
