<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class MpesaService
{
    private $baseUrl;
    private $consumerKey;
    private $consumerSecret;
    private $shortcode;
    private $passkey;

    public function __construct()
    {
        $env = config('services.mpesa.environment');
        $this->baseUrl = $env === 'live' 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';
        
        $this->consumerKey = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode = config('services.mpesa.shortcode');
        $this->passkey = config('services.mpesa.passkey');
    }

    public function getAccessToken()
    {
        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type' => 'application/json',
        ])->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        if ($response->successful()) {
            return $response->json()['access_token'];
        }
        
        throw new \Exception('Failed to get M-Pesa access token');
    }

    public function stkPush($phone, $amount, $reference, $description = 'Payment')
    {
        // Handle array input from CheckoutController
        if (is_array($phone)) {
            $data = $phone; // First parameter is actually the data array
            $phone = $data['phone'];
            $amount = $data['amount'];
            $reference = $data['reference'];
            $description = $data['description'] ?? 'Payment';
        }
        
        $accessToken = $this->getAccessToken();
        $timestamp = Carbon::now()->format('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        // Format phone number (remove + and leading zeros, ensure starts with 254)
        $phone = $this->formatPhoneNumber($phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => config('services.mpesa.callback_url'),
            'AccountReference' => $reference,
            'TransactionDesc' => $description
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', $payload);

        return $response->json();
    }

    private function formatPhoneNumber($phone)
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading + or 0
        $phone = ltrim($phone, '+0');
        
        // If it starts with 7, prepend 254
        if (substr($phone, 0, 1) === '7') {
            $phone = '254' . $phone;
        }
        
        // If it starts with 1 (like 1xxxxxxx), prepend 25
        if (substr($phone, 0, 1) === '1') {
            $phone = '25' . $phone;
        }
        
        return $phone;
    }
}