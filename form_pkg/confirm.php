<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: form.php'); exit; }

/* ---- 1. トークン生成 ---- */
$token = bin2hex(random_bytes(16));
$_SESSION['form_token'] = $token;

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function ew($s,$suf){ return substr($s,-strlen($suf)) === $suf; }
$labels = json_decode($_POST['__labels'] ?? '[]', true);
$done   = [];
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



<form action="send.php" method="post"><h2>確認画面</h2>
<table class="ta1">
<?php foreach ($_POST as $k => $v):
    if ($k==='__labels' || $k==='__replyto' || $k==='__required' || in_array($k,$done,true)) continue;

    /* 住所まとめ（3 項目とも空なら非表示） */
    if (ew($k,'_zip')){
        $b    = substr($k,0,-4);
        $zip  = $_POST[$b.'_zip']  ?? '';
        $pref = $_POST[$b.'_pref'] ?? '';
        $addr = $_POST[$b.'_addr'] ?? '';

        if (trim($zip.$pref.$addr) === ''){
            $done = array_merge($done,["{$b}_zip","{$b}_pref","{$b}_addr"]);
            continue;
        }

        echo '<tr><th>'.h($labels[$k]??'住所').'</th><td>';
        echo h($zip).'<br>';
        echo h($pref.$addr).'</td></tr>';
        $done = array_merge($done,["{$b}_zip","{$b}_pref","{$b}_addr"]);
        continue;
    }

    /* 年月日まとめ（3 項目とも空なら非表示） */
    if (ew($k,'_y')){
        $b = substr($k,0,-2);
        $y = $_POST[$b.'_y'] ?? '';
        $m = $_POST[$b.'_m'] ?? '';
        $d = $_POST[$b.'_d'] ?? '';

        if (trim($y.$m.$d) === ''){
            $done = array_merge($done,["{$b}_y","{$b}_m","{$b}_d"]);
            continue;
        }

        $heading = preg_replace('/\s*[年月日]\z/u','',$labels[$k]??'');
        echo '<tr><th>'.h($heading).'</th><td>';
        echo h("{$y}年{$m}月{$d}日").'</td></tr>';
        $done = array_merge($done,["{$b}_y","{$b}_m","{$b}_d"]); continue;
    }

    /* 月日まとめ（2 項目とも空なら非表示） */
    if (ew($k,'_d_m')){
        $b = substr($k,0,-4);
        $m = $_POST[$b.'_d_m'] ?? '';
        $d = $_POST[$b.'_d_d'] ?? '';

        if (trim($m.$d) === ''){
            $done = array_merge($done,["{$b}_d_m","{$b}_d_d"]);
            continue;
        }

        $heading = preg_replace('/\s*[年月日]\z/u','',$labels[$k]??'');
        echo '<tr><th>'.h($heading).'</th><td>';
        echo h("{$m}月{$d}日").'</td></tr>';
        $done = array_merge($done,["{$b}_d_m","{$b}_d_d"]); continue;
    }

    /* 通常（空なら非表示） */
    $vals = is_array($v) ? array_filter($v,'strlen') : [trim($v)];
    if (count($vals) === 0 || $vals[0] === '') continue;

    echo '<tr><th>'.h($labels[$k]??$k).'</th><td>';
    foreach ($vals as $sv) echo nl2br(h($sv)).'<br>';
    echo '</td></tr>';
endforeach; ?>
</table>

<input type="hidden" name="__labels" value="<?=h($_POST['__labels'])?>">
<input type="hidden" name="__token"  value="<?=h($token)?>">
<?php foreach ($_POST as $k=>$v):
    if ($k==='__labels') continue;
    if (is_array($v)):
        foreach ($v as $sv)
            echo '<input type="hidden" name="'.$k.'[]" value="'.h($sv).'">';
    else:
        echo '<input type="hidden" name="'.$k.'" value="'.h($v).'">';
    endif;
endforeach; ?>
<p class="c">
    <button type="button" onclick="history.back()">修正する</button>
    <button type="submit">送信する</button>
</p>
</form>


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
