<?php

if (!function_exists('payment_return_success')){
    function payment_return_success(string $message, array $data = []) : array{
        return [
            'status'  => 'success',
            'code'    => 200,
            'message' => $message,
            'data'    => $data
        ];
    }
}

if (!function_exists('payment_return_error')){
    function payment_return_error(string $message, array $data = []) : array{
        return [
            'status'  => 'exception',
            'code'    => 500,
            'message' => $message,
            'data'    => $data,
        ];
    }
}