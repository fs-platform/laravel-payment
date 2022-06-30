<?php

namespace Smbear\Payment;

use Illuminate\Support\Facades\Log;
use Smbear\Payment\Traits\PaymentConfig;
use Smbear\Payment\Traits\PaymentParams;
use Smbear\Payment\Exceptions\ParamsException;
use Smbear\Payment\Exceptions\MethodException;
use Smbear\Payment\Services\PaymentStatusService;
use Smbear\Payment\Services\PaymentCheckoutService;

class Payment
{
    use PaymentConfig ,PaymentParams;

    /**
     * @var string $local 语言
     */
    public $local;

    /**
     * @var array $config 配置文件数据
     */
    public $config;

    /**
     * @var int $userId 用户id
     */
    public $userId;

    /**
     * @var bool $threeDSecure
     */
    public $threeDSecure;

    /**
     * @var int $merchantId 商家id
     */
    public $merchantId;

    /**
     * @var int $paymentMethod 交易方式
     */
    public $paymentMethod;

    /**
     * @var object $paymentCheckoutService 回调url service
     */
    public $paymentCheckoutService;

    /**
     * @var object $paymentStatusService 支付状态 service
     */
    public $paymentStatusService;

    public function __construct()
    {
        $this->setEnvironment();

        $this->paymentCheckoutService = new PaymentCheckoutService();

        $this->paymentStatusService = new PaymentStatusService();
    }

    /**
     * @Notes: 验证方法是否被使用
     *
     * @param array $parameters
     * @throws MethodException
     * @Author: smile
     * @Date: 2021/6/16
     * @Time: 10:47
     */
    protected function checkMethod($parameters = [])
    {
        foreach ($parameters as $method => $attribute){
            if (is_null($this->$attribute)){
                throw new MethodException($method .' is not call');
            }
        }
    }

    /**
     * @Notes:设置语言环境
     *
     * @param string $local
     * @return $this
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 11:14
     * @throws ParamsException
     */
    public function setLocal(string $local) : self
    {
        if (empty($local)) {
            throw new ParamsException(__FUNCTION__.' 参数异常');
        }

        $this->local = $local;

        return $this;
    }

    /**
     * @Notes:设置3ds认证环境
     * @Notes true 表示开启3ds支付 false 表示不开启3ds支付
     * @param bool $threeDSecure
     * @return $this
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 11:14
     */
    public function setThreeDSecure(bool $threeDSecure) : self
    {
        $this->threeDSecure = $threeDSecure;

        return $this;
    }

    /**
     * @Notes:设置用户id
     *
     * @param int $userId
     * @return $this
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 10:52
     * @throws ParamsException
     */
    public function setUserId(int $userId) : self
    {
        if (empty($userId)) {
            throw new ParamsException(__FUNCTION__.' 参数异常');
        }

        $this->userId = $userId;

        return $this;
    }

    /**
     * @Notes:设置付款方式
     *
     * @param int $paymentMethod
     * @return $this
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 10:51
     * @throws ParamsException
     */
    public function setPaymentMethod(int $paymentMethod ) : self
    {
        if (empty($paymentMethod)) {
            throw new ParamsException(__FUNCTION__.' 参数异常');
        }

        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * @Notes:根据回调获取到merchantId
     *
     * @param \Closure $closure
     * @return self
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 10:29
     * @throws MethodException
     */
    public function getMerchantId(\Closure $closure) : self
    {
        if (empty($this->paymentMethod)) {
            throw new MethodException('setPaymentMethod' .' is not call');
        }

        $this->merchantId = call_user_func($closure,$this->paymentMethod,$this->environment);

        return $this;
    }

    /**
     * @Notes:支付初始化
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/28
     * @Time: 15:38
     * @throws Exceptions\ConfigException|MethodException
     */
    public function init(): array
    {
        $this->getConfig([
            'api_key_id',
            'api_secret',
            'api_end_point',
            'integrator',
            'variant_one',
            'variant_two',
            'return_url',
            'url_domain',
            'merchant_id_one',
            'merchant_id_two',
            'merchant_id_three'
        ]);

        $this->checkMethod([
            'setLocal'              => 'local',
            'setPersonalName'       => 'person',
            'setUserId'             => 'userId',
            'setThreeDSecure'       => 'threeDSecure',
            'setAddress'            => 'address',
            'getMerchantId'         => 'merchantId',
            'setAmountMoney'        => 'amountOfMoney',
            'setPaymentMethod'      => 'paymentMethod',
            'setContactDetails'     => 'contactDetails',
            'setCompanyInformation' => 'companyInformation',
            'setOrderReferences'    => 'orderReferences',
        ]);

        //设置输出格式
        $this->setHostedCheckoutSpecificInput($this->local,[$this->paymentMethod],$this->threeDSecure);

        return $this->paymentCheckoutService
            ->checkout(
                $this->userId,
                $this->config,
                $this->getPaymentParams(),
                $this->merchantId,
                $this->threeDSecure
            );
    }

    /**
     * @Notes:判断支付状态
     *
     * @param string $hostedCheckoutId
     * @return array | string
     * @throws MethodException
     * @throws Exceptions\ConfigException
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 11:56
     */
    public function status(string $hostedCheckoutId)
    {
        $this->getConfig([
            'api_key_id',
            'api_secret',
            'api_end_point',
            'integrator',
            'merchant_id_one',
            'merchant_id_two',
            'merchant_id_three'
        ]);

        $this->checkMethod([
            'setLocal'           => 'local',
            'getMerchantId'      => 'merchantId',
            'setPaymentMethod'   => 'paymentMethod',
            'setAmountMoney'     => 'amountOfMoney',
            'setOrderReferences' => 'orderReferences',
        ]);

        return $this->paymentStatusService
            ->status(
                $this->config,
                $hostedCheckoutId,
                $this->local,
                $this->merchantId,
                $this->getPaymentParams()
            );
    }
}