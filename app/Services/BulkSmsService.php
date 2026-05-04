<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BulkSmsService
{
    private string $token;
    private string $sender;

    public function __construct()
    {
        $this->token  = (string) config('services.bulksms.token', '');
        $this->sender = (string) config('services.bulksms.sender', 'CountryYoghurt');
    }

    /**
     * Send an SMS via BulkSMSNigeria.
     * Returns true on success, false on failure.
     */
    public function send(string $to, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('BulkSMS: API token not configured. SMS not sent.');
            return false;
        }

        $to = $this->normalizePhone($to);

        if (empty($to)) {
            Log::warning('BulkSMS: Invalid phone number provided.');
            return false;
        }

        try {
            $response = Http::withToken($this->token)
                ->accept('application/json')
                ->post('https://www.bulksmsnigeria.com/api/v2/sms', [
                    'to'   => $to,
                    'from' => $this->sender,
                    'body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('BulkSMS: Request failed.', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('BulkSMS: Exception during send.', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Normalise a Nigerian phone number to the 234XXXXXXXXXX format.
     */
    private function normalizePhone(string $phone): string
    {
        // Strip all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '234') && strlen($digits) === 13) {
            return $digits; // already correct
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '234' . substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '234' . $digits;
        }

        return $digits; // return as-is and let the API validate
    }
}
