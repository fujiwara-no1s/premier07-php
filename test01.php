<?php
// APIキー
$api_key = '__API_KEY__';
// APIシークレット
$api_secret = '__API_SECRET__';
// アクセストークン
$access_token = '__ACCESS_TOKEN__';
// アクセストークンシークレット
$access_token_secret = '__ACCESS_TOKEN_SECRET__';
// エンドポイント
$request_url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
$request_method = 'GET';

// リクエストパラメータ
$params = [
  'screen_name' => '@realDonaldTrump',
  'count' => 10,
  'tweet_mode' => 'extended',
];

// キーを作成する
$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret );

// 署名用のパラメータ
$signature_params = [
  'oauth_token' => $access_token,
  'oauth_consumer_key' => $api_key,
  'oauth_signature_method' => 'HMAC-SHA1',
  'oauth_timestamp' => time(),
  'oauth_nonce' => microtime(),
  'oauth_version' => '1.0',
];

// 結合
$params2 = array_merge( $params , $signature_params );

// 並び替え
ksort( $params2 );

// クエリ値を構成
$request_params = http_build_query( $params2 , '' , '&' );
$request_params = str_replace( ['+','%7E'],['%20','~'],$request_params );

// 変換した文字列をURLエンコード
$request_params = rawurlencode( $request_params );

// リクエストメソッドをURLエンコードする
$encoded_request_method = rawurlencode( $request_method );

// リクエストURLをURLエンコードする
$encoded_request_url = rawurlencode( $request_url );

// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params;

// HMAC-SHA1方式のハッシュ値に変換
$signature = base64_encode(hash_hmac( 'sha1', $signature_data, $signature_key, true ));

// パラメータに署名を追加
$params['oauth_signature'] = $signature;

// パラメータを変換する
$header_params = http_build_query( $params2, '', ',');

// リクエスト用のコンテキスト
$context = [
  'http' => [
    'method' => $request_method,
    'header' => ['Authorization: OAuth ' . $header_params],
  ],
];

// パラメータがある場合、URLの末尾に追加
if( $params ) {
  $request_url .= '?' . http_build_query( $params ) ;
}

// CURLを使ってリクエスト
$curl = curl_init();
// リクエストURL
curl_setopt( $curl, CURLOPT_URL , $request_url );
// ヘッダーを設定
curl_setopt( $curl, CURLOPT_HEADER, true );
// GET
curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] );
// 中間証明書でエラーが起きる場合があるのでの、中間証明書の検証を行わない
curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false );
// curl_execの結果を文字列で返す
curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true );
// ヘッダに署名を付与
curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] );
$response = curl_exec( $curl );
$response_array = curl_getinfo( $curl );
curl_close( $curl ) ;

$json = substr( $response, $response_array['header_size'] );

// jsonをデコード
$tweets  = json_decode($json);
foreach($tweets as $tweet) {
  $created_at_timestamp = strtotime($tweet->created_at);
  echo "<<< " .date("Y年m月d日 H時i分s秒",$created_at_timestamp) . " >>>\n";
  $full_text = htmlspecialchars($tweet->full_text, ENT_QUOTES, 'UTF-8', false);
  echo $full_text . "\n";
  echo "----------------------------\n";
}

