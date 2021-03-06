<?php

namespace App\Libs\LineSdk;

use Illuminate\Support\Facades\Log;

//規約
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/*
 * This polyfill of hash_equals() is a modified edition of https://github.com/indigophp/hash-compat/tree/43a19f42093a0cd2d11874dff9d891027fc42214
 *
 * Copyright (c) 2015 Indigo Development Team
 * Released under the MIT license
 * https://github.com/indigophp/hash-compat/blob/43a19f42093a0cd2d11874dff9d891027fc42214/LICENSE
 */

//php5.6以下のバージョンの場合使用
/*
//function_exists:指定した関数が定義されている場合に true を返す
//hash_equals:タイミング攻撃に対して安全な文字列比較
//2つの文字列が等しいかどうか、同じ長さの時間で比較
if (!function_exists('hash_equals')) {
    // defined:指定した名前の定数が存在するかどうかを調べる
    defined('USE_MB_STRING') or define('USE_MB_STRING', function_exists('mb_strlen'));

    //2つの文字列が等しい
    function hash_equals($knownString, $userString)
    {
        $strlen = function($string) {

            if (USE_MB_STRING) {
                //mb_strlen:文字列の長さを得る
                return mb_strlen($string, '8bit');
            }

            //文字列の長さを得る
            return strlen($string);
        };


        // Compare string lengths
        // 文字列の長さを比較する
        if (($length = $strlen($knownString)) !== $strlen($userString)) {
            return false;
        }

        $diff = 0;

        // Calculate differences
        //差異を計算
        for ($i = 0; $i < $length; $i++) {
            //ord:文字列の先頭バイトを、0 から 255 までの値に変換する
            $diff |= ord($knownString[$i]) ^ ord($userString[$i]);
        }

        return $diff === 0;
    }
}
*/

/**
 *
 */
class LineBotTiny
{
    private $channelAccessToken;
    private $channelSecret;

    private $requestMethod;
    private $lineSignature;

    /**
     * __construct:クラスのインスタンス生成
     *
     * @var string $channelAccessToken  アクセストークン
     * @var string $channelSecret シークレットトークン
     * @var string $requestMethod リクエストメゾット
     * @var string $lineSignature ヘッダーシグネチャ
     */
    public function __construct($channelAccessToken, $channelSecret, $requestMethod, $lineSignature)
    {
        $this->channelAccessToken = $channelAccessToken;
        $this->channelSecret = $channelSecret;

        $this->requestMethod = $requestMethod;
        $this->lineSignature = $lineSignature;
    }


    /**
     *
     *
     * @return array
     */
    public function parseEvents()
    {
        //$_SERVER:サーバー情報および実行時の環境情報
        //ページがリクエストされたときのリクエストメソッド名を返す
        //リクエストがPOSTメソッドなのかGETメソッドか判別
        //何も投稿されなかった場合、エラーを返す
        Log::debug('$_SERVER["REQUEST_METHOD"]', (array)$this->requestMethod);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            //HTTP レスポンスコードを取得または設定
            http_response_code(405);

            // エラーメッセージを送信
            Log::error('Method not allowed');

            // メッセージを出力し、現在のスクリプトを終了
            exit();
        }

        //ファイルの内容を全て文字列に読み込む
        //php://input:リクエストの body 部から生のデータを読み込み
        $entityBody = file_get_contents('php://input');
        Log::debug('$entityBody', (array)print_r($entityBody, true));

        //文字列が０の場合、エラーを返す
        if (strlen($entityBody) === 0) {
            http_response_code(400);
            Log::error('Missing request body');
            exit();
        }

        //hash_equals：文字列が等しい比較
        //sign：数の符号を調べる
        //$_SERVER~:拡張ヘッダからAPI側から設定されているSignature(署名)を取得
        Log::debug('$_SERVER["HTTP_X_LINE_SIGNATURE"]', (array)$this->lineSignature);
        Log::debug('$this->sign($entityBody)', (array)print_r($this->sign($entityBody), true));

        if (!hash_equals($this->sign($entityBody), $this->lineSignature)) {
            http_response_code(400);
            Log::error('Invalid signature value');
            exit();
        }

        //JSONエンコードされた文字列を受け取りPHPに変換
        $data = json_decode($entityBody, true);
        Log::debug('$data', (array)$data);
        if (null === $data) {
            Log::error('"json_decode"に失敗しました。', compact('entityBody'));
            http_response_code(400);
            exit();
        }

        //isset:変数に値がはいっているチェック
        if (!isset($data['events'])) {
            http_response_code(400);
            Log::error('Invalid request body: missing events property');
            exit();
        }

        return $data['events'];
    }


    /**
     * LINEにストリーム送信
     *
     * @var string $message
     *
     * @return void
     */
    public function replyMessage($message)
    {
        $header = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->channelAccessToken,
        );

        //ストリームコンテキストを作成する
        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => 'POST',
                'header' => implode("\r\n", $header),
                'content' => json_encode($message),
            ],
        ]);
        Log::debug('$context', compact('context'));

        //ファイルの内容を全て文字列に読み込む
        $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
        Log::debug('$response', (array)print_r($response, true));

        //strpos:文字列内の部分文字列が最初に現れる場所を見つける
        if (strpos($http_response_header[0], '200') === false) {
            Log::error('Request failed: ' . $response, compact('http_response_header'));
        }
    }


    /**
     * @var string $body
     *
     * @return string MIME base64 方式でデータをエンコード
     */
    private function sign($body)
    {
        //hash_hmac:HMAC 方式を使用してハッシュ値を生成
        $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
        Log::debug('$hash', compact('hash'));

        //MIME base64 方式でデータをエンコード
        $signature = base64_encode($hash);
        Log::debug('message', compact('signature'));

        return $signature;
    }
}
