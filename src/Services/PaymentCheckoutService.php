<?php

namespace Smbear\Payment\Services;

use Illuminate\Support\Facades\Log;
use Smbear\Payment\Enums\PaymentEnums;
use Smbear\Payment\Traits\PaymentException;
use Smbear\Payment\Traits\PaymentConnection;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Order;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Shipping;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\Customer;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\ThreeDSecure;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\RedirectionData;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\CreateHostedCheckoutRequest;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\CardPaymentMethodSpecificInput;

class PaymentCheckoutService
{
    use PaymentConnection ,PaymentException;

    /**
     * @Notes:payment 获取到checkout 页面数据
     * 将异常完整的记录
     * @param int $customerId
     * @param array $config
     * @param array $parameter
     * @param int $merchantId
     * @param bool $threeDSecure
     * @return array
     * @Author: smile
     * @Date: 2021/6/28
     * @Time: 18:31
     */
    public function checkout(int $customerId,array $config,array $parameter,int $merchantId,bool $threeDSecure): array
    {
        try{
            $customer = new Customer();

            $customer->billingAddress     = $parameter['address'];
            $customer->contactDetails     = $parameter['contactDetails'];
            $customer->merchantCustomerId = $customerId;
            $customer->companyInformation = $parameter['companyInformation'];

            $shipping = new Shipping();
            $shipping->address = $parameter['addressPerson'];

            $order = new Order();
            $order->amountOfMoney = $parameter['amountOfMoney'];
            $order->customer      = $customer;
            $order->shipping      = $shipping;
            $order->references    = $parameter['orderReferences'];

            $body = new CreateHostedCheckoutRequest();
            $body->order = $order;

            $body->hostedCheckoutSpecificInput = $parameter['hostedCheckoutSpecificInput'];

            $body->hostedCheckoutSpecificInput->returnUrl         = $config['return_url'];
            $body->hostedCheckoutSpecificInput->showResultPage    = (bool) $config['show_result_page'];
            $body->hostedCheckoutSpecificInput->returnCancelState = (bool) $config['return_cancel_state'];

            //验证是否开启3ds认证
            if ($threeDSecure) {
                $threeDSecure = new ThreeDSecure();
                $threeDSecure->authenticationAmount = $parameter['amountOfMoney'];
                $threeDSecure->authenticationFlow = "browser";
                $threeDSecure->challengeCanvasSize = "390x400";
                $threeDSecure->challengeIndicator = "no-challenge-requested";
                $threeDSecure->exemptionRequest = "automatic";
                $threeDSecure->skipAuthentication = false;

                $cardPaymentMethodSpecificInput = new CardPaymentMethodSpecificInput();
                $cardPaymentMethodSpecificInput->threeDSecure = $threeDSecure;

                $body->cardPaymentMethodSpecificInput = $cardPaymentMethodSpecificInput;
            }

            $response = $this->client($config)
                ->merchant($merchantId)
                ->hostedcheckouts()
                ->create($body);

            if (isset($response->RETURNMAC,$response->partialRedirectUrl) && !empty($response->RETURNMAC)  && !empty($response->partialRedirectUrl)) {

                return payment_return_success('success',[
                    'url'    => $config['url_domain'].$response->partialRedirectUrl,
                    'params' => [
                        'returnMac'        => $response->RETURNMAC,
                        'hostedCheckoutId' => $response->hostedCheckoutId,
                        'information'      => json_encode($response,JSON_FORCE_OBJECT),
                    ]
                ]);
            }

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('checkout 返回的数据不完整 :'.json_encode($response,JSON_FORCE_OBJECT));

            return payment_return_error('error');
        }catch (\Exception $exception){
            $message = '';

            if (method_exists($exception,'getHttpStatusCode')) {
                $httpCode = $exception->getHttpStatusCode();

                $message = PaymentEnums::ERRORS[$httpCode] ?? '';
            }

            if (empty($message)) {
                $message = $this->getExceptionMessage($exception);
            }

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('初始化异常:'.$message);

            report($exception);

            return payment_return_error($message);
        }
    }
}