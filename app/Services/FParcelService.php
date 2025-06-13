<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FParcelService
{
    private $testUrl = 'http://fparcel.net:59/WebServiceExterne';
    private $prodUrl = 'https://admin.fparcel.net/WebServiceExterne';

    private $environment;
    private $token;

    public function __construct($environment = 'test', $token = null)
    {
        $this->environment = $environment;
        $this->token = $token;
    }

    /**
     * Get base URL based on environment
     */
    private function getBaseUrl()
    {
        return $this->environment === 'prod' ? $this->prodUrl : $this->testUrl;
    }

    /**
     * Get authentication token
     */
    public function getToken($username, $password)
    {
        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/get_token', [
                'USERNAME' => $username,
                'PASSWORD' => $password
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to connect to FParcel service');
            }

            $token = $response->body();

            if (stripos($token, 'error') !== false || stripos($token, 'invalid') !== false) {
                throw new Exception('Invalid credentials');
            }

            $this->token = $token;
            return $token;
        } catch (Exception $e) {
            Log::error('FParcel get_token error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a new delivery position
     */
    public function createPosition($data)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/pos_create', array_merge([
                'TOKEN' => $this->token
            ], $data));

            if ($response->failed()) {
                throw new Exception('Failed to create delivery position');
            }

            $result = $response->body();

            if (stripos($result, 'INVALID_TOKEN') !== false) {
                throw new Exception('Invalid or expired token');
            }

            if (stripos($result, 'error') !== false) {
                throw new Exception('FParcel API error: ' . $result);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('FParcel pos_create error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update number of pieces for a position
     */
    public function updatePieces($barcode, $nbPieces)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/set_nb_piece', [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode,
                'NB_PIECE' => $nbPieces
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to update pieces count');
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel set_nb_piece error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get delivery label (PDF)
     */
    public function getLabel($barcode, $format = 'A4', $temporary = false)
    {
        $this->validateToken();

        try {
            $endpoint = $temporary ? '/get_label_tmp' : '/get_label';
            if ($format === 'A5') {
                $endpoint = $temporary ? '/get_label_zebra_tmp' : '/get_label_zebra';
            }

            $response = Http::timeout(30)->post($this->getBaseUrl() . $endpoint, [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get delivery label');
            }

            // Return PDF content
            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel get_label error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Track a delivery position
     */
    public function trackPosition($barcode)
    {
        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/tracking_position/' . $barcode);

            if ($response->failed()) {
                throw new Exception('Failed to track position');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel tracking_position error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get event list
     */
    public function getEventList()
    {
        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/event_list');

            if ($response->failed()) {
                throw new Exception('Failed to get event list');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel event_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payment methods list
     */
    public function getPaymentMethods()
    {
        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/mr_list');

            if ($response->failed()) {
                throw new Exception('Failed to get payment methods');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel mr_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate position (make it active)
     */
    public function validatePosition($barcode)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/set_valid', [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to validate position');
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel set_valid error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get drop points list
     */
    public function getDropPoints()
    {
        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/droppoint_list');

            if ($response->failed()) {
                throw new Exception('Failed to get drop points');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel droppoint_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get position details
     */
    public function getPositionDetails($barcode)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/get_pos_details', [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get position details');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel get_pos_details error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get multiple position details
     */
    public function getMultiplePositionDetails($barcodes)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/get_pos_details_list', [
                'TOKEN' => $this->token,
                'POSBARCODE_LIST' => $barcodes
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get position details');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel get_pos_details_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all positions for the current user
     */
    public function getAllPositions()
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/get_pos_list', [
                'TOKEN' => $this->token
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get positions list');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel get_pos_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get anomaly reasons list
     */
    public function getAnomalyReasons()
    {
        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/motif_ano_list');

            if ($response->failed()) {
                throw new Exception('Failed to get anomaly reasons');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel motif_ano_list error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add bill of lading (BL) to position
     */
    public function addBillOfLading($barcode, $blNumber, $items)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/add_bl', [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode,
                'NUM_BL' => $blNumber,
                'LIGNE_LIST' => $items
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to add bill of lading');
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel add_bl error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get bill of lading PDF
     */
    public function getBillOfLading($barcode)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->get($this->getBaseUrl() . '/get_bl', [
                'TOKEN' => $this->token,
                'POSBARCODE' => $barcode
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get bill of lading');
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel get_bl error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get multiple bills of lading
     */
    public function getMultipleBillsOfLading($barcodes)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/get_bl_en_masse', [
                'TOKEN' => $this->token,
                'POSLIST' => $barcodes
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get bills of lading');
            }

            return $response->body();
        } catch (Exception $e) {
            Log::error('FParcel get_bl_en_masse error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get barcode list by date range
     */
    public function getBarcodesByDateRange($startDate, $endDate)
    {
        $this->validateToken();

        try {
            $response = Http::timeout(30)->post($this->getBaseUrl() . '/barcode_list_filtre', [
                'TOKEN' => $this->token,
                'DATE_START_CREATION' => $startDate,
                'DATE_END_CREATION' => $endDate
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to get barcode list');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('FParcel barcode_list_filtre error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate token exists
     */
    private function validateToken()
    {
        if (!$this->token) {
            throw new Exception('No token provided. Please authenticate first.');
        }
    }

    /**
     * Set token
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Get current token
     */
    public function getTokenValue()
    {
        return $this->token;
    }
}
