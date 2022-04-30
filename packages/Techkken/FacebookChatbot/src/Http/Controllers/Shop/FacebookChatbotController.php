<?php

namespace Techkken\FacebookChatbot\Http\Controllers\Shop;

use Barryvdh\Debugbar\Twig\Extension\Debug;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use pimax\FbBotApp;
use pimax\Menu\MenuItem;
use pimax\Menu\LocalizedMenu;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use pimax\Messages\MessageReceiptElement;
use pimax\Messages\Address;
use pimax\Messages\Summary;
use pimax\Messages\Adjustment;
use pimax\Messages\AccountLink;
use pimax\Messages\ImageMessage;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use pimax\Messages\SenderAction;
use Webkul\Product\Repositories\ProductRepository;
use Techkken\FacebookChatbot\Helpers\Data as ChatBotHelper;
use Techkken\FacebookChatbot\Actions\CheckoutAction;
use Techkken\FacebookChatbot\Messages\Payload\Element\Product as PayloadElementProduct;
use Cart;

class FacebookChatbotController extends Controller
{
    const NEWLINE = '\n';
    /**
     * ProductRepository object
     *
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;


    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;


    protected $verify_token = ""; // Verify token
    protected $token = ""; // Page token


    protected $chatbotHelper;

    protected $customer;

    protected $jwtToken;

    protected $checkoutAction;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ProductRepository $productRepository,
        ChatBotHelper $chatbotHelper,
        CheckoutAction $checkoutAction
    ) {
        $this->productRepository = $productRepository;
        $this->verify_token = "techkken";
        $this->token = config('facebook.page_token');
        $this->bot = new FbBotApp($this->token);
        $this->chatbotHelper = $chatbotHelper;
        $this->jwtToken = null;
        $this->checkoutAction = $checkoutAction;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {

        if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $this->verify_token) {
            // // Webhook setup request
            Log::debug($_REQUEST['hub_verify_token']);
            Log::debug($_REQUEST['hub_challenge']);
            echo $_REQUEST['hub_challenge'];
            exit;
        } else {
            $this->receiveData();
        }

        //return view($this->_config['view']);
    }

    private function receiveData()
    {
        
        $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
        if (isset($data['entry'][0]['messaging'])) {
            foreach ($data['entry'][0]['messaging'] as $message) {

                //Login or Register
                $sender_id = $message['sender']['id'];
                if (!$this->chatbotHelper->checkIfCustomerExistsByFbId($sender_id)) {
                    $user = $this->bot->userProfile($sender_id);
                    $response = $this->chatbotHelper->registerCustomer($user, $sender_id);
                    if (!$response['success']) {
                        Log::critical("Error registration customer:" . $response->message);
                    } else {
                        $this->bot->send(new Message($message['sender']['id'], config('facebook.welcome_message')));
                    }
                } else {

                    //Log::debug($this->customer);

                }

                // Skipping delivery messages
                if (!empty($message['delivery'])) {
                    continue;
                }

                // skip the echo of my own messages
                if (isset($message['message']['is_echo'])) {
                    if (($message['message']['is_echo'] == "true")) {
                        continue;
                    }
                }


                $command = "";

                // When bot receive message from user
                if (!empty($message['message'])) {
                    if(isset($message['message']['text'])){
                        $command = trim($message['message']['text']);
                    }

                    //When bot quickreply
                    if(isset($message['message']['quick_reply'])){
                        $this->processQuickReplies($message);
                        continue;
                    }
                    
                    // When bot receive button click from user
                } elseif (!empty($message['postback'])) {
                    $this->processPostbackPayload($message);
                    continue;
                }

                switch ($command) {

                    case 'text':
                        $this->textReceiver($message);
                        break;

                    case 'products':
                        $this->getNewProducts($message);
                        break;

                    case 'setmenu':
                        Log::debug("setmenu"); //transfer this function to admin
                        $menuItems = array();
                        array_push($menuItems,  new MenuItem(MenuItem::TYPE_POSTBACK, 'Categories', 'DISPLAY_PRODUCTS_ACTION'));
                        array_push($menuItems,  new MenuItem(MenuItem::TYPE_POSTBACK, 'Search', 'SEARCH_ACTION'));
                        array_push($menuItems,  new MenuItem(MenuItem::TYPE_POSTBACK, 'Checkout', array('action' => CheckoutAction::ACTION, 'step'=>CheckoutAction::GET_ADDRESS_STEP)));
                        array_push($menuItems,  new MenuItem(MenuItem::TYPE_POSTBACK, 'View Cart', 'GET_CART_ACTION'));
                        
                        $reponse = $this->bot->setPersistentMenu([
                            new LocalizedMenu('default', false  ,$menuItems
                            )
                            
                        ]);
                        Log::debug($reponse);
                        break;

                    case 'setgetstarted':
                        Log::debug("set get started"); //transfer this function to admin
                        $this->bot->setGetStartedButton('GET_STARTED');
                        break;
                    default:
                        if (!empty($command)) // otherwise "empty message" wont be understood either
                        $this->defaultAction($message,"Sorry I can't understand. Please select the action you want.");
                }
            }
        }
    }
    private function textReceiver($message)
    {
        $this->bot->send(new Message($message['sender']['id'], 'This is a simple text message.'));
    }
    private function getNewProducts($message)
    {
        $newProducts =  $this->productRepository->getNewProducts();
        // Log::debug($message['sender']['id']);
        $reponse = $this->bot->send(new \Techkken\FacebookChatbot\Messages\ProductMessage($message['sender']['id'], $newProducts));
        // Log::debug($reponse);
    }

    private function processPostbackPayload($message)
    {
        $payload =  json_decode($message['postback']['payload'],true);

        if(isset($payload['action'])){
            $action = $payload['action'];
        }else{
            $action = ""; //empty  for default action
        }

        switch ($action) {

            case PayloadElementProduct::PRODUCT_PAYLOAD_ACTION_ADDTOCART:
                $this->productAddToCart($message);
                break;
            case CheckoutAction::ACTION:
              
                //Log::debug(CheckoutAction::getAddresses($this->getBagistoCustomer($message)));
                break;
            default:
                if (!empty($command)) // otherwise "empty message" wont be understood either
                    $this->defaultAction($message,"Sorry I can't understand. Please select the action you want.");

        }
    }
    private function processQuickReplies($message)
    {
        $payload =  json_decode($message['message']['quick_reply']['payload'],true);

        if(isset($payload['action'])){
            $action = $payload['action'];
        }else{
            $action = ""; //empty  for default action
        }

        switch ($action) {
            case CheckoutAction::ACTION:
                //Log::debug(json_encode(CheckoutAction::getAddresses($this->getBagistoCustomer($message))));
                $this->checkoutAction->executeStep(CheckoutAction::GET_ADDRESS_STEP,$this->getBagistoCustomer($message),$message['sender']['id']);
                break;
            default:
                if (!empty($command)) // otherwise "empty message" wont be understood either
                    $this->defaultAction($message,"Sorry I can't understand. Please select the action you want.");

        }
    }

    private function getBagistoCustomer($message)
    {
        if ($this->customer != null) {
            return $this->customer;
        } else {
            if (!$this->jwtToken = auth()->guard('customer')->attempt($this->chatbotHelper->getCreds($message['sender']['id']))) {
                Log::critical("Error Login customer:" . $message['sender']['id']);
            }

            $this->customer = auth('customer')->user();

            return $this->customer;
        }
    }

    private function productAddtoCart($message)
    {
        $this->getBagistoCustomer($message);
        $payload = json_decode($message['postback']['payload'], true);
        $cart = Cart::addProduct($payload['product_id'], array(
            'quantity' => 1,
            'product_id' => $payload['product_id']
        ));

        $productName = "";
        $productCount = 0;


        if (count($cart->items) > 0) {
            foreach ($cart->items as $item) {
                if ($item->product_id == $payload['product_id']) {
                    $productName = $item->name;
                    $productCount = $item->quantity;
                }
            }

            $responseMessage = "Product: ".$productName . " ( " . $productCount . " )  " . $this->newLine() . __(' Added to cart!');
            $this->bot->send(new Message($message['sender']['id'], $responseMessage));
        } else {
            $this->bot->send(new Message($message['sender']['id'], __('There is a problem in adding to cart')));
        }
    }


    private function defaultAction($message, $caption)
    {
       

        $quickReplyItems = array();
        array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Categories', json_encode(array('action' => ChatBotHelper::DISPLAY_PRODUCTS_ACTION))));
        array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Search',  json_encode(array('action' => ChatBotHelper::SEARCH_ACTION))));

        $checkoutPayLoad = array(
            'action' => CheckoutAction::ACTION,
            'step' => CheckoutAction::GET_ADDRESS_STEP
        );

        array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'Checkout',  json_encode($checkoutPayLoad)));
        array_push($quickReplyItems,  new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'View Cart',  json_encode(array('action' => ChatBotHelper::GET_CART_ACTION))));


        $this->bot->send(new QuickReply($message['sender']['id'], $caption,$quickReplyItems));

    }
    private function newLine()
    {
        return chr(10);
    }

}
