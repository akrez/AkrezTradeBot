<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\Message;
use App\Support\BitpinApi;
use App\Support\TelegramApi;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class SyncMessagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $token = config('telegramapi.token');
        //
        $this->syncMessages($token);
        //
        foreach (Message::get() as $message) {
            $this->parseMessage($token, $message);
            $message->delete();
        }
    }

    public function syncMessages(string $token)
    {
        $maxId = Message::withTrashed()->max('message_id');
        $response = (new TelegramApi($token))->getUpdates($maxId + 1);
        $results = Arr::get($response, 'result', []);
        //
        foreach ($results as $result) {
            $content = ($result['message'] ?? $result['edited_message']);
            $id = $result['update_id'];
            //
            $message = Message::withTrashed()->where('message_id', $id)->first();
            if (! $message) {
                Message::create([
                    'message_id' => $id,
                    'chat_id' => ($content['chat']['id'] ?? null),
                    'message_text' => ($content['text'] ?? null),
                    'message_json' => $content,
                ]);
            }
        }
    }

    public function parseMessage($token, Message $message)
    {
        if (empty($message->chat_id) or ! is_scalar($message->chat_id)) {
            return false;
        }
        $chatId = $message->chat_id;

        if (empty($message->message_text) or ! is_scalar($message->message_text)) {
            return false;
        }

        $commands = explode(' ', strtoupper(trim($message->message_text))) + [
            0 => null,
        ];

        if (strlen($commands[0]) === 0) {
            return false;
        }

        $telegramApi = new TelegramApi($token);
        $bitpinApi = new BitpinApi;

        if ($commands[0] == '/GET') {
            $telegramApi->sendMessage(
                $chatId,
                'GET: '.Coin::query()->where('chat_id', $chatId)->get()->implode('coin_name', ' '),
                $telegramApi->getDefaultParameter(
                    null,
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        if ($commands[0] == '/ADDIRT') {
            $telegramApi->sendMessage(
                $chatId,
                'Please choose of keyboard:',
                $telegramApi->getDefaultParameter(
                    array_chunk($bitpinApi->getMarketsKeyboard('IRT'), 3),
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        if ($commands[0] == '/ADDUSDT') {
            $telegramApi->sendMessage(
                $chatId,
                'Please choose of keyboard:',
                $telegramApi->getDefaultParameter(
                    array_chunk($bitpinApi->getMarketsKeyboard('USDT'), 3),
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        if ($commands[0] == '/REMOVE') {
            $markets = Coin::query()->where('chat_id', $chatId)->get()->map(function (Coin $coin) {
                $coin->coin_name = '-'.$coin->coin_name;

                return $coin;
            })->pluck('coin_name')->toArray();
            $telegramApi->sendMessage(
                $chatId,
                'Please choose of keyboard:',
                $telegramApi->getDefaultParameter(
                    array_chunk($markets, 3),
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        if (strpos($commands[0], '+') === 0) {
            $command = str_replace('+', '', $commands[0]);
            $markets = ['ADD:'];
            if ($coinId = $bitpinApi->getMarketId($command)) {
                $markets[] = $command;
                Coin::firstOrCreate([
                    'chat_id' => $chatId,
                    'coin_id' => $coinId,
                ], [
                    'coin_name' => $command,
                ]);
            }
            $telegramApi->sendMessage(
                $chatId,
                implode(' ', $markets),
                $telegramApi->getDefaultParameter(
                    null,
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        if (strpos($commands[0], '-') === 0) {
            $markets = ['REMOVE:'];
            $command = str_replace('-', '', $commands[0]);
            if ($coinId = $bitpinApi->getMarketId($command)) {
                $coins = Coin::query()
                    ->where('chat_id', $chatId)
                    ->where('coin_id', $coinId)
                    ->delete();
                if ($coins) {
                    $markets[] = $command;
                }
            }
            $telegramApi->sendMessage(
                $chatId,
                implode(' ', $markets),
                $telegramApi->getDefaultParameter(
                    null,
                    $message->message_json['message_id']
                )
            );

            return true;
        }

        return false;
    }
}
