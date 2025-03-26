<?php
namespace App\Model\Rest;

class APIInt6
{
    private $url;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->url = getenv('API_URL_INT6');
        $this->user = getenv('API_USER_INT6');
        $this->pass = getenv('API_PASS_INT6');
    }

    private static function getToken()
    {
        $instance = new self();
        $url = $instance->url . '/api/auth/v2/request_token';
        $data = [
            "username" => $instance->user,
            "password" => $instance->pass
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
        } else {
            $responseData = json_decode($response, true);
            $token = $responseData['token'] ?? '';
        }
        curl_close($ch);
        return $token;
    }
    public static function verificaMassivaBydId($id_massiva)
    {
        $instance = new self();
        $token = self::getToken();

        $url = $instance->url . "/api/massives/v1/events?filters=" . urlencode(json_encode([
            [
                "field" => "analytics_event_id",
                "op" => "==",
                "value" => $id_massiva
            ],
            [
                "field" => "admin_status",
                "op" => "==",
                "value" => "resolved"
            ]
        ]));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token"
        ]);

        $response = curl_exec($ch);
        $decodedResponse = json_decode($response, true);
        if (!empty($decodedResponse['events'])) {
            return true;
        }
        return false;

    }
}