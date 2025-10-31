<?php
namespace App\Model\Rest;

class APIFortics
{
    private $url;
    private $user;
    private $pass;
    private $channel;

    public function __construct()
    {
        $this->url = getenv('API_URL_SZ');
        $this->user = getenv('API_USER_SZ');
        $this->pass = getenv('API_PASS_SZ');
        $this->channel = getenv('API_CHANNEL_SZ');
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
        curl_close($ch);

        $responseData = json_decode($response, true);
        return $responseData['data'][0]['_id'] ?? 'ID não encontrado';
    }
    public static function sendMessageAtt($numero, $message, &$multiHandle, &$curlHandles, $token, $end = false)
    {
        $instance = new self();

        $data = [
            "platform_id" => $numero,
            "type" => "text",
            "channel_id" => $instance->channel,
            "message" => $message,
            "close_session" => $end ? 1 : 0
        ];

        $url = $instance->url . '/message/send';

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'User-Agent: APIElite/1.0',
                'Authorization: Bearer ' . $token
            ],
            CURLOPT_POSTFIELDS => json_encode($data),

            // Timeouts e estabilidade
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_NOSIGNAL => true,
        ]);

        // Adiciona ao Multi cURL
        curl_multi_add_handle($multiHandle, $ch);
        $curlHandles[] = $ch;
    }



    // Nova função para executar todas as requisições
    public static function executeBatchRequests(&$multiHandle, &$curlHandles)
    {
        if ($multiHandle === null || empty($curlHandles)) {
            return;
        }

        $active = null;
        $status = null;

        // Loop principal de execução
        do {
            $status = curl_multi_exec($multiHandle, $active);
            if ($active) {
                // Espera até que haja atividade para evitar travamentos
                curl_multi_select($multiHandle, 1.0);
            }
        } while ($active && $status == CURLM_OK);

        // Opcional: capturar respostas e erros (debug)
        foreach ($curlHandles as $ch) {
            $response = curl_multi_getcontent($ch);
            $error = curl_error($ch);

            if ($error) {
                error_log("Erro cURL: " . $error);
            } elseif (!empty($response)) {
                // Você pode armazenar/logar as respostas se quiser
                // error_log("Resposta: " . $response);
            }

            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
            unset($ch);
        }

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