<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function sendOtp(string $phone, string $code): bool
    {
        $message = "SASA ERP Master Kontrol tek kullanimlik guvenlik kodunuz: {$code}. Bu kodu kimseyle paylasmayiniz.";
        $driver = env('SMS_DRIVER', 'log');

        if ($driver === 'netgsm') {
            return self::sendNetgsm($phone, $message);
        }

        if ($driver === 'twilio') {
            return self::sendTwilio($phone, $message);
        }

        Log::info("SMS_DISPATCH [{$driver}]: To={$phone}, Code={$code}, Message={$message}");
        return true;
    }

    private static function sendNetgsm(string $phone, string $message): bool
    {
        $usercode = env('NETGSM_USERCODE');
        $password = env('NETGSM_PASSWORD');
        $header = env('NETGSM_HEADER', 'SASAERP');

        if (empty($usercode) || empty($password)) {
            Log::warning('Netgsm credentials missing. Falling back to log.');
            Log::info("NETGSM_SIMULATED: To={$phone}, Message={$message}");
            return true;
        }

        try {
            $response = Http::timeout(10)->get('https://api.netgsm.com.tr/sms/send/get', [
                'usercode' => $usercode,
                'password' => $password,
                'gsmno' => preg_replace('/[^0-9]/', '', $phone),
                'message' => $message,
                'msgheader' => $header,
            ]);

            return $response->successful() && str_starts_with($response->body(), '00');
        } catch (\Throwable $e) {
            Log::error('Netgsm SMS error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendTwilio(string $phone, string $message): bool
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_NUMBER');

        if (empty($sid) || empty($token) || empty($from)) {
            Log::warning('Twilio credentials missing. Falling back to log.');
            Log::info("TWILIO_SIMULATED: To={$phone}, Message={$message}");
            return true;
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->timeout(10)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $phone,
                    'Body' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage());
            return false;
        }
    }

    public static function maskPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) >= 10) {
            $prefix = substr($cleaned, 0, 4);
            $suffix = substr($cleaned, -2);
            return "+90 (" . substr($prefix, 1, 3) . ") *** ** " . $suffix;
        }
        return $phone;
    }
}
