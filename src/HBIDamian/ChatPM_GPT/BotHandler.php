<?php
namespace HBIDamian\ChatPM_GPT;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class BotHandler extends AsyncTask {
    private string $playerName;
    private string $input;
    private string $configJson;
    private string $historyJson; // Use string for thread safety

    public function __construct(string $playerName, string $input, array $config) {
        $this->playerName = $playerName;
        $this->input = $input;
        $this->configJson = json_encode($config);
        $this->historyJson = $config['history']; // Pass history as JSON string
    }

    public function onRun(): void {
        $config = json_decode($this->configJson, true);
        $history = json_decode($this->historyJson, true); // Decode history JSON to array

        // Combine history into messages for OpenAI API
        $systemPrompt = str_replace(
            ["{BOT_NAME}", "{PLAYER_NAME}"],
            [$config['botName'], $this->playerName],
            $config['systemPrompt']
        );
        $messages = [["role" => "system", "content" => $systemPrompt]];

        foreach ($history as $entry) {
            $messages[] = ["role" => "user", "content" => $entry['content']];
        }
        $messages[] = ["role" => "user", "content" => $this->input];

        $postData = json_encode([
            "model" => $config['model'],
            "messages" => $messages,
            "temperature" => $config['temperature'],
            "max_tokens" => $config['maxTokens'],
            "top_p" => $config['topP'],
            "frequency_penalty" => $config['frequencyPenalty'],
            "presence_penalty" => $config['presencePenalty']
        ]);

        // cURL setup
        $ch = curl_init($config['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $config['apiKey']
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $data = json_decode($result, true);

        if (isset($data['error']['message'])) {
            $this->setResult(["error" => "API Error: " . $data['error']['message']]);
            return;
        }

        if ($httpCode !== 200 || !$result) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->setResult(["error" => $error ?: "Request failed. HTTP Code: " . $httpCode]);
            return;
        }
        curl_close($ch);

        if (isset($data['choices'][0]['message']['content'])) {
            $response = $data['choices'][0]['message']['content'];
            $output = str_replace(
                ["{BOT_NAME}", "{RESPONSE}"],
                [$config['botName'], $response],
                $config['outputFormat']
            );
            $this->setResult(["response" => TextFormat::colorize($output)]);
        } else {
            $this->setResult(["error" => "An unexpected error occurred while processing the response."]);
        }
    }

    public function onCompletion(): void {
        $server = Server::getInstance();
        $player = $server->getPlayerExact($this->playerName);

        if ($player !== null && $player->isOnline()) {
            $result = $this->getResult();
            if (isset($result["response"])) {
                $server->broadcastMessage($result["response"]);
            } elseif (isset($result["error"])) {
                $player->sendMessage(TextFormat::RED . "An error occurred while processing your request.");
                $server->getLogger()->error("Error processing request for player " . $this->playerName . ": " . $result["error"]);
            }
        }
    }
}