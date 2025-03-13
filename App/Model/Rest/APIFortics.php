<?php
namespace App\Model\Rest;

class APIFortics
{
    private $url;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->url = getenv('API_URL_SZ');
        $this->user = getenv('API_USER_SZ');
        $this->pass = getenv('API_PASS_SZ');
    }

    private static function getToken()
    {
        $instance = new self();
        $url = $instance->url . '/auth/login';
        $data = [
            "email" => $instance->user,
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


    public static function sendMessage($numero)
    {
        $instance = new self();
        $data = [
            "platform_id" => $numero,
            "type" => "text",
            "channel_id" => "647f2d69971cd900180bbd8c",
            "message" => "A instabilidade na região foi resolvida e o acesso à internet já foi normalizado. Caso sua conexão ainda não tenha sido restabelecida, sugerimos que reinicie seu roteador. Se o problema persistir, entre em contato com nosso suporte para que possamos auxiliar.\n\nAgradecemos sua paciência e compreensão."
        ];
        $url = $instance->url . '/message/send';
        $instance->makeRequest($data, $url);
        return true;
    }
    public static function closeChat($protocolo)
    {
        $instance = new self();

        $session_id = self::getSessionId($protocolo);
        $data = [
            "session_id" => $session_id
        ];

        $url = $instance->url . '/attendances/finish';
        $instance->makeRequest($data, $url);
        return true;
    }

    private static function getSessionId($protocolo)
    {
        $instance = new self();
        $url = $instance->url . '/attendances?protocol=' . urlencode($protocolo);
        $token = self::getToken();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "User-Agent: insomnia/10.3.1"
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        return $responseData['data'][0]['_id'] ?? 'ID não encontrado';
    }
    private function makeRequest(array $data, $url)
    {
        $token = self::getToken();
        $ch = curl_init($url);

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: APIElite/1.0',
            'Authorization: Bearer ' . $token . ''
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Executa a requisição
        $response = curl_exec($ch);
        // Verifica erros de conexão
        if (curl_errno($ch)) {
            $error = "Erro na requisição: " . curl_error($ch);
            curl_close($ch);
            return $error;
        }

        // Fecha a conexão cURL
        curl_close($ch);

        // Decodifica a resposta JSON
        $decodedResponse = json_decode($response, true);

        // Verifica se a decodificação foi bem-sucedida
        if (is_null($decodedResponse)) {
            return "Erro ao decodificar a resposta da API: " . $response;
        }
        return $decodedResponse;

    }
}