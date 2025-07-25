<?php
session_start();

/* ---- 1. ワンタイムトークン検証 ---- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST'
 || empty($_POST['__token'])
 || empty($_SESSION['form_token'])
 || !hash_equals($_SESSION['form_token'], $_POST['__token'])) {
    header('Content-Type: text/html; charset=UTF-8', true, 400);
    echo '<p style="padding:2rem;text-align:center;">不正な送信です。ページを再読み込みしてやり直してください。</p>';
    exit;
}
unset($_SESSION['form_token']);  // 使い捨て

/* ---- 1b. サーバー側バリデーション（長さ・制御文字） ---- */
$MAX_LEN = 2000;                          // 1項目あたり許可する最大文字数
foreach ($_POST as $k => $v) {
    if (in_array($k, ['__labels','__replyto','__token'], true)) continue;
    $vals = is_array($v) ? $v : [$v];

    foreach ($vals as $sv) {
        if (mb_strlen($sv) > $MAX_LEN) {
            header('Content-Type: text/html; charset=UTF-8', true, 413);
            echo '<p style="padding:2rem;text-align:center;">入力が長すぎます（最大 '.$MAX_LEN.' 文字）。</p>';
            exit;
        }
        /* 制御文字やヌルバイト混入防止 */
        if (preg_match('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]/u', $sv)) {
            header('Content-Type: text/html; charset=UTF-8', true, 400);
            echo '<p style="padding:2rem;text-align:center;">不正な文字が含まれています。</p>';
            exit;
        }
    }
}

/* ========= ここだけ編集してください ========= */
$adminMail        = 'liang.wang@indep-jp.com';            // 管理者メールアドレス
$adminSubject     = '【サイト問合せ】内容通知';    // 管理者宛 件名
$userSubject      = 'お問い合わせありがとうございます'; // 自動返信 件名
$userHeaderLead   = "以下の内容で受け付けました。\n\n"; // 自動返信 文頭
$signature        = <<< 'SIG'
──────────────────────
サンプル株式会社
https://example.com/
──────────────────────
SIG;
$enableUserReply  = true;                          // true: 自動返信を送信 / false: 送信しない
/* ============================================ */

/* ---- 1c. 必須項目未入力チェック ---- */
$requiredKeys = array_filter(explode(',', $_POST['__required'] ?? ''));
foreach ($requiredKeys as $rk){
    $val = $_POST[$rk] ?? '';
    $isEmpty = is_array($val)
        ? count(array_filter($val,'strlen')) === 0
        : trim($val) === '';
    if ($isEmpty){
        header('Content-Type: text/html; charset=UTF-8', true, 400);
        echo '<p style="padding:2rem;text-align:center;">必須項目が未入力です。</p>';
        exit;
    }
}

/* ---- 1d. メールアドレス形式チェック ---- */
$emailKeys = array_filter(explode(',', $_POST['__replyto'] ?? ''));
foreach ($emailKeys as $ek){
    $v = $_POST[$ek] ?? '';
    if ($v !== '' && !filter_var(is_array($v)?($v[0]??''):$v, FILTER_VALIDATE_EMAIL)){
        header('Content-Type: text/html; charset=UTF-8', true, 400);
        echo '<p style="padding:2rem;text-align:center;">メールアドレスの形式が正しくありません。</p>';
        exit;
    }
}

/* ---- 2. IP 連投 30 秒制限 ---- */

/* クライアント IP 取得（X-Forwarded-For 優先） */
if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ips = array_filter(array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
    $ip  = $ips[0];        // 先頭＝元のクライアント
} else {
    $ip  = $_SERVER['REMOTE_ADDR'] ?? '';
}
$ip = preg_replace('/[^0-9a-fA-F:.]/', '_', $ip);  // ファイル名用にサニタイズ

$stampFile = sys_get_temp_dir().'/form_last_'.preg_replace('/[^0-9a-fA-F:]/','_',$ip);
$now = time();
if (file_exists($stampFile)) {
    $elapsed = $now - (int)file_get_contents($stampFile);
    if ($elapsed < 30) {
        $wait = 30 - $elapsed;
        header('Content-Type: text/html; charset=UTF-8', true, 429);
        echo '<p style="padding:2rem;text-align:center;">30秒以内の連続送信はできません。少し時間をあけて送信して下さい。</p>';
        exit;
    }
}
file_put_contents($stampFile, (string)$now);

/* ---- 3. 送信処理 ---- */
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function ew($s,$suf){ return substr($s,-strlen($suf)) === $suf; }

$labels = json_decode($_POST['__labels'] ?? '[]', true);
$done   = [];
$lines  = [];

foreach ($_POST as $k => $v){
    if (in_array($k, ['__labels','__replyto','__required','__token'], true) || in_array($k, $done, true)) continue;

    /* 住所（3 項目とも空なら非表示） */
    if (ew($k, '_zip')){
        $b    = substr($k, 0, -4);
        $zip  = $_POST[$b.'_zip']  ?? '';
        $pref = $_POST[$b.'_pref'] ?? '';
        $addr = $_POST[$b.'_addr'] ?? '';

        if (trim($zip.$pref.$addr) === ''){
            $done = array_merge($done, ["{$b}_zip","{$b}_pref","{$b}_addr"]);
            continue;
        }

        $lines[] = "[住所] {$zip} / {$pref}{$addr}";
        $done = array_merge($done, ["{$b}_zip","{$b}_pref","{$b}_addr"]);
        continue;
    }

    /* 年月日（3 項目とも空なら非表示） */
    if (ew($k, '_y')){
        $b = substr($k, 0, -2);
        $y = $_POST[$b.'_y'] ?? '';
        $m = $_POST[$b.'_m'] ?? '';
        $d = $_POST[$b.'_d'] ?? '';

        if (trim($y.$m.$d) === ''){
            $done = array_merge($done, ["{$b}_y","{$b}_m","{$b}_d"]);
            continue;
        }

        $lines[] = "[日付] {$y}年{$m}月{$d}日";
        $done = array_merge($done, ["{$b}_y","{$b}_m","{$b}_d"]);
        continue;
    }

    /* 月日（2 項目とも空なら非表示） */
    if (ew($k, '_d_m')){
        $b = substr($k, 0, -4);
        $m = $_POST[$b.'_d_m'] ?? '';
        $d = $_POST[$b.'_d_d'] ?? '';

        if (trim($m.$d) === ''){
            $done = array_merge($done, ["{$b}_d_m","{$b}_d_d"]);
            continue;
        }

        $lines[] = "[日付] {$m}月{$d}日";
        $done = array_merge($done, ["{$b}_d_m","{$b}_d_d"]);
        continue;
    }

    /* 通常（空なら非表示） */
    $vals = is_array($v) ? array_filter($v,'strlen') : [trim($v)];
    if (count($vals) === 0 || $vals[0] === '') continue;

    $lines[] = '[' . ($labels[$k] ?? $k) . '] ' . (is_array($v) ? implode(', ', $vals) : $vals[0]);
}

/* ---- 宛先抽出（自動返信用） ---- */
$userEmail = '';
$keys = array_filter(explode(',', $_POST['__replyto'] ?? ''));
foreach ($keys as $k){
    if (!isset($_POST[$k])) continue;
    $candidate = is_array($_POST[$k]) ? ($_POST[$k][0] ?? '') : $_POST[$k];
    if (filter_var($candidate, FILTER_VALIDATE_EMAIL)){
        $userEmail = $candidate; break;
    }
}

/* ---- 送信 ---- */
mb_language('Japanese'); mb_internal_encoding('UTF-8');

$bodyAdmin = "▼送信内容\n" . implode("\n", $lines) . "\n\n" . $signature;
mb_send_mail($adminMail, $adminSubject, $bodyAdmin, "From: {$adminMail}", "-f{$adminMail}");

if ($enableUserReply && $userEmail){
    $bodyUser = $userHeaderLead . implode("\n", $lines) . "\n\n" . $signature;
    mb_send_mail($userEmail, $userSubject, $bodyUser, "From: {$adminMail}", "-f{$adminMail}");
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>お問い合わせ|株式会社INDEP</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="株式会社INDEP">
<link rel="icon" href="images/logo_icon.png">
<link rel="stylesheet" href="css/style.css">
</head>

<body>

<div id="container">

<header>

<!--<h1 id="logo"><a href="index.html">株式会社INDEP</a></h1>-->
<h1>
      <a href="index.html">
        <img src="images/logo company_white.png" width="150"  height="270" alt="">
      </a>
</h1>
<!--開閉ブロック-->
<div id="menubar">

<nav>
<ul>
<li><a href="index.html">ホーム</a></li>
<li><a href="company.html">会社概要</a></li>
<li><a href="">事業内容</a>
	<ul>
	<li><a href="service.html">事業内容</a></li>
	<li><a href="service1.html">システム分析・コンサルティング</a></li>
	<li><a href="service2.html">SAP ERP 導入・保守支援</a></li>
	<li><a href="service3.html">オープン系システム開発・保守支援</a></li>
	</ul>
</li>
<li><a href="contact.html">お問い合わせ</a></li>
</ul>
</nav>

<div class="sh">小さな端末でのみ表示させたいコンテンツがあればここに入れます。</div>

</div>
<!--/#menubar-->

</header>

<div id="contents">

<main>

<section>

<form>

<h2>お問い合わせ<span class="hosoku">Contact</span></h2>
<p>当ページと同じ３項目のお問い合わせフォーム（自動フォーム試用版）を簡単に使えるようにセットしています。<br>
<span class="color-check">※当ページ（contact.html）はフォームの見本ページです。実際の自動フォームには使いませんのでご注意下さい。</span></p>
<p><span class="color-check">自動フォームを使う場合（※編集に入る前にご確認下さい）</span><br>
あなたのメールアドレス設定と、簡単な編集だけで使えます。<a href="https://template-party.com/file/formgen_manual_set2.html" target="_blank">こちらのマニュアルをご覧下さい。</a></p>
<p><span class="color-check">自動フォームを使わない場合</span><br>
テンプレートに梱包されている「form.html」「confirm.html」「finish.html」の3枚のファイルを削除して下さい。</p>



<h2>送信完了</h2>
<p>送信を受け付けました。</p>


<table class="ta1">
<tr>
<th>お名前※</th>
<td><input type="text" name="お名前" size="30" class="ws"></td>
</tr>
<tr>
<th>メールアドレス※</th>
<td><input type="text" name="メールアドレス" size="30" class="ws"></td>
</tr>
<tr>
<th>お問い合わせ詳細※</th>
<td><textarea name="お問い合わせ詳細" cols="30" rows="10" class="wl"></textarea></td>
</tr>
</table>

<p class="c">
<input type="submit" value="内容を確認する">
</p>

</form>

</section>

</main>

</div>
<!--/#contents-->

<ul id="footermenu">
<li><a href="index.html">ホーム</a></li>
<li><a href="company.html">会社概要</a></li>
<li><a href="service.html">事業内容</a></li>
<li><a href="contact.html">お問い合わせ</a></li>
</ul>

<footer>
<small>Copyright&copy; <a href="index.html">INDEP Co., Ltd.</a> All Rights Reserved.</small>
</footer>

<!--ページの上部へ戻るボタン-->
<div class="pagetop"><a href="#"><i class="fas fa-angle-double-up"></i></a></div>

</div>
<!--/#container-->

<!--開閉ボタン（ハンバーガーアイコン）-->
<div id="menubar_hdr">
<span></span><span></span><span></span>
</div>

<!--jQueryの読み込み-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!--パララックス（inview）-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/protonet-jquery.inview/1.1.2/jquery.inview.min.js"></script>
<script src="js/jquery.inview_set.js"></script>

<!--このテンプレート専用のスクリプト-->
<script src="js/main.js"></script>

</body>
</html>
