<?php

namespace App\Jobs;

use App\Http\Services\UpdateHandleService;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;

class WebhookJob extends BaseQueue
{
    private Update $update;
    private Telegram $telegram;
    private int $updateId;

    /**
     * @param Update $update
     * @param Telegram $telegram
     * @param int $updateId
     */
    public function __construct(Update $update, Telegram $telegram, int $updateId)
    {
        parent::__construct();
        $this->update = $update;
        $this->telegram = $telegram;
        $this->updateId = $updateId;
    }

    public function handle()
    {
        $update = $this->update;
        $telegram = $this->telegram;
        $updateId = $this->updateId;
        UpdateHandleService::handle($update, $telegram, $updateId);
    }
}
