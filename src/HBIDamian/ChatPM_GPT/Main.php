<?php
namespace HBIDamian\ChatPM_GPT;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use HBIDamian\ChatPM_GPT\BotHandler;
use HBIDamian\ChatPM_GPT\ConfigManager;

class Main extends PluginBase implements Listener {
    private ConfigManager $configManager;
    private BotHandler $botHandler;

    public function onEnable(): void {
        $this->saveResource("config.yml", false);
        $this->configManager = new ConfigManager($this);
        $this->botHandler = new BotHandler($this->configManager);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
    
        $trigger = $this->configManager->getBotTrigger();
    
        if (str_starts_with(strtolower($message), strtolower($trigger))) {
            // I have to re-send the message to the player, because the bot's message appears before the player's message -_-
            $event->cancel();
            // This is a lazy workaround for the bot responding to its own messages
            $invisChar = "§y"; // My client doesn't render this, so it's invisible. Console does show it though.
            $message = $invisChar . $message;
            $player->chat($message);
    
            // Extract the actual command after the trigger
            $input = trim(str_replace($trigger, "", $message));
    
            $this->botHandler->handleMessage($player, $input, function ($response) use ($player) {
                // Broadcast the response to all players
                $this->getServer()->broadcastMessage($response);
            });
        }
    }
    
}
