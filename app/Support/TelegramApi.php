<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class TelegramApi
{
    public function __construct(protected string $token) {}

    private function getUrl($path)
    {
        return implode('/', [
            config('telegramapi.base_url'),
            $this->token,
            $path,
        ]);
    }

    private function sendPostForm($path, $data = [], $headers = [])
    {
        $url = $this->getUrl($path);

        $response = Http::withOptions([
                'curl' => [
                    CURLOPT_DNS_SERVERS => '8.8.8.8',
                ],
            ])
            ->withHeaders($headers)
            ->asForm()
            ->post($url, $data);

        return $response->json();
    }

    public function getDefaultParameter($keyboard = null, $replyToMessageId = null)
    {
        $parameters = [
            'link_preview_options' => json_encode([
                'is_disabled' => true,
            ]),
        ];

        if ($keyboard) {
            $parameters['reply_markup'] = json_encode([
                'resize_keyboard' => true,
                'one_time_keyboard' => true,
                'keyboard' => $keyboard,
            ]);
        }

        if ($replyToMessageId) {
            $parameters['reply_to_message_id'] = $replyToMessageId;
        }

        return $parameters;
    }

    public function getMe()
    {
        return $this->sendPostForm('getMe');
    }

    public function setMyCommands($commands, $optionalParameters = [])
    {
        $requiredParameters = [
            'commands' => [],
        ];

        foreach ($commands as $command => $description) {
            $requiredParameters['commands'][] = [
                'command' => $command,
                'description' => $description,
            ];
        }
        $requiredParameters['commands'] = json_encode($requiredParameters['commands']);

        return $this->sendPostForm('setMyCommands', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }

    public function getUpdates($offset = null, $limit = 200)
    {
        return $this->sendPostForm('getUpdates', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    public function sendMessage($chatId, $text, $optionalParameters = [])
    {
        $requiredParameters = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        return $this->sendPostForm('sendMessage', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }

    public function sendPhoto($chatId, $photo, $caption = '', $optionalParameters = [])
    {
        $requiredParameters = [
            'chat_id' => $chatId,
        ];

        if ($caption) {
            $optionalParameters['caption'] = $caption;
        }

        $data = array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        );

        $url = $this->getUrl('sendPhoto');

        $response = Http::withOptions([
                'curl' => [
                    CURLOPT_DNS_SERVERS => '8.8.8.8',
                ],
            ])
            ->timeout(5)
            ->connectTimeout(5)
            ->attach('photo', $photo, 'photo.jpg')
            ->post($url, $data);

        return $response->json();
    }

    public function sendMediaGroup($chatId, $mediaArray, $optionalParameters = [])
    {
        $requiredParameters = [
            'chat_id' => $chatId,
            'media' => json_encode(array_values($mediaArray)),
        ];

        return $this->sendPostForm('sendMediaGroup', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }

    public function setMyName($name, $optionalParameters = [])
    {
        $requiredParameters = [
            'name' => strval($name),
        ];

        return $this->sendPostForm('setMyName', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }

    public function setMyDescription($description, $optionalParameters = [])
    {
        $requiredParameters = [
            'description' => $description,
        ];

        return $this->sendPostForm('setMyDescription', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }

    public function setMyShortDescription($shortDescription, $optionalParameters = [])
    {
        $requiredParameters = [
            'short_description' => $shortDescription,
        ];

        return $this->sendPostForm('setMyShortDescription', array_replace_recursive(
            $optionalParameters,
            $requiredParameters
        ));
    }
}
