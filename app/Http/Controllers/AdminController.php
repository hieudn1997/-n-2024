<?php

namespace App\Http\Controllers;

use App\Services\VendorService;

class AdminController extends Controller
{
    protected $vendor;

    public function __construct(VendorService $vendor)
    {
        $this->vendor = $vendor;
    }

    public function verifyCredential($credential = 0, $otp)
    {
        try {
            $vendorConfig = Config('vendor');
            $stepupOtpUrl = $vendorConfig['stepup_otp'];
            $currentToken = $this->vendor->getCredential($credential, true);
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $currentToken
                ],
                'body' => json_encode([
                    'duration' => 28800,
                    'otp' => $otp,
                    'otpTypeName' => "TOTP",
                    'tcbsId' => $vendorConfig['account_id']
                ])
            ];

            $res = $this->vendor->post($stepupOtpUrl, $options);

            $this->vendor->updateCredential($credential, $res->token);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
