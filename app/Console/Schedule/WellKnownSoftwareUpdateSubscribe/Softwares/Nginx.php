<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG Co., Ltd.
 * @license GNU General Public License v3.0 (https://www.gnu.org/licenses/gpl-3.0.html)
 * @author DESMG Co., Ltd. (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * 🇨🇳 🇬🇧 🇳🇱
 * Terms of Service: https://www.desmg.com/policies/terms
 *
 * Released under GNU General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Softwares;

use App\Common\Config;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Common;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\Software;
use App\Console\Schedule\WellKnownSoftwareUpdateSubscribe\SoftwareInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\ArrayShape;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

class Nginx implements SoftwareInterface
{
    /**
     * @param int $chat_id
     * @param string $version
     * @return array
     */
    #[ArrayShape([
        'chat_id' => 'int',
        'text' => 'string',
        'reply_markup' => InlineKeyboard::class,
    ])]
    public function generateMessage(int $chat_id, string $version): array
    {
        $emoji = Common::emoji();
        $message = [
            'chat_id' => $chat_id,
            'text' => "$emoji A new version of Nginx($version) is now available.",
            'reply_markup' => new InlineKeyboard([]),
        ];
        $button1 = new InlineKeyboardButton([
            'text' => 'View',
            'url' => 'https://nginx.org/en/download.html',
        ]);
        $button2 = new InlineKeyboardButton([
            'text' => 'Download',
            'url' => "https://nginx.org/download/nginx-$version.tar.gz",
        ]);
        $message['reply_markup']->addRow($button1, $button2);
        return $message;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        $data = $this->getJson();
        if (!is_array($data)) {
            return Common::getLastVersion(Software::Nginx);
        }
        $major = 0;
        $minor = 0;
        $patch = 0;
        foreach ($data as $branch) {
            if (preg_match('/^release-(\d+)\.(\d+)\.(\d+)$/i', $branch['name'], $matches)) {
                if ($matches[1] > $major) {
                    $major = $matches[1];
                    $minor = $matches[2];
                    $patch = $matches[3];
                } elseif ($matches[1] == $major && $matches[2] > $minor) {
                    $minor = $matches[2];
                    $patch = $matches[3];
                } elseif ($matches[1] == $major && $matches[2] == $minor && $matches[3] > $patch) {
                    $patch = $matches[3];
                }
            }
        }
        return "$major.$minor.$patch";
    }

    /**
     * @return array|int|false
     */
    private function getJson(): array|int|false
    {
        $headers = Config::CURL_HEADERS;
        $ts = Carbon::now()->getTimestamp();
        $headers['User-Agent'] .= " Telegram-Nginx-Subscriber-Runner/$ts";
        $last_modified = Common::getLastModified(Software::Nginx);
        if ($last_modified) {
            $headers['If-Modified-Since'] = $last_modified;
        }
        $get = Http::
        withHeaders($headers)
            ->accept('application/vnd.github+json')
            ->withToken(env('GITHUB_TOKEN'))
            ->get('https://api.github.com/repos/nginx/nginx/tags?per_page=30');
        $last_modified = $get->header('last-modified');
        Common::cacheLastModified(Software::Nginx, $last_modified);
        if ($get->status() == 200) {
            return $get->json();
        }
        if ($get->status() == 304) {
            return 304;
        }
        return false;
    }
}
