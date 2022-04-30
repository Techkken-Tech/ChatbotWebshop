<?php

namespace Techkken\FacebookChatbot\Helpers;

use Codeception\Extension\Logger;
use \Webkul\Customer\Models\Customer;
use \pimax\UserProfile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Webkul\Customer\Repositories\CustomerRepository;
use \Webkul\Customer\Repositories\CustomerGroupRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class Data
{
    const TYPE_POST = "POST";
    const TYPE_GET = "GET";
    const TYPE_DELETE = "DELETE";
    const DISPLAY_PRODUCTS_ACTION = "DISPLAY_PRODUCTS_ACTION";
    const SEARCH_ACTION = "SEARCH_ACTION";
    const CHECKOUT_ACTION = "CHECKOUT_ACTION";
    const GET_CART_ACTION = "GET_CART_ACTION";


    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerRepository
     */
    protected $customerRepository;


    /**
     * Repository object
     *
     * @var \Webkul\Customer\Repositories\CustomerGroupRepository
     */
    protected $customerGroupRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Customer\Repositories\CustomerRepository  $customerRepository
     * @param  \Webkul\Customer\Repositories\CustomerGroupRepository  $customerGroupRepository
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository,
        CustomerGroupRepository $customerGroupRepository
    ) {

        $this->customerRepository = $customerRepository;

        $this->customerGroupRepository = $customerGroupRepository;
    }

    public function checkIfCustomerExistsByFbId($sender_id)
    {

        $email = $sender_id . config('facebook.emailSuffix');

        $customerModel = new Customer();

        if ($customerModel->emailExists($email)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCustomerByFbId($sender_id)
    {

        $email = $sender_id . config('facebook.emailSuffix');

        $customerModel = new Customer();

        if ($customerModel->emailExists($email)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCreds($sender_id){
        $pw = $sender_id;
        return array(
            'email' => $sender_id . config('facebook.emailSuffix'),
            'password' => $sender_id
        );
    }
    /**
     * Register Customer
     *
     * @param UserProfile $userProfile
     * @param string $sender_id
     * @return json
     */
    public function registerCustomer($userProfile, $sender_id)
    {
        $pw = $sender_id;
     
        $data = [
            'email' => $sender_id . config('facebook.emailSuffix'),
            'first_name' => $userProfile->getFirstName(),
            'last_name' => $userProfile->getLastName(),
            'password' =>  bcrypt($pw),
            'password_confirmation' => bcrypt($pw),
            'is_verified' => 1,
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => 'general'])->id
        ];



        try {
            Event::dispatch('customer.registration.before');
            $customer = $this->customerRepository->create($data);
            Event::dispatch('customer.registration.after', $customer);
        } catch (\Exception $e) {
            return [
                'errorCode' => $e->getCode(),
                'message' => 'Cannot register account',
                'success' => false
            ];
        }



        return [
            'message' => 'Your account has been created successfully.',
            'success' => true
        ];
    }


}
