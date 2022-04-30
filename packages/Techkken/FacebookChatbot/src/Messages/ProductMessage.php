<?php

namespace Techkken\FacebookChatbot\Messages;
use Illuminate\Support\Facades\Log;
use \pimax\Messages\Message as Message;
use \pimax\Messages\Attachment;
use \Techkken\FacebookChatbot\Messages\Payload\Element\Product as ProductElement;
use Webkul\Product\Repositories\ProductRepository;
/**
 * Class ImageMessage
 *
 * @package pimax\Messages
 */
class ProductMessage extends Message
{
    /***  Webkul\Product\Model\Product;
     **$productlist  
     */
    protected $productList;



    public function __construct($recipient,$productList)
    {
     $this->productList = $productList;
     $this->recipient = $recipient;
    }

    /**
     * Get message data
     *
     * @return array
     */
    public function getData()
    {
        $_elements = array();
        
        foreach ($this->productList as $product) {
            $_element = new \Techkken\FacebookChatbot\Messages\Payload\Element\Product($product,[]);
            array_push($_elements,$_element->getData());
        }

        $res = [
            'recipient' =>  [
                'id' => $this->recipient
            ]
        ];
  
        $_payload = [
            "template_type" => "generic",
            "elements" => $_elements
        ];


        $attachment = new Attachment('template',$_payload,[]);
        $res['message'] = $attachment->getData();

    
        return $res;
    }
}
