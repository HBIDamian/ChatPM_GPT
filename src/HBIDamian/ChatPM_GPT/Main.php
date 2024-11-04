<?php
namespace HBIDamian\ChatPM_GPT;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class Main extends PluginBase implements Listener {
    private ConfigManager $configManager;
    private array $chatHistory = [];

    public function onEnable(): void {
        $this->saveResource("config.yml", false);
        $this->configManager = new ConfigManager($this);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $trigger = $this->configManager->getBotTrigger();
    
        if (!$player->hasPermission("chatpmgpt.use")) {
            return;
        }
    
        if (str_starts_with(strtolower($message), strtolower($trigger))) {
            $input = trim(str_replace($trigger, "", $message));
    
            // Fetch player's chat history and encode it as JSON
            $history = $this->chatHistory[$player->getName()] ?? [];
            $historyJson = json_encode($history);
    
            // Dispatch an asynchronous task for the bot's response
            $this->getServer()->getAsyncPool()->submitTask(new BotHandler(
                $player->getName(),
                $input,
                [
                    'url' => $this->configManager->getApiUrl(),
                    'apiKey' => $this->configManager->getApiKey(),
                    'model' => $this->configManager->getChatModel(),
                    'temperature' => $this->configManager->getTemperature(),
                    'maxTokens' => $this->configManager->getMaxTokens(),
                    'topP' => $this->configManager->getTopP(),
                    'frequencyPenalty' => $this->configManager->getFrequencyPenalty(),
                    'presencePenalty' => $this->configManager->getPresencePenalty(),
                    'systemPrompt' => $this->configManager->getSystemPrompt(),
                    'botName' => $this->configManager->getBotName(),
                    'outputFormat' => $this->configManager->getBotOutputFormat(),
                    'history' => $historyJson // Pass encoded history
                ]
            ));

            // Update player's chat history with the new message
            $this->chatHistory[$player->getName()][] = ["role" => "user", "content" => $input];

            // Ensure history doesn't exceed the maximum count
            $maxHistoryCount = $this->configManager->getMaxHistoryCount();
            if (count($this->chatHistory[$player->getName()]) > $maxHistoryCount) {
                $this->chatHistory[$player->getName()] = array_slice($this->chatHistory[$player->getName()], -$maxHistoryCount);
            }
        }
    }
}