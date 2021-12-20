<?php

namespace Smbear\Payment\Enums;

class PaymentEnums
{
    const ERRORS = [
        0 => 'The payment attempt was created',
        20 => 'The HostedMerchantLink transaction is waiting for the consumer to be redirected by the merchant to WebCollect',
        25 => 'The HostedMerchantLink transaction is waiting for the consumer to enter missing data on the payment pages of GlobalCollect',
        30 => 'The Hosted Merchant Link transaction is waiting for WebCollect to redirect the consumer to the bank payment pages (optionally, after the consumer enters missing data)',
        50 => 'The payment request and consumer have been forwarded to the payment pages of the bank',
        55 => 'The consumer received all payment details to initiate the transaction the consumer must go to the (bank) office to initiate the payment',
        60 => 'The consumer is not enrolled for 3D Secure authentications',
        65 => 'The consumer is at an office to initiate a transaction
				The status is used when the supplier polls the WebCollect database to verify if a payment on an order is (still) possible',
        70 => 'The status of the payment is in doubt at the bank',
        100 => 'WebCollect rejected the payment instruction.You can contact your card issuing bank or PP customer service to get the specific reason for the transaction failure.',
        120 => 'The bank rejected the payment',
        125 => 'The consumer cancelled the payment while on the bank payment page',
        130 => 'The payment has failed',
        140 => 'The payment was not completed within the given set time limit by the consumer and is expired<BR/>The payment has failed',
        150 => 'WebCollect did not receive information regarding the outcome of the payment at the bank',
        160 => 'The transaction was rejected due to risk control by the credit card company.It is recommended that you temporarily stop payment to avoid violating the new risk control regulations and try another card or payment method.',
        170 => 'The authorization is expired because no explicit settlement request was received in time',
        172 => 'The enrolment period was pending for too long',
        175 => 'The validation period was pending for too long',
        180 => 'The cardholder authentication response from the bank was invalid or not completed',
        190 => 'The settlement is rejected
			Used in a captured by GlobalCollect credit card online transaction, specifically ATOS',
        200 => 'The cardholder was successfully authenticated',
        220 => 'The authentication service was out of order; the cardholder could not be authenticated',
        230 => 'The cardholder is not participating in the 3D Secure authentication program',
        280 => 'The cardholder authentication response from the bank was invalid or not completed
			Authorization is not possible',
        300 => 'This payment will be re-authorized and settled offline',
        310 => 'The consumer is not enrolled for 3D Secure authenticationb Authorization is not possible',
        320 => 'The authentication service was out of order; the cardholder could not be authenticated
			Authorization is not possible',
        330 => 'The cardholder is not participating in the 3D Secure authentication program
			Authorization is not possible',
        350 => 'The cardholder was successfully authenticated <br />
			Authorization is not possible',
        400 => '<p style="margin-bottom:8px">Sorry, your request is declined. Please check the following reasons and try again, or choose another payment method.</p>
                <p>1. Total amount exceeds limit (€ 15,000) ;</p>
                <p>2. Card does not support the currency;</p>
                <p>3. Network error, please try later.</p>',
        403 => 'You are not allowed to access the service or account or your API authentication failed.',
        500 => 'Payment was unsuccessful <br />
			This is the final status update for this transaction',
        525 => 'The payment was challenged by your fraud rule set and is pending <br />
			Use the Process Challenged API or the Web Payment Console if you choose to process further',
        550 => 'The payment was referred
			A manual authorization attempt will be made shortly',
        600 => 'The payment instruction is waiting for one of these <br />
			Mandate (direct debit) <br />
			Settlement (credit card online) <br />
			Acceptance (recurring orders)',
        625 => 'The transaction is authorized and waiting for the second message (captured) from the provider',
        650 => 'The real-time bank payment is pending verification by the batch process
			If followed by 50 PENDING AT BANK, the verification could not be carried out successfully',
        800 => 'successful',
        900 => 'The refund was processed',
        950 => 'The invoice was printed and sent',
        975 => 'The settlement file was sent for processing at the financial institution',
        1000 => 'The payment was paid',
        1010 => 'GlobalCollect debited the consumer account',
        1020 => 'GlobalCollect corrected the payment information that was given',
        1030 => 'The chargeback has been withdrawn',
        1050 => 'The funds have been made available for remittance to the merchant',
        1100 => 'GlobalCollect rejected the payment attempt',
        1110 => 'The acquiring bank rejected the direct debit',
        1120 => 'Refused settlement before payment by GlobalCollect (credit card)',
        1150 => 'Refused settlement after payment from Acquirer (credit card)',
        1210 => 'The bank of the consumer rejected the direct debit',
        1250 => 'The payment bounced',
        1500 => 'The payment was charged back by the consumer',
        1510 => 'The consumer reversed the direct debit payment',
        1520 => 'The payment was reversed',
        1800 => 'The payment was refunded',
        1810 => 'GlobalCollect corrected the refund information given',
        1850 => 'Refund is refused by the Acquirer',
        2000 => 'GlobalCollect credited the consumer account',
        2030 => 'The reversed payout was withdrawn',
        2100 => 'GlobalCollect rejected the payout attempt',
        2110 => 'Bank rejected the payout attempt',
        2120 => 'The acquiring bank rejected the payout attempt',
        2130 => 'The consumer bank rejected the payout attempt',
        2210 => 'The consumer reversed the payout',
        2220 => 'The payout was reversed',
        99999 => 'The payment, refund, or payout attempt was cancelled by the merchant',
    ];
}
