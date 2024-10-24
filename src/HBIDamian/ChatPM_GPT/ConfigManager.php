<?php
namespace HBIDamian\ChatPM_GPT;

use pocketmine\plugin\PluginBase;

class ConfigManager {
    private PluginBase $plugin;

    public function __construct(PluginBase $plugin) {
        $this->plugin = $plugin;
    }

    public function getApiUrl(): string {
        return $this->plugin->getConfig()->get("ApiUrl");
    }

    public function getApiKey(): string {
        return $this->plugin->getConfig()->get("ApiKey");
    }

    public function getChatModel(): string {
        return $this->plugin->getConfig()->get("ChatModel");
    }

    public function getSystemPrompt(): string {
        return $this->plugin->getConfig()->get("SystemPrompt");
    }

    public function getMaxTokens(): int {
        return $this->plugin->getConfig()->get("MaxTokens");
    }

    public function getTemperature(): float {
        return $this->plugin->getConfig()->get("Temperature");
    }

    public function getTopP(): float {
        return $this->plugin->getConfig()->get("TopP");
    }

    public function getFrequencyPenalty(): float {
        return $this->plugin->getConfig()->get("FrequencyPenalty");
    }

    public function getPresencePenalty(): float {
        return $this->plugin->getConfig()->get("PresencePenalty");
    }

    public function getBotName(): string {
        return $this->plugin->getConfig()->get("BotName");
    }

    public function getBotTrigger(): string {
        return $this->plugin->getConfig()->get("BotTrigger");
    }

    public function getBotOutputFormat(): string {
        return $this->plugin->getConfig()->get("BotOutputFormat");
    }
}
