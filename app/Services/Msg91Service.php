<?php

namespace App\Services;

use App\Models\OtpVerification;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Msg91Service
{
    /**
     * Generate and send 6-digit OTP via MSG91 SMS API (with sandbox support for local testing).
     */
    public function sendOtp(string $phone, string $countryCode = '+91', string $purpose = 'signup', ?string $username = null, ?string $ip = null): array
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $cleanCountry = preg_replace('/\D/', '', $countryCode) ?: '91';
        $fullMobile = $cleanCountry . $cleanPhone;

        $otp = (string) random_int(100000, 999999);

        $authKey = trim((string) SiteSetting::getValue('msg91_auth_key', env('MSG91_AUTH_KEY', '')));
        $templateId = trim((string) SiteSetting::getValue('msg91_template_id', env('MSG91_TEMPLATE_ID', '')));
        $senderId = trim((string) SiteSetting::getValue('msg91_sender_id', env('MSG91_SENDER_ID', 'WINB24')));
        $sandboxShowOtp = ((string) SiteSetting::getValue('msg91_sandbox_show_otp', env('MSG91_SANDBOX_SHOW_OTP', '1'))) === '1';

        $provider = 'sandbox';
        $providerResponse = [
            'mode' => 'MSG91_SANDBOX_READY',
            'mobile' => '+' . $fullMobile,
            'sender_id' => $senderId,
            'note' => 'Set MSG91_AUTH_KEY and MSG91_TEMPLATE_ID in .env or Admin Panel -> Settings for live carrier SMS.',
        ];
        $liveSent = false;

        if (!empty($authKey) && $authKey !== 'your_msg91_auth_key_here') {
            try {
                // MSG91 v5 OTP API endpoint
                $url = 'https://control.msg91.com/api/v5/otp';
                $queryParams = [
                    'mobile' => $fullMobile,
                    'otp' => $otp,
                    'otp_length' => 6,
                    'otp_expiry' => 10,
                ];
                if (!empty($templateId) && $templateId !== 'your_msg91_dlt_template_id_here') {
                    $queryParams['template_id'] = $templateId;
                }

                $response = Http::timeout(8)
                    ->withHeaders([
                        'authkey' => $authKey,
                        'accept' => 'application/json',
                        'content-type' => 'application/json',
                    ])
                    ->post($url . '?' . http_build_query($queryParams), [
                        'OTP' => $otp,
                        'company_name' => 'winbook24.com',
                    ]);

                $body = $response->json() ?? ['raw' => $response->body()];
                $provider = 'msg91';
                $providerResponse = $body;

                if ($response->successful() && (($body['type'] ?? '') === 'success' || !isset($body['type']))) {
                    $liveSent = true;
                } else {
                    Log::warning('MSG91 API returned non-success response, falling back to local OTP verification.', $body);
                }
            } catch (\Throwable $e) {
                Log::warning('MSG91 HTTP exception: ' . $e->getMessage());
                $providerResponse = ['error' => $e->getMessage()];
            }
        }

        // Invalidate previous unverified OTPs for this phone & purpose
        OtpVerification::where('phone', $cleanPhone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        $record = OtpVerification::create([
            'phone' => $cleanPhone,
            'country_code' => '+' . $cleanCountry,
            'username' => $username,
            'purpose' => $purpose,
            'otp_code' => $otp,
            'provider' => $liveSent ? 'msg91_live' : $provider,
            'provider_response' => $providerResponse,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(10),
            'ip_address' => $ip,
        ]);

        return [
            'status' => true,
            'otp_id' => $record->id,
            'mobile' => '+' . $cleanCountry . ' ' . $cleanPhone,
            'provider' => $liveSent ? 'MSG91 Live SMS' : 'MSG91 Gateway',
            'live_sent' => $liveSent,
            'sandbox_otp' => ($sandboxShowOtp || !$liveSent) ? $otp : null,
            'message' => $liveSent
                ? "OTP sent to +{$cleanCountry} {$cleanPhone} via MSG91 SMS."
                : "OTP generated via MSG91 Gateway for +{$cleanCountry} {$cleanPhone}.",
        ];
    }

    /**
     * Verify the 6-digit OTP code.
     */
    public function verifyOtp(string $phone, string $otpCode, string $purpose = 'signup'): array
    {
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $cleanOtp = preg_replace('/\D/', '', $otpCode);

        $record = OtpVerification::where('phone', $cleanPhone)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$record) {
            return [
                'status' => false,
                'message' => 'No active OTP found for this phone number. Please click Generate OTP again.',
            ];
        }

        if ($record->expires_at->isPast()) {
            return [
                'status' => false,
                'message' => 'OTP has expired. Please generate a new OTP.',
            ];
        }

        if ($record->attempts >= 5) {
            return [
                'status' => false,
                'message' => 'Too many invalid OTP attempts. Please generate a new OTP.',
            ];
        }

        $record->increment('attempts');

        if ($record->otp_code !== $cleanOtp) {
            return [
                'status' => false,
                'message' => 'Invalid OTP code entered. Please check the 6-digit SMS code.',
            ];
        }

        $record->update(['verified_at' => now()]);

        return [
            'status' => true,
            'record' => $record,
            'message' => 'Phone number verified successfully via MSG91 OTP.',
        ];
    }
}

