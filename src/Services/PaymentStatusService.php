<?php

namespace Smbear\Payment\Services;

use Illuminate\Support\Facades\Log;
use Smbear\Payment\Enums\PaymentEnums;
use Smbear\Payment\Exceptions\ApiException;
use Smbear\Payment\Traits\PaymentException;
use Smbear\Payment\Exceptions\BaseException;
use Smbear\Payment\Traits\PaymentConnection;
use Smbear\Payment\Exceptions\TokenException;
use Ingenico\Connect\Sdk\Domain\Payment\ApprovePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\TokenizePaymentRequest;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderApprovePayment;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferencesApprovePayment;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\ApprovePaymentNonSepaDirectDebitPaymentMethodSpecificInput;

class PaymentStatusService
{
    use PaymentConnection ,PaymentException;

    /**
     * @var string $local 语种
     */
    public $local;

    /**
     * @var array $config 配置文件
     */
    public $config;

    /**
     * @var int $merchantId 商户id
     */
    public $merchantId;

    /**
     * @var array $parameters order生成的数据
     */
    public $parameters;

    /**
     * @var string $hostedCheckoutId 支付返回的验证id
     */
    public $hostedCheckoutId;

    /**
     * @Notes:获取订单状态
     *
     * @param array $config
     * @param string $hostedCheckoutId
     * @param string $local
     * @param int $merchantId
     * @param array $parameters
     * @return array|string
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 15:25
     */
    public function status(array $config,string $hostedCheckoutId,string $local,int $merchantId,array $parameters)
    {
        $this->config           = $config;
        $this->local            = $local;
        $this->merchantId       = $merchantId;
        $this->parameters       = $parameters;
        $this->hostedCheckoutId = $hostedCheckoutId;

        try{
            $response = $this->client($config)
                ->merchant($merchantId)
                ->hostedcheckouts()
                ->get($hostedCheckoutId);

            Log::channel(config('payment.channel') ?: 'payment')
                ->info('status 响应数据 :'.json_encode($response,JSON_FORCE_OBJECT));

            if ($response->status == 'PAYMENT_CREATED'){
                $payment  = $this->client($config)
                    ->merchant($merchantId)
                    ->payments()
                    ->get($response->createdPaymentOutput->payment->id);

                $statusCategory = $payment->statusOutput->statusCategory ?? '';

                if ($statusCategory== 'PENDING_CONNECT_OR_3RD_PARTY' || $statusCategory == 'COMPLETED'){
                    return [
                        'status'   => 'success',
                        'paymentId'=> $response->createdPaymentOutput->payment->id,
                        'code'     => $payment->statusOutput->statusCode,
                        'message'  => 'successful',
                        'response' => json_encode($response,JSON_FORCE_OBJECT)
                    ];
                } else if ($statusCategory == 'PENDING_MERCHANT'){
                    switch ($payment->status){
                        case 'PENDING_FRAUD_APPROVAL':
                            return $this->approveChallengedPayment($response->createdPaymentOutput->payment->id);
                        case 'PENDING_APPROVAL':
                            return $this->approvePayment($response->createdPaymentOutput->payment->id);
                        default:
                            return [
                                'status'     => 'error',
                                'code'       => $payment->statusOutput->statusCode,
                                'message'    => $payment->status,
                                'paymentId'  => $response->createdPaymentOutput->payment->id,
                                'response'   => json_encode($response,JSON_FORCE_OBJECT),
                                'outMessage' => $this->getOutMessage($payment->statusOutput->statusCode)
                            ];
                    }

                } else {
                    return [
                        'status'    => 'error',
                        'paymentId' => $response->createdPaymentOutput->payment->id,
                        'code'      => $payment->statusOutput->statusCode ?? 0,
                        'message'   => $statusCategory,
                        'response'  => json_encode($response,JSON_FORCE_OBJECT),
                        'outMessage'=> $this->getOutMessage($payment->statusOutput->statusCode)
                    ];
                }
            }

            return $this->invalidStatusDeal($response);
        }catch (\Exception $exception){
            $message = $this->getExceptionMessage($exception);

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('获取订单状态异常:'.$message);

            report($exception);

            return payment_return_error($message);
        }
    }

    /**
     * @Notes:处理最初的无效状态
     *
     * @param object $response
     * @return array
     * @Author: smile
     * @Date: 2021/5/10
     * @Time: 18:51
     */
    public function invalidStatusDeal(object $response) : array
    {
        switch ($response->status){
            case 'CANCELLED_BY_CONSUMER':
            case 'IN_PROGRESS':
                return [
                    'status'    => 'error',
                    'paymentId' => $response->createdPaymentOutput->payment->id ?? 0,
                    'code'      => $payment->statusOutput->statusCode ?? 0,
                    'message'   => $response->status,
                    'response'  => json_encode($response,JSON_FORCE_OBJECT)
                ];
            default:
                return [
                    'status'    => 'error',
                    'paymentId' => $response->createdPaymentOutput->payment->id ?? 0,
                    'code'      => $payment->statusOutput->statusCode ?? 0,
                    'message'   => 'UNKNOWN ERROR',
                    'response'  => json_encode($response,JSON_FORCE_OBJECT)
                ];
        }
    }

    /**
     * @Notes:自动审核
     *
     * @param string $paymentId
     * @return array|string[]
     * @Author: smile
     * @Date: 2021/5/10
     * @Time: 19:26
     * @throws BaseException
     */
    private function approveChallengedPayment(string $paymentId) : array
    {
        try{
            $response = $this->client($this->config)
                ->merchant($this->merchantId)
                ->payments()
                ->processchallenged($paymentId);

            $statusCategory = $response->statusOutput->statusCategory ?? '';

            if ($statusCategory == 'PENDING_CONNECT_OR_3RD_PARTY' || $statusCategory == 'COMPLETED'){
                return [
                    'status'    => 'success',
                    'paymentId' => $paymentId,
                    'message'   => 'successful',
                    'response'  => json_encode($response,JSON_FORCE_OBJECT),
                    'code'      => $response->statusOutput->statusCode
                ];
            } else if ($statusCategory == 'PENDING_MERCHANT'){
                if ($response->status == 'PENDING_APPROVAL'){
                    return $this->approvePayment($paymentId);
                } else {
                    return [
                        'status'     => 'error',
                        'message'    => $statusCategory,
                        'code'       => $response->statusOutput->statusCode,
                        'paymentId'  => $paymentId,
                        'response'   => json_encode($response,JSON_FORCE_OBJECT),
                        'outMessage' => $this->getOutMessage($response->statusOutput->statusCode)
                    ];
                }
            } else {
                return [
                    'status'     => 'error',
                    'message'    => $statusCategory,
                    'code'       => $response->statusOutput->statusCode,
                    'paymentId'  => $paymentId,
                    'response'   => json_encode($response,JSON_FORCE_OBJECT),
                    'outMessage' => $this->getOutMessage($response->statusOutput->statusCode)
                ];
            }
        }catch (\Exception $exception){
            $message = $this->getExceptionMessage($exception);

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('自动审核 异常:'.$message);

            report($exception);

            throw new ApiException($message);
        }
    }

    /**
     * @Notes:检索付款明细,可疑订单审核
     *
     * @param string $paymentId
     * @return string[]
     * @throws CustomerApiException
     * @throws \Exception
     * @Author: smile
     * @Date: 2021/5/10
     * @Time: 19:12
     */
    public function approvePayment(string $paymentId) : array
    {
        try{
            $token =  $this->createTokenFromPayment($paymentId);

            if (empty($token)) throw new TokenException('payment token 为空');

            $directDebitPaymentMethodSpecificInput = new ApprovePaymentNonSepaDirectDebitPaymentMethodSpecificInput();
            $directDebitPaymentMethodSpecificInput->dateCollect = date("Ymd");;
            $directDebitPaymentMethodSpecificInput->token = $token;

            $references = new OrderReferencesApprovePayment();
            $references->merchantReference = $this->parameters['orderReferences']->merchantReference;

            $order = new OrderApprovePayment();
            $order->references = $references;

            $body = new ApprovePaymentRequest();
            $body->amount = $this->parameters['amountOfMoney']->amount;
            $body->directDebitPaymentMethodSpecificInput = $directDebitPaymentMethodSpecificInput;
            $body->order = $order;

            $response = $this->client($this->config)
                ->merchant($this->merchantId)
                ->payments()
                ->approve($paymentId, $body);

            $statusCategory = $response->payment->statusCategory ?? '';

            if ($statusCategory == 'PENDING_CONNECT_OR_3RD_PARTY' || $statusCategory =='COMPLETED'){
                return [
                    'status'    => 'success',
                    'paymentId' => $paymentId,
                    'message'   => 'successful',
                    'response'  => json_encode($response,JSON_FORCE_OBJECT),
                    'code'      => $response->statusOutput->statusCode
                ];
            } else {
                return [
                    'status'     => 'error',
                    'message'    => $statusCategory,
                    'code'       => $response->statusOutput->statusCode,
                    'paymentId'  => $paymentId,
                    'response'   => json_encode($response,JSON_FORCE_OBJECT),
                    'outMessage' => $this->getOutMessage($response->statusOutput->statusCode)
                ];
            }
        }catch (\Exception $exception){
            $message = $this->getExceptionMessage($exception);

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('检索付款明细 异常:'.$message);

            report($exception);

            throw new ApiException($message);
        }
    }

    /**
     * @Notes:获取到token
     *
     * @param string $paymentId
     * @return string
     * @throws CustomerApiException
     * @throws \Exception
     * @Author: smile
     * @Date: 2021/5/10
     * @Time: 16:44
     */
    public function createTokenFromPayment(string $paymentId) : string
    {
        try{
            $body = new TokenizePaymentRequest();
            $body->alias = 'Approve payment';

            $response = $this->client($this->config)
                ->merchant($this->merchantId)
                ->payments()
                ->tokenize($paymentId,$body);

            return $response->token;
        }catch (\Exception $exception){
            $message = $this->getExceptionMessage($exception);

            Log::channel(config('payment.channel') ?: 'payment')
                ->emergency('获取token 异常:'.$message);

            report($exception);

            throw new ApiException($message);
        }
    }

    /**
     * @Notes:输出错误信息
     *
     * @param int $statusCode
     * @Author: smile
     * @Date: 2021/7/16
     * @Time: 21:07
     * @return string
     */
    protected function getOutMessage(int $statusCode): string
    {
        $errors = PaymentEnums::ERRORS;

        return $errors[$statusCode] ?? 'The payment has failed';
    }
}