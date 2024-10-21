<?php

namespace HBIDamian\ChatPM_GPT;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class BotHandler {
    private ConfigManager $configManager;

    public function __construct(ConfigManager $configManager) {
        $this->configManager = $configManager;
    }

    public function handleMessage(Player $player, string $input, callable $callback): void {
        $botName = $this->configManager->getBotName();
        
        // System Prompt
        $systemPrompt = $this->configManager->getSystemPrompt();
        $systemPrompt = str_replace("{BOT_NAME}", $botName, $systemPrompt);
        $systemPrompt = str_replace("{PLAYER_NAME}", $player->getName(), $systemPrompt);

        // Build OpenAI API request payload without chat history
        $postData = json_encode([
            "model" => $this->configManager->getChatModel(),
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $input]
            ],
            "temperature" => $this->configManager->getTemperature(),
            "max_tokens" => $this->configManager->getMaxTokens(),
            "top_p" => $this->configManager->getTopP(),
            "frequency_penalty" => $this->configManager->getFrequencyPenalty(),
            "presence_penalty" => $this->configManager->getPresencePenalty()
        ]);

        // Make the asynchronous cURL request
        $this->asyncCurlRequest($this->configManager->getApiUrl(), $postData, $callback, $botName);
    }

    private function asyncCurlRequest(string $url, string $postData, callable $callback, string $botName): void {
        // Create a cURL handle
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->configManager->getApiKey()
        ]);

        // Create a cURL multi handle
        $multiHandle = curl_multi_init();
        curl_multi_add_handle($multiHandle, $ch);

        // Execute the multi handle
        $running = null;
        do {
            $status = curl_multi_exec($multiHandle, $running);
            if ($running) {
                curl_multi_select($multiHandle);
            }
        } while ($running > 0);

        // Get the results
        $result = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $callback(TextFormat::RED . "cURL Error: " . curl_error($ch));
        } else {
            // Decode JSON and check for errors
            $data = json_decode($result, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $callback(TextFormat::RED . "Error decoding JSON response: " . json_last_error_msg());
            } elseif (isset($data['error'])) {
                $callback(TextFormat::RED . "OpenAI API error: " . $data['error']['message']);
            } else {
                // Extract response and handle accordingly
                if (isset($data['choices'][0]['message']['content'])) {
                    $response = $data['choices'][0]['message']['content'];

                    // Set Bot's Output Format
                    $outputFormat = $this->configManager->getBotOutputFormat();
                    // Placeholders
                    $outputFormat = str_replace("{BOT_NAME}", $botName, $outputFormat);
                    $outputFormat = str_replace("{RESPONSE}", $response, $outputFormat);

                    // Send the response
                    $callback(TextFormat::colorize($outputFormat));
                } else {
                    $callback(TextFormat::RED . "Failed to get a response from the bot. Response: " . print_r($data, true));
                }
            }
        }

        // Clean up
        curl_multi_remove_handle($multiHandle, $ch);
        curl_multi_close($multiHandle);
        curl_close($ch);
    }
}
