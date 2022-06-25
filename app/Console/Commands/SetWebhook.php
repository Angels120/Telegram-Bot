<?php

namespace App\Console\Commands;

use DESMG\UUID;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class SetWebhook extends Command
{
    protected $signature = 'command:SetWebhook';
    protected $description = 'Set Webhook https://core.telegram.org/bots/api#setwebhook';

    public function handle(): int
    {
        $url = env('APP_URL') . '/api/webhook';
        $max_connections = 25;
        $allowed_updates = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'my_chat_member',
            'chat_member',
            'chat_join_request',
        ];
        $secret_token = UUID::generateUniqueID();
        self::info("Secret token: $secret_token");
        $this->setSecret($secret_token);
        try {
            $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'), env('TELEGRAM_BOT_USERNAME'));
            $result = $telegram->setWebhook($url, [
                'max_connections' => $max_connections,
                'allowed_updates' => $allowed_updates,
                'drop_pending_updates' => true,
            ]);
            self::info($result->getDescription());
        } catch (TelegramException $e) {
            self::error($e->getMessage());
        }
        return self::SUCCESS;
    }

    protected function setSecret(string $data): void
    {
        $filename = App::environmentFilePath();
        $content = file_get_contents($filename);
        $origin = env('HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN');
        self::info("Origin: $origin");
        self::info("Setting secret token to $data");
        $content = preg_replace(
            "/^HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN=$origin/m",
            "HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN=$data",
            $content
        );
        file_put_contents($filename, $content);
    }
}
