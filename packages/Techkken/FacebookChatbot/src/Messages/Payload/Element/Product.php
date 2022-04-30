<?php

namespace Techkken\FacebookChatbot\Messages\Payload\Element;
use \Webkul\Product\Models\ProductFlat as WebkulProduct;
use Webkul\Product\Facades\ProductImage;
use Illuminate\Support\Facades\Log;
/**
 * Class Attachment
 */
class Product
{
    public const PRODUCT_PAYLOAD_ACTION_ADDTOCART = "add_to_cart";
    public const PRODUCT_PAYLOAD_ACTION_ADDTOCART_TITLE = "Add to Cart";
    public const PRODUCT_PAYLOAD_ACTION_LEARNMORE = "learn_more";
    public const PRODUCT_PAYLOAD_ACTION_LEARNMORE_TITLE = "Learn More";
    /** @var WebkulProduct */
    protected $product;

    protected $buttons;

    /**
     * Attachment constructor.
     * @param WebkulProduct $product
     */
    public function __construct($product, $buttons = array())
    {
        $this->product = $product;
        $this->buttons = $buttons;
        
    }

 
    /**
     * @return array
     */
    public function getData()
    {
        $galleryImages = ProductImage::getGalleryImages($this->product);
        $productImage = ProductImage::getProductBaseImage($this->product, $galleryImages)['medium_image_url'];
       // Log::debug( ProductImage::getProductBaseImage($this->product, $galleryImages));
        $productImageLarge = ProductImage::getProductBaseImage($this->product, $galleryImages)['large_image_url'];
        $default_action =  array(); //default action shouuld be editable

        if(sizeof($this->buttons) == 0){
            $this->buttons = [];
            $buttonAddToCartPayload = [
                "action" => self::PRODUCT_PAYLOAD_ACTION_ADDTOCART,
                "product_id" => $this->product->id,
                "qty" => 1
            ];
            $buttonAddToCart = [
                "type" => "postback",
                "title" => __(self::PRODUCT_PAYLOAD_ACTION_ADDTOCART_TITLE),
                "payload"=> utf8_encode(json_encode($buttonAddToCartPayload))
            ];
            $buttonLearnMorePayload = [
                "action" => self::PRODUCT_PAYLOAD_ACTION_LEARNMORE,
                "product_id" => $this->product->id
            ];
            $buttonLearnMore = [
                "type" => "postback",
                "title" => __(self::PRODUCT_PAYLOAD_ACTION_LEARNMORE_TITLE),
                "payload"=> utf8_encode(json_encode($buttonLearnMorePayload))
            ];

            array_push($this->buttons,$buttonAddToCart);
            
            array_push($this->buttons,$buttonLearnMore);

        }


        $default_action = [  
            'type' => "web_url",
            'url' => $productImageLarge,
            'webview_height_ratio' => 'full',
        ];



        $data = [
                'title' => $this->product->name,
                'image_url' => $productImage,
                'subtitle' => strip_tags($this->product->getTypeInstance()->getPriceHtml()),
                'default_action' => $default_action,
                'buttons' => $this->buttons

        ];

        return $data;
    }
}
