<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KapitalBankService
{
    private string $baseUrl;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.kapital.base_url'), '/');
        $this->username = config('services.kapital.username');
        $this->password = config('services.kapital.password');
    }

    /**
     * Create a purchase order (Order_SMS).
     * Returns ['id', 'hppUrl', 'password', 'status', ...]
     *
     * @throws \Exception
     */
    public function createOrder(float $amount, string $description, string $redirectUrl): array
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->timeout(30)
            ->post($this->baseUrl . '/order', [
                'order' => [
                    'typeRid'        => 'Order_SMS',
                    'amount'         => number_format($amount, 2, '.', ''),
                    'currency'       => 'AZN',
                    'language'       => 'az',
                    'description'    => $description,
                    'hppRedirectUrl' => $redirectUrl,
                ],
            ]);

        if (!$response->successful()) {
            Log::error('KapitalBank createOrder failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \Exception('Ödəniş sistemi ilə əlaqə qurulmadı. Zəhmət olmasa yenidən cəhd edin.');
        }

        $order = $response->json('order');

        if (empty($order['id'])) {
            Log::error('KapitalBank createOrder unexpected response', ['body' => $response->body()]);
            throw new \Exception('Ödəniş sistemi xətalı cavab qaytardı.');
        }

        return $order;
    }

    /**
     * Get order details to verify payment status.
     * Returns the 'order' object from Kapital Bank.
     *
     * @throws \Exception
     */
    public function getOrderDetails(int $orderId): array
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->timeout(30)
            ->get($this->baseUrl . '/order/' . $orderId);

        if (!$response->successful()) {
            Log::error('KapitalBank getOrderDetails failed', [
                'orderId' => $orderId,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            throw new \Exception('Ödəniş statusu yoxlanılmadı.');
        }

        return $response->json('order') ?? [];
    }

    /**
     * Build the HPP redirect URL.
     * hppUrl already contains the full path (e.g. https://txpgtst.birbank.az/flex)
     */
    public function buildHppUrl(string $hppUrl, int $orderId, string $orderPassword): string
    {
        return $hppUrl . '?id=' . $orderId . '&password=' . $orderPassword;
    }
}
