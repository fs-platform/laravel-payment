<?php

namespace Smbear\Payment\Traits;

use Ingenico\Connect\Sdk\Domain\Definitions\Address;
use Ingenico\Connect\Sdk\Domain\Definitions\AmountOfMoney;
use Ingenico\Connect\Sdk\Domain\Definitions\CompanyInformation;
use Ingenico\Connect\Sdk\Domain\Definitions\PaymentProductFilter;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\PersonalName;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\ContactDetails;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\AddressPersonal;
use Ingenico\Connect\Sdk\Domain\Payment\Definitions\OrderReferences;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\HostedCheckoutSpecificInput;
use Ingenico\Connect\Sdk\Domain\Hostedcheckout\Definitions\PaymentProductFiltersHostedCheckout;

trait PaymentParams
{
    /**
     * @var object $address 购物地址
     */
    public $address;

    /**
     * @var object $person 购物人信息
     */
    public $person;

    /**
     * @var object $amountOfMoney 购物金额
     */
    public $amountOfMoney;

    /**
     * @var object $addressPersonal 购物人的地址
     */
    public $addressPersonal;

    /**
     * @var object $contactDetails 联系详情
     */
    public $contactDetails;

    /**
     * @var object $companyInformation 公司信息
     */
    public $companyInformation;

    /**
     * @var object $orderReferences 订单信息
     */
    public $orderReferences;

    /**
     * @var object $hostedCheckoutSpecificInput 规格输入
     */
    public $hostedCheckoutSpecificInput;

    /**
     * @Notes:获取到所有的属性数据
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/28
     * @Time: 18:15
     */
    public function getPaymentParams() : array
    {
        return [
            'address'                     => $this->address,
            'amountOfMoney'               => $this->amountOfMoney,
            'person'                      => $this->person,
            'addressPerson'               => $this->addressPersonal,
            'contactDetails'              => $this->contactDetails,
            'companyInformation'          => $this->companyInformation,
            'orderReferences'             => $this->orderReferences,
            'hostedCheckoutSpecificInput' => $this->hostedCheckoutSpecificInput
        ];
    }

    /**
     * @Notes:设置购物地址
     *
     * @param string|null $countryCode
     * @param string|null $city
     * @param string|null $street
     * @param string|null $zip
     * @return $this
     * @Author: smile
     * @Date: 2021/6/26
     * @Time: 18:06
     */
    public function setAddress(string $countryCode = null,string $city = null,string $street = null,string $zip = null) : self
    {
        if (is_null($this->address)){
            $address = new Address();

            $address->countryCode = $countryCode;
            $address->city        = $city;
            $address->street      = $street;
            $address->zip         = $zip;

            $this->address = $address;
        }

        return $this;
    }

    /**
     * @Notes:设置购物金额和货币类型
     *
     * @param int $amount
     * @param string $currencyCode
     * @return $this
     * @Author: smile
     * @Date: 2021/6/26
     * @Time: 18:11
     */
    public function setAmountMoney(int $amount,string $currencyCode) : self
    {
        if (is_null($this->amountOfMoney)){
            $amountOfMoney = new AmountOfMoney();

            $amountOfMoney->amount = (int) $amount;
            $amountOfMoney->currencyCode = $currencyCode;

            $this->amountOfMoney = $amountOfMoney;
        }

        return $this;
    }

    /**
     * @Notes:设置购物人信息
     *
     * @param string|null $firstName
     * @param string|null $surname
     * @return $this
     * @Author: smile
     * @Date: 2021/6/26
     * @Time: 18:19
     */
    public function setPersonalName(string $firstName = null,string $surname = null) : self
    {
        if (is_null($this->person)){
            $person = new PersonalName();

            $person->firstName = $firstName;
            $person->surname   = $surname;

            $this->person = $person;
        }

        return $this;
    }

    /**
     * @Notes:设置用户的个人地址
     *
     * @param string|null $countryCode
     * @param string|null $state
     * @param string|null $city
     * @param string|null $zip
     * @Author: smile
     * @Date: 2021/6/27
     * @Time: 11:03
     * @return $this
     */
    public function setAddressPersonal(string $countryCode = null,string $state = null,string $city = null,string $zip = null) : self
    {
        if (is_null($this->addressPersonal)){
            $addressPersonal = new AddressPersonal();

            $addressPersonal->countryCode = $countryCode;
            $addressPersonal->city = $city;
            $addressPersonal->zip  = $zip;

            if (is_null($this->person)){
                throw new MethodException('setPersonalName 方法未设置');
            }

            $addressPersonal->name = $this->person;

            $this->addressPersonal = $addressPersonal;
        }

        return $this;
    }

    /**
     * @Notes:设置联系方式
     *
     * @param  null|string $emailAddress
     * @return $this
     * @Author: smile
     * @Date: 2021/6/27
     * @Time: 11:07
     */
    public function setContactDetails(string $emailAddress = null) : self
    {
        if (is_null($this->contactDetails)){
            $contactDetails = new ContactDetails();

            $contactDetails->emailAddress = $emailAddress;

            $this->contactDetails = $contactDetails;
        }

        return $this;
    }

    /**
     * @Notes:设置公司信息
     *
     * @param string|null $name
     * @return $this
     * @Author: smile
     * @Date: 2021/6/27
     * @Time: 11:10
     */
    public function setCompanyInformation(string $name = null) : self
    {
        if (is_null($this->companyInformation)){
            $companyInformation = new CompanyInformation();

            $companyInformation->name = $name;

            $this->companyInformation = $companyInformation;
        }

        return $this;
    }

    /**
     * @Notes:设置订单信息
     *
     * @param string $merchantOrderId
     * @param string $merchantReference
     * @return $this
     * @Author: smile
     * @Date: 2021/6/27
     * @Time: 11:13
     */
    public function setOrderReferences(string $merchantOrderId,string $merchantReference) : self
    {
        if (is_null($this->orderReferences)){
            $orderReferences = new OrderReferences();

            $orderReferences->merchantOrderId   = $merchantOrderId;
            $orderReferences->merchantReference = $merchantReference;

            $this->orderReferences = $orderReferences;
        }

        return $this;
    }

    /**
     * @Notes:设置托管签出规格输入
     *
     * @param string $local
     * @param array $paymentMethod
     * @return $this
     * @Author: smile
     * @Date: 2021/6/27
     * @Time: 11:18
     */
    public function setHostedCheckoutSpecificInput(string $local,array $paymentMethod) : self
    {
        if (is_null($this->hostedCheckoutSpecificInput)){
            $hostedCheckoutSpecificInput = new HostedCheckoutSpecificInput();

            $hostedCheckoutSpecificInput->locale = $local;
            $hostedCheckoutSpecificInput->variant = config('payment.'.$this->environment.'.variant');

            $hostedCheckoutSpecificInput->paymentProductFilters = new PaymentProductFiltersHostedCheckout();
            $hostedCheckoutSpecificInput->paymentProductFilters->restrictTo = new PaymentProductFilter();
            $hostedCheckoutSpecificInput->paymentProductFilters->restrictTo->products = $paymentMethod;

            $this->hostedCheckoutSpecificInput = $hostedCheckoutSpecificInput;
        }

        return $this;
    }
}