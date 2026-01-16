<?php
namespace App\Model\Rest;

class APIFortics
{
    private $url;
    private $user;
    private $pass;
    private $channel;
    private $IP;

    public function __construct()
    {
        $this->url = getenv('API_URL_SZ');
        $this->user = getenv('API_USER_SZ');
        $this->pass = getenv('API_PASS_SZ');
        $this->channel = getenv('API_CHANNEL_SZ');
        $this->IP = getenv('PRIVATE_IP');
    }

    public static function getToken()
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

        $responseData = json_decode($response, true);
        return $responseData['data'][0]['_id'] ?? 'ID não encontrado';
    }
    private static function getChannelId($protocolo, $token)
    {
        $instance = new self();
        $url = $instance->url . '/attendances?protocol=' . urlencode($protocolo);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token"
        ]);

        $response = curl_exec($ch);

        $responseData = json_decode($response, true);
        return $responseData['data'][0]['channel_id'] ?? null;
    }
    public static function sendMessageAtt($numero, $protocolo, $message, &$multiHandle, &$curlHandles, $token, $imagemCaminho = null, $end = false)
    {
        $instance = new self();
        $close_session = 0;
        if ($end) {
            $close_session = 1;
        }

        $data = [
            "platform_id" => $numero,
            "channel_id" => self::getChannelId($protocolo, $token),
            "close_session" => $close_session,
        ];


        if (!empty($imagemCaminho) && file_exists($imagemCaminho)) {
            // Se houver imagem, envia como media
            $basename = basename($imagemCaminho);
            $fileUrl = 'http://' . $instance->IP . '/resources/img/tmp/' . $basename;

            $data["type"] = "media";
            $data["file"] = $fileUrl;
            $data["legend"] = $message; // legenda da imagem
        } else {
            // Somente mensagem
            $data["type"] = "text";
            $data["message"] = $message;
        }
        $url = $instance->url . '/message/send';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        curl_multi_add_handle($multiHandle, $ch);
        $curlHandles[] = $ch;
    }

    // Nova função para executar todas as requisições
    public static function executeBatchRequests(&$multiHandle, &$curlHandles)
    {
        if ($multiHandle === null || empty($curlHandles)) {
            return;
        }

        // Executa todas as requisições em paralelo
        do {
            $status = curl_multi_exec($multiHandle, $active);
        } while ($active && $status == CURLM_OK);

        // Fecha e remove as conexões
        foreach ($curlHandles as $ch) {
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }

        // Limpa os handlers
        $curlHandles = [];
        curl_multi_close($multiHandle);
        $multiHandle = null;
    }
    public static function getAllMessages()
    {
        $instance = new self();
        $url = $instance->url . '/attendances/phase/human';
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
        return $responseData;
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