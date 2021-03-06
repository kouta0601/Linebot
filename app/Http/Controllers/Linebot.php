<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Libs\LineSdk\LineBotTiny;

/**
 *
 */
class Linebot extends Controller
{
    /**
     *
     * @var Request $request
     */
    public function index(Request $request)
    {
        Log::debug('LINE:POST@index');

        // アクセストークン
        $channelAccessToken = config('line.API_TOKEN');
        Log::debug('$channelAccessToken', compact('hannelAccessToken'));

        // チャンネルシークレット
        $channelSecret = config('line.API_SECRET');
        Log::debug('$channelSecret', compact('channelSecret'));

        $requestMethod = $request->method();
        $lineSignature = $request->header('x-line-signature');
        Log::debug('args', compact('requestMethod', 'lineSignature'));

        // LINEBotTinyクラスをインスタンス化
        $client = new LINEBotTiny($channelAccessToken, $channelSecret, $requestMethod, $lineSignature);

        foreach ($client->parseEvents() as $event) {

            // イベントのタイプ
            switch ($event['type']) {

                    // イベントタイプがメッセージの場合
                case 'message':
                    $message = $event['message'];

                    // メッセージのタイプ
                    switch ($message['type']) {

                            // メッセージタイプがテキストの場合
                        case 'text':
                            // DBから検索し、文字列を返す
                            // モデルへアクセスし、値を取得
                            // $sendText = '';


                            $client->replyMessage([
                                'replyToken' => $event['replyToken'],
                                'messages' => [
                                    [
                                        'type' => 'text',
                                        'text' => $message['text']
                                        // DBから検索し、文字列を返す
                                        // 'text' => $sendText
                                    ]
                                ]
                            ]);

                            break;

                            // メッセージタイプがテキスト以外の場合、エラー処理にエラーメッセージを送信
                        default:
                            Log::error('Unsupported message type: ' . $message['type']);
                            break;
                    }

                    break;

                    // イベントタイプがメッセージ以外の場合、エラー処理にエラーメッセージを送信
                default:
                    Log::error('Unsupported event type: ' . $event['type']);
                    break;
            }
        };

        exit;
    }
}
