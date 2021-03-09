<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Libs\LineSdk\LineBotTiny;
use App\Tabelogdata;


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
                    if ($message['type'] == 'text') {

                        // メッセージが『おすすめのお店』の場合はお店情報を返す
                        switch ($message['text']) {

                            case 'おすすめのお店':

                                // ランダムでID番号を取得
                                $min = 1;
                                $max = 1736;
                                $id_number = mt_rand($min, $max);
                                // テーブルからデータ取得
                                $data = Tabelogdata::find($id_number);

                                // 各データを変数に代入
                                $url = $data->url;
                                $name = $data->name;
                                $review = $data->review;
                                $type = $data->type;
                                $address = $data->address;

                                // 返信内容
                                $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [

                                        [
                                            'type' => 'text',
                                            'text' => "オススメのお店" . "\n" .
                                                "□URL" . "\n" . $url .  "\n" .
                                                "□店名" . "\n" . $name . "\n"  .
                                                "□レビュー数" . "\n" . $review . "\n" .
                                                "□ジャンル" . "\n" . $type . "\n"  .
                                                "□住所" . "\n" . $address
                                        ]

                                    ]
                                ]);
                                break;

                                // メッセージが『おすすめのお店』ではない場合
                            default:

                                $client->replyMessage([
                                    'replyToken' => $event['replyToken'],
                                    'messages' => [

                                        [
                                            'type' => 'text',
                                            'text' => '『おすすめのお店』と入力してください'
                                        ]

                                    ]
                                ]);

                                break;
                        }

                        // メッセージタイプがテキスト以外の場合、エラー処理にエラーメッセージを送信
                    } else {
                        Log::error('Unsupported message type: ' . $message['type']);
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
