<?php

namespace Smbear\Payment\Traits;

trait PaymentException
{
    /**
     * 格式化获取到异常的message
     * @param object $exception
     * @return string
     */
    public function getExceptionMessage(object $exception): string
    {
        $message = $exception->getMessage();

        if (method_exists($exception,'getResponse')) {
            $response = $exception->getResponse();

            $errors = $response->errors ?? [];

            if (!empty($errors) && is_array($errors)) {
                $messageObject = current($errors) ?? new \stdClass();

                if (!empty($messageObject->message) && is_string($messageObject->message)) {
                    $message = $messageObject->message;
                }
            }
        }

        return $message;
    }
}