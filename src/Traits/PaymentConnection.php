<?php

namespace Smbear\Payment\Traits;

use Ingenico\Connect\Sdk\Client;
use Ingenico\Connect\Sdk\Communicator;
use Ingenico\Connect\Sdk\DefaultConnection;
use Ingenico\Connect\Sdk\CommunicatorConfiguration;

trait PaymentConnection
{
    public $client;

    /**
     * @Notes:payment client
     *
     * @param array $config
     * @return Client
     * @Author: smile
     * @Date: 2021/6/29
     * @Time: 11:48
     */
    public function client(array $config): Client
    {
        if (is_null($this->client)){
            $communicatorConfiguration = new CommunicatorConfiguration(
                $config['api_key_id'],
                $config['api_secret'],
                $config['api_end_point'],
                $config['integrator']
            );

            $connection = new DefaultConnection();

            $communicator = new Communicator($connection, $communicatorConfiguration);

            $this->client = new Client($communicator);
        }

        return $this->client;
    }
}