<?php

/**
 * Erede Gateway.
 */
namespace Omnipay\Erede;

use Omnipay\Common\AbstractGateway;
use Omnipay\Erede\Message\CreateTokenRequest;

/**
 * Erede Gateway.
 *
 * Example:
 *
 * <code>
 *   // Create a gateway for the Erede Gateway
 *   // (routes to GatewayFactory::create)
 *   $gateway = Omnipay::create('Erede');
 *
 *   // Initialise the gateway
 *   $gateway->initialize(array(
 *       'apiKey' => 'MyApiKey',
 *   ));
 *
 *   // Create a credit card object
 *   // This card can be used for testing.
 *   $card = new CreditCard(array(
 *               'firstName'    => 'Example',
 *               'lastName'     => 'Customer',
 *               'number'       => '4242424242424242',
 *               'expiryMonth'  => '01',
 *               'expiryYear'   => '2020',
 *               'cvv'          => '123',
 *               'email'                 => 'customer@example.com',
 *               'billingAddress1'       => '1 Scrubby Creek Road',
 *               'billingCountry'        => 'AU',
 *               'billingCity'           => 'Scrubby Creek',
 *               'billingPostcode'       => '4999',
 *               'billingState'          => 'QLD',
 *   ));
 *
 *   // Do a purchase transaction on the gateway
 *   $transaction = $gateway->purchase(array(
 *       'amount'                   => '10.00',
 *       'currency'                 => 'USD',
 *       'card'                     => $card,
 *   ));
 *   $response = $transaction->send();
 *   if ($response->isSuccessful()) {
 *       echo "Purchase transaction was successful!\n";
 *       $sale_id = $response->getTransactionReference();
 *       echo "Transaction reference = " . $sale_id . "\n";
 *
 *       $balance_transaction_id = $response->getBalanceTransactionReference();
 *       echo "Balance Transaction reference = " . $balance_transaction_id . "\n";
 *   }
 * </code>
 *
 * Test modes:
 *
 * Erede accounts have test-mode API keys as well as live-mode
 * API keys. These keys can be active at the same time. Data
 * created with test-mode credentials will never hit the credit
 * card networks and will never cost anyone money.
 *
 * Unlike some gateways, there is no test mode endpoint separate
 * to the live mode endpoint, the Erede API endpoint is the same
 * for test and for live.
 *
 * Setting the testMode flag on this gateway has no effect.  To
 * use test mode just use your test mode API key.
 *
 * You can use any of the cards listed at https://Erede.com/docs/testing
 * for testing.
 *
 * Authentication:
 *
 * Authentication is by means of a single secret API key set as
 * the apiKey parameter when creating the gateway object.
 *
 * @see \Omnipay\Common\AbstractGateway
 * @see \Omnipay\Erede\Message\AbstractRequest
 *
 * @link https://Erede.com/docs/api
 *
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = array())
 * @method \Omnipay\Common\Message\RequestInterface completePurchase(array $options = array())
 */
class Gateway extends AbstractGateway
{
    const LIVE_ENDPOINT = 'https://api.userede.com.br/erede/v1';
    const TEST_ENDPOINT = 'https://api-hom.userede.com.br/erede/v1';
    const KEY_MERCHANT_ID  = 'merchantId';
    const KEY_MERCHANT_KEY = 'merchantKey';

    public function getName()
    {
        return 'Erede Gateway';
    }

    public function getShortName()
    {
        return 'Erede';
    }

    /**
     * Get the gateway parameters.
     *
     * @return array
     */
    public function getDefaultParameters()
    {
        if ($this->getTestMode()) {
            return [
                self::KEY_MERCHANT_ID  => '50079557',
                'testMode'    => true,
                self::KEY_MERCHANT_KEY => '4913bb24a0284954be72c4258e229b86',
                'installments' => 1,
                'capture'      => true,
                'kind'         => 'credit',
                'origin'       => 1
            ];
        }
        return [
            self::KEY_MERCHANT_ID => null,
            'testMode'   => false,
            self::KEY_MERCHANT_KEY => null,
            'installments' => 1,
            'capture'      => true,
            'kind'         => 'credit',
            'origin'       => 1
        ];
        // return array(
        //     'apiKey' => '',
        //     'username' => '',
        //     'password' => '',
        //     'testMode' => false
        // );
    }

    /**
     * Get the gateway API Key.
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key.
     *
     * @param string $value
     *
     * @return Gateway provides a fluent interface.
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    public function encodeCredentials($merchantId, $merchantKey)
    {
        return base64_encode($merchantId . ':' . $merchantKey);
    }

    public function setMerchantId($merchantId)
    {
        $this->setParameter(self::KEY_MERCHANT_ID, $merchantId);
    }

    public function getMerchantId()
    {
        return $this->getParameter(self::KEY_MERCHANT_ID);
    }

    public function setMerchantKey($merchantKey)
    {
        $this->setParameter(self::KEY_MERCHANT_KEY, $merchantKey);
    }

    public function getMerchantKey()
    {
        return $this->getParameter(self::KEY_MERCHANT_KEY);
    }

    /**
     * Create an authorize request.
     *
     * An Authorize request is similar to a purchase request but the
     * charge issues an authorization (or pre-authorization), and no money
     * is transferred.  The transaction will need to be captured later
     * in order to effect payment. Uncaptured charges expire in 7 days.
     *
     * Either a customerReference or a card is required.  If a customerReference
     * is passed in then the cardReference must be the reference of a card
     * assigned to the customer.  Otherwise, if you do not pass a customer ID,
     * the card you provide must either be a token, like the ones returned by
     * Erede.js, or a dictionary containing a user's credit card details.
     *
     * IN OTHER WORDS: You cannot just pass a card reference into this request,
     * you must also provide a customer reference if you want to use a stored
     * card.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\AuthorizeRequest
     */
    public function authorize(array $parameters = [])
    {
        return $this->createRequest(
            Message\AuthorizationRequest::class,
            $parameters
        );
    }

    /**
     * Capture Request.
     *
     * Use this request to capture and process a previously created authorization.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\CaptureRequest
     */
    public function capture(array $parameters = [])
    {
        return $this->createRequest(
            \Omnipay\Rede\Message\CaptureRequest::class,
            $parameters
        );
    }

    /**
     * Create an purchase request.
     *
     * To charge a credit card, you create a new charge object. If your API key
     * is in test mode, the supplied card won't actually be charged, though
     * everything else will occur as if in live mode. (Erede assumes that the
     * charge would have completed successfully).
     *
     * Either a customerReference or a card is required.  If a customerReference
     * is passed in then the cardReference must be the reference of a card
     * assigned to the customer.  Otherwise, if you do not pass a customer ID,
     * the card you provide must either be a token, like the ones returned by
     * Erede.js, or a dictionary containing a user's credit card details.
     *
     * IN OTHER WORDS: You cannot just pass a card reference into this request,
     * you must also provide a customer reference if you want to use a stored
     * card.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest(
            Message\PurchaseRequest::class,
            $parameters
        );
    }
    /**
     * Create a refund request.
     *
     * When you create a new refund, you must specify a
     * charge to create it on.
     *
     * Creating a new refund will refund a charge that has
     * previously been created but not yet refunded. Funds will
     * be refunded to the credit or debit card that was originally
     * charged. The fees you were originally charged are also
     * refunded.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\RefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest(
            \Omnipay\Rede\Message\RefundRequest::class,
            $parameters
        );
    }

    /**
     * Fetch Transaction Request.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\VoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\VoidRequest', $parameters);
    }

    /**
     * Create a fetch transaction request.
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchTransactionRequest
     */
    public function fetchTransaction(array $parameters = [])
    {
        return $this->createRequest(
            \Omnipay\Erede\Message\FetchTransactionRequest::class,
            $parameters
        );
    }

    //
    // Cards
    // @link https://Erede.com/docs/api#cards
    //

    /**
     * Create Card.
     *
     * This call can be used to create a new customer or add a card
     * to an existing customer.  If a customerReference is passed in then
     * a card is added to an existing customer.  If there is no
     * customerReference passed in then a new customer is created.  The
     * response in that case will then contain both a customer token
     * and a card token, and is essentially the same as CreateCustomerRequest
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\CreateCardRequest
     */
    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreateCardRequest', $parameters);
    }

    /**
     * Update Card.
     *
     * If you need to update only some card details, like the billing
     * address or expiration date, you can do so without having to re-enter
     * the full card details. Erede also works directly with card networks
     * so that your customers can continue using your service without
     * interruption.
     *
     * When you update a card, Erede will automatically validate the card.
     *
     * This requires both a customerReference and a cardReference.
     *
     * @link https://Erede.com/docs/api#update_card
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\UpdateCardRequest
     */
    public function updateCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\UpdateCardRequest', $parameters);
    }

    /**
     * Delete a card.
     *
     * This is normally used to delete a credit card from an existing
     * customer.
     *
     * You can delete cards from a customer or recipient. If you delete a
     * card that is currently the default card on a customer or recipient,
     * the most recently added card will be used as the new default. If you
     * delete the last remaining card on a customer or recipient, the
     * default_card attribute on the card's owner will become null.
     *
     * Note that for cards belonging to customers, you may want to prevent
     * customers on paid subscriptions from deleting all cards on file so
     * that there is at least one default card for the next invoice payment
     * attempt.
     *
     * In deference to the previous incarnation of this gateway, where
     * all CreateCard requests added a new customer and the customer ID
     * was used as the card ID, if a cardReference is passed in but no
     * customerReference then we assume that the cardReference is in fact
     * a customerReference and delete the customer.  This might be
     * dangerous but it's the best way to ensure backwards compatibility.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\DeleteCardRequest
     */
    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\DeleteCardRequest', $parameters);
    }

    //
    // Customers
    // link: https://Erede.com/docs/api#customers
    //

    /**
     * Create Customer.
     *
     * Customer objects allow you to perform recurring charges and
     * track multiple charges that are associated with the same customer.
     * The API allows you to create, delete, and update your customers.
     * You can retrieve individual customers as well as a list of all of
     * your customers.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\CreateCustomerRequest
     */
    public function createCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreateCustomerRequest', $parameters);
    }

    /**
     * Fetch Customer.
     *
     * Fetches customer by customer reference.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\FetchCustomerRequest
     */
    public function fetchCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchCustomerRequest', $parameters);
    }

    /**
     * Update Customer.
     *
     * This request updates the specified customer by setting the values
     * of the parameters passed. Any parameters not provided will be left
     * unchanged. For example, if you pass the card parameter, that becomes
     * the customer's active card to be used for all charges in the future,
     * and the customer email address is updated to the email address
     * on the card. When you update a customer to a new valid card: for
     * each of the customer's current subscriptions, if the subscription
     * is in the `past_due` state, then the latest unpaid, unclosed
     * invoice for the subscription will be retried (note that this retry
     * will not count as an automatic retry, and will not affect the next
     * regularly scheduled payment for the invoice). (Note also that no
     * invoices pertaining to subscriptions in the `unpaid` state, or
     * invoices pertaining to canceled subscriptions, will be retried as
     * a result of updating the customer's card.)
     *
     * This request accepts mostly the same arguments as the customer
     * creation call.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\CreateCustomerRequest
     */
    public function updateCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\UpdateCustomerRequest', $parameters);
    }

    /**
     * Delete a customer.
     *
     * Permanently deletes a customer. It cannot be undone. Also immediately
     * cancels any active subscriptions on the customer.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\DeleteCustomerRequest
     */
    public function deleteCustomer(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\DeleteCustomerRequest', $parameters);
    }

    //
    // Tokens
    // @link https://Erede.com/docs/api#tokens
    //

    /**
     * Creates a single use token that wraps the details of a credit card.
     * This token can be used in place of a credit card associative array with any API method.
     * These tokens can only be used once: by creating a new charge object, or attaching them to a customer.
     *
     * This kind of token is also useful when sharing clients between one platform and a connect account.
     * Use this request to create a new token to make a direct charge on a customer of the platform.
     *
     * @param array $parameters parameters to be passed in to the TokenRequest.
     * @return CreateTokenRequest|\Omnipay\Common\Message\AbstractRequest The create token request.
     */
    public function createToken(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreateTokenRequest', $parameters);
    }

    /**
     * Erede Fetch Token Request.
     *
     * Often you want to be able to charge credit cards or send payments
     * to bank accounts without having to hold sensitive card information
     * on your own servers. Erede.js makes this easy in the browser, but
     * you can use the same technique in other environments with our token API.
     *
     * Tokens can be created with your publishable API key, which can safely
     * be embedded in downloadable applications like iPhone and Android apps.
     * You can then use a token anywhere in our API that a card or bank account
     * is accepted. Note that tokens are not meant to be stored or used more
     * than once—to store these details for use later, you should create
     * Customer or Recipient objects.
     *
     * @param array $parameters
     *
     * @return \Omnipay\Erede\Message\FetchTokenRequest
     */
    public function fetchToken(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchTokenRequest', $parameters);
    }

    /**
     * Create Plan
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\CreatePlanRequest
     */
    public function createPlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreatePlanRequest', $parameters);
    }

    /**
     * Fetch Plan
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchPlanRequest
     */
    public function fetchPlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchPlanRequest', $parameters);
    }

    /**
     * Delete Plan
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\DeletePlanRequest
     */
    public function deletePlan(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\DeletePlanRequest', $parameters);
    }

    /**
     * List Plans
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\ListPlansRequest
     */
    public function listPlans(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\ListPlansRequest', $parameters);
    }

    /**
     * Create Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\CreateSubscriptionRequest
     */
    public function createSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreateSubscriptionRequest', $parameters);
    }

    /**
     * Fetch Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchSubscriptionRequest
     */
    public function fetchSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchSubscriptionRequest', $parameters);
    }

    /**
     * Update Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\UpdateSubscriptionRequest
     */
    public function updateSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\UpdateSubscriptionRequest', $parameters);
    }

    /**
     * Cancel Subscription
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\CancelSubscriptionRequest
     */
    public function cancelSubscription(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CancelSubscriptionRequest', $parameters);
    }

    /**
     * Fetch Event
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchEventRequest
     */
    public function fetchEvent(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchEventRequest', $parameters);
    }

    /**
     * Fetch Invoice Lines
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchInvoiceLinesRequest
     */
    public function fetchInvoiceLines(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchInvoiceLinesRequest', $parameters);
    }

    /**
     * Fetch Invoice
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchInvoiceRequest
     */
    public function fetchInvoice(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchInvoiceRequest', $parameters);
    }

    /**
     * List Invoices
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\ListInvoicesRequest
     */
    public function listInvoices(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\ListInvoicesRequest', $parameters);
    }

    /**
     * Create Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\CreateInvoiceItemRequest
     */
    public function createInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\CreateInvoiceItemRequest', $parameters);
    }

    /**
     * Fetch Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\FetchInvoiceItemRequest
     */
    public function fetchInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\FetchInvoiceItemRequest', $parameters);
    }

    /**
     * Delete Invoice Item
     *
     * @param array $parameters
     * @return \Omnipay\Erede\Message\DeleteInvoiceItemRequest
     */
    public function deleteInvoiceItem(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Erede\Message\DeleteInvoiceItemRequest', $parameters);
    }

    /**
     * 
     */
    protected function createRequest($class, array $parameters)
    {
        $gatewayParameters = $this->getParameters();
        $merchantId        = isset($gatewayParameters[self::KEY_MERCHANT_ID])
                           ? $gatewayParameters[self::KEY_MERCHANT_ID]
                           : '';
        $merchantKey        = isset($gatewayParameters[self::KEY_MERCHANT_KEY])
                            ? $gatewayParameters[self::KEY_MERCHANT_KEY]
                            : '';
        $obj = new $class(
            $this->httpClient,
            $this->httpRequest,
            $this->encodeCredentials($merchantId, $merchantKey),
            $this->getEndpoint()
        );
        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }
}
