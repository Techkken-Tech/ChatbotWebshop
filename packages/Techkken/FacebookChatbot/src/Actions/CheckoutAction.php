<?php

namespace Techkken\FacebookChatbot\Actions;

use Webkul\Checkout\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use pimax\FbBotApp;
use pimax\Messages\Message;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use Webkul\API\Http\Resources\Customer\CustomerAddress as CustomerAddressResource;
use Webkul\Checkout\Repositories\CartRepository;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use Tymon\JWTAuth\Claims\Custom;


class CheckoutAction
{
    const ACTION = "CHECKOUT_ACTION";
    //steps
    const GET_ADDRESS_STEP = 0;
    const ADD_ADDRESS_STEP = 1;
    const SET_ADDRESS_STEP = 2;

    const ADDRESS_LIMIT = 5;

    const ADDRESSTYPE_CUSTOMER = 'customer';

    protected $cartRepository;

    protected $orderRepository;

    protected $customer = null;

    protected $sender_id = null;

    public function __construct(CartRepository $cartRepository, OrderRepository $orderRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->token = config('facebook.page_token');
        $this->bot = new FbBotApp($this->token);
    }
    /**
     * @param int $step
     */
    public function executeStep($step,$customer,$sender_id){
        if($this->customer == null){
            $this->customer = $customer;
        }
        if($this->sender_id == null){
            $this->sender_id = $sender_id;
        }
        if($step == self::GET_ADDRESS_STEP){
            $this->selectAddressToDeliver();
        }

    }

    public function saveOrder(){
        try {
            $order = $this->orderRepository->create($this->prepareDataForOrder($this->cart));
            return $order; 
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function displaySavedAddresses(){
        $addresses = $this->getAddresses($this->customer);
        if(count($addresses) == 0 ){ // No saved address 
            $quickReplyItems = array();
            $checkoutPayLoad = array(
                'action' => CheckoutAction::ACTION,
                'step' => CheckoutAction::ADD_ADDRESS_STEP
            );
            array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, __('Add Address'),  json_encode($checkoutPayLoad)));
            $this->bot->send(new QuickReply( $this->sender_id, __('Where should we deliver your order ? You have no saved address'),$quickReplyItems));
        }else{
            $quickReplyItems = array();
            foreach ($addresses as $address ) { 
                $addressPayload = array(
                    'action' => CheckoutAction::ACTION,
                    'step'  => CheckoutAction::SET_ADDRESS_STEP,
                    'addressId' => $address->id
                );  
                Log::debug($address->address1);
                array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, $address->address1,  json_encode($addressPayload)));
            }

            $checkoutPayLoad = array(
                'action' => CheckoutAction::ACTION,
                'step' => CheckoutAction::ADD_ADDRESS_STEP
            );
            array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, __('Add Address'),  json_encode($checkoutPayLoad)));
            $this->bot->send(new QuickReply( $this->sender_id, __('Where should we deliver your order?'),$quickReplyItems));
            
        }
        
    }

    private function selectAddressToDeliver(){ //
        $this->bot->send(new StructuredMessage( $this->sender_id,
        StructuredMessage::TYPE_GENERIC,
        [
            'elements' => [
                new MessageElement($this->customer->getFirstName()." ".$user->getLastName(), " ", $user->getPicture())
            ]
        ],
        [ 
            new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
        ]
    ));
    }

    private  function getAddresses($customer){
        $addresses = $customer->addresses()->get();
        return CustomerAddressResource::collection($addresses);
    }

    private function getCustomerDefaultAddress(){
        $addresses = $this->customer->addresses()->get();
        $_addresses = CustomerAddressResource::collection($addresses);
        foreach ($_addresses as $address ) {
            if($address->address_type == self::ADDRESSTYPE_CUSTOMER  && $address->default_address == 1 ){
                return $address;
            }
        }
    }

       /**
     * Prepare data for order.
     *
     * @return array
     */
    private function prepareDataForOrder($cart): array
    {
        $data = $this->toArray($cart);


        if(!empty($data['customer_id'])){
            $_customer =  $this->customerRepository->findOrFail($data['customer_id']);
        }else{
            $_customer = null;
        }
       
        $finalData = [
            'cart_id'               => $cart->id,
            'customer_id'           => $data['customer_id'],
            'is_guest'              => $data['is_guest'],
            'customer_email'        => $data['customer_email'],
            'customer_first_name'   => $data['customer_first_name'],
            'customer_last_name'    => $data['customer_last_name'],
            'customer'              => $_customer,
            'total_item_count'      => $data['items_count'],
            'total_qty_ordered'     => $data['items_qty'],
            'base_currency_code'    => $data['base_currency_code'],
            'channel_currency_code' => $data['channel_currency_code'],
            'order_currency_code'   => $data['cart_currency_code'],
            'grand_total'           => $data['grand_total'],
            'base_grand_total'      => $data['base_grand_total'],
            'sub_total'             => $data['sub_total'],
            'base_sub_total'        => $data['base_sub_total'],
            'tax_amount'            => $data['tax_total'],
            'base_tax_amount'       => $data['base_tax_total'],
            'coupon_code'           => $data['coupon_code'],
            'applied_cart_rule_ids' => $data['applied_cart_rule_ids'],
            'discount_amount'       => $data['discount_amount'],
            'base_discount_amount'  => $data['base_discount_amount'],
            'billing_address'       => Arr::except($data['billing_address'], ['id', 'cart_id']),
            'payment'               => Arr::except($data['payment'], ['id', 'cart_id']),
            'channel'               => core()->getCurrentChannel(),
        ];

        if ($cart->haveStockableItems()) {
            $finalData = array_merge($finalData, [
                'shipping_method'               => $data['selected_shipping_rate']['method'],
                'shipping_title'                => $data['selected_shipping_rate']['carrier_title'] . ' - ' . $data['selected_shipping_rate']['method_title'],
                'shipping_description'          => $data['selected_shipping_rate']['method_description'],
                'shipping_amount'               => $data['selected_shipping_rate']['price'],
                'base_shipping_amount'          => $data['selected_shipping_rate']['base_price'],
                'shipping_address'              => Arr::except($data['shipping_address'], ['id', 'cart_id']),
                'shipping_discount_amount'      => $data['selected_shipping_rate']['discount_amount'],
                'base_shipping_discount_amount' => $data['selected_shipping_rate']['base_discount_amount'],
            ]);
        }

        foreach ($data['items'] as $item) {
            $finalData['items'][] = $this->prepareDataForOrderItem($item);
        }

        return $finalData;
    }

    

    /**
     * Returns cart details in array.
     *
     * @return array
     */
    public function toArray($cart)
    {

        $data = $cart->toArray();

        $data['billing_address'] = $cart->billing_address->toArray();

        if ($cart->haveStockableItems()) {
            $data['shipping_address'] = $cart->shipping_address->toArray();

            $data['selected_shipping_rate'] = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->toArray() : 0.0;
        }

        $data['payment'] = $cart->payment->toArray();

        $data['items'] = $cart->items->toArray();

        return $data;
    }

    
    /**
     * Prepares data for order item.
     *
     * @param  array  $data
     * @return array
     */
    public function prepareDataForOrderItem($data): array
    {
        $locale = ['locale' => core()->getCurrentLocale()->code];

        $finalData = [
            'product'              => $this->productRepository->find($data['product_id']),
            'sku'                  => $data['sku'],
            'type'                 => $data['type'],
            'name'                 => $data['name'],
            'weight'               => $data['weight'],
            'total_weight'         => $data['total_weight'],
            'qty_ordered'          => $data['quantity'],
            'price'                => $data['price'],
            'base_price'           => $data['base_price'],
            'total'                => $data['total'],
            'base_total'           => $data['base_total'],
            'tax_percent'          => $data['tax_percent'],
            'tax_amount'           => $data['tax_amount'],
            'base_tax_amount'      => $data['base_tax_amount'],
            'discount_percent'     => $data['discount_percent'],
            'discount_amount'      => $data['discount_amount'],
            'base_discount_amount' => $data['base_discount_amount'],
            'additional'           => is_array($data['additional']) ? array_merge($data['additional'],$locale) : $locale,
        ];

        if (isset($data['children']) && $data['children']) {
            foreach ($data['children'] as $child) {
                $child['quantity'] = $child['quantity'] ? $child['quantity'] * $data['quantity'] : $child['quantity'];

                $finalData['children'][] = $this->prepareDataForOrderItem($child);
            }
        }

        return $finalData;
    }

    
}