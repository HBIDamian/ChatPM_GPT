# ChatPM_GPT
A Highly customisable ChatGPT Chat Bot for your minecraft world!

## How to use
> [!NOTE]  
> You will need an OpenAI API key to use this plugin. You can get one from [OpenAI Platform](https://platform.openai.com/account/api-keys).
1. Copy your OpenAI API key and paste it in the `config.yml` file.
2. Run the server
3. Trigger the chatbot by typing `@Botty` in the chat, followed by your message.

## Permission
- `chatpm_gpt.use` - Allows the player to use the chatbot. Default: true.

## Config
```yaml
# This is the configuration file for ChatPM_GPT.
# You can modify the settings here to customize the plugin to your liking.

# --- OpenAI API Settings ---
ApiUrl: "https://api.openai.com/v1/chat/completions" # The URL for OpenAI API.
ApiKey: "sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" # Your OpenAI API key.

# --- Chat Settings ---
ChatModel: "gpt-4o-mini" # For a list of available models, see https://platform.openai.com/docs/models
MaxTokens: 70 # Max tokens per response. 1 token is roughly 4 characters.
Temperature: 1.25 # Higher temperature means more randomness in responses.
TopP: 1.0 # Controls the diversity of responses.
FrequencyPenalty: 0.75 # Penalizes new tokens based on their existing frequency.
PresencePenalty: 0.25 # Penalizes new tokens based on their existing presence.

# --- Bot Settings ---
BotName: "Botty McBotface" # The name of the AI bot.
BotTrigger: "@Botty" # The text that the player will need to type to trigger the AI.

# --- System Prompt ---
# This is the prompt that the AI will use to generate responses.
# Available placeholders:
# - {BOT_NAME}    - Bot's name
# - {PLAYER_NAME} - Player's name
SystemPrompt: |
  You are a AI chat bot that's taking place in a PocketMine Minecraft Bedrock Edition server.
  Your name is '{BOT_NAME}', and you're currently speaking to '{PLAYER_NAME}'. 
  '{BOT_NAME}' is witty, clever, and very friendly. '{BOT_NAME}' is here to have fun conversations in the Minecraft Chat.
  Don't ask if you can help. Just be yourself and have fun! 
  Minecraft doesn't support Unicode emotes, so you can't use emojis! You can use :), :(, :D type emotes though. 
  Just don't overdo it. Limit your character count to 160 characters or less.
  Keep your responses friendly and appropriate for all ages. You can't physically interact with the world,
  but you can interact with players through chat. You can't give out personal information or ask for personal information.
  You can't swear, bully, or be mean. You can't advertise or promote anything. You can't spam. 
  You can't impersonate staff or other players. You can't share links or URLs of any kind.

# --- Bot Output Format ---
# How the bot's response will be formatted in the chat.
# Available placeholders: 
# - {BOT_NAME} - Bot's name
# - {RESPONSE} - Bot's response
BotOutputFormat: "<&g{BOT_NAME}§r> {RESPONSE}"
```


