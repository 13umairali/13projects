<?php

namespace App\PaymentChannels\Drivers\Paymob;


trait PaymobTrait
{

    public function AuthenticationRequest()
    {
        // Try API key first if available, otherwise use username/password
        if (!empty($this->api_key)) {
            $userInfo = [
                'api_key' => $this->api_key,
            ];
        } else {
            $userInfo = [
                'username' => $this->username,
                'password' => $this->password,
            ];
        }

        $postData = json_encode($userInfo);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accept.paymob.com/api/auth/tokens');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response = curl_exec($ch);

        if ($response === false) {
            \Log::error('Paymob AuthenticationRequest curl error: ' . curl_error($ch));
        }

        curl_close($ch);
        $decoded = json_decode($response);


        return $decoded;
    }

    public function OrderRegistrationAPI(array $requestData)
    {
        $postData = json_encode($requestData);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accept.paymob.com/api/ecommerce/orders');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);
        if ($response === false) {
            \Log::error('Paymob OrderRegistrationAPI curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        $decoded = json_decode($response);


        return $decoded;
    }

    public function PaymentKeyRequest($requestData)
    {
        $requestData['expiration'] = 3600;
        $requestData['integration_id'] = $this->integration_id;
        $postData = json_encode($requestData);


        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://accept.paymob.com/api/acceptance/payment_keys');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);
        if ($response === false) {
            \Log::error('Paymob PaymentKeyRequest curl error: ' . curl_error($ch));
        }
        curl_close($ch);

        $decoded = json_decode($response);


        return $decoded;
    }

    public function calcHMAC($request)
    {
        $values = $request->only([
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data_pan',
            'source_data_sub_type',
            'source_data_type',
            'success'
        ]);

        foreach ($values as &$val) {
            if (is_array($val)) {
                $val = array_values($val);
                $val = implode($val);
            }
            if ($val === true)
                $val = "true";
            if ($val === false)
                $val = "false";
        }
        $concatenate = implode($values);
        $hash = hash_hmac('sha512', $concatenate, $this->HMAC);

        return $hash;
    }

}
