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


<form action="confirm.php" method="post" id="genForm"><h2>お問い合わせフォーム</h2><table class="ta1">
<tr><th>お名前<span class=\"req\">*</span></th><td><input type="text" name="f_688353292a27d" class="wl" required /></td></tr>
<tr><th>メールアドレス<span class=\"req\">*</span></th><td><input type="text" name="f_688353292a285" class="wl" required /></td></tr>
<tr><th>お問い合わせ詳細<span class=\"req\">*</span></th><td><textarea name="f_688353292a288" rows="10" class="wl" required></textarea></td></tr>
</table><input type="hidden" name="__labels" value='{"f_688353292a27d":"お名前","f_688353292a285":"メールアドレス","f_688353292a288":"お問い合わせ詳細"}'>
<input type="hidden" name="__required" value="f_688353292a27d,f_688353292a285,f_688353292a288">
<input type="hidden" name="__replyto" value="f_688353292a285">
<p class="c"><button type="submit">確認画面へ</button></p></form><script>
/* 郵便番号検索 */
document.addEventListener('click', e=>{
    if(!e.target.matches('.lookup')) return;
    const blk=e.target.closest('.addr-block');
    const zip=blk.querySelector('.zip').value.replace(/\D/g,'');
    if(zip.length!==7){alert('郵便番号を正しく入力してください');return;}
    fetch('https://zipcloud.ibsnet.co.jp/api/search?zipcode='+zip)
      .then(r=>r.json()).then(d=>{
          if(d.status===200&&d.results){
              const r=d.results[0];
              blk.querySelector('.pref').value  = r.address1;
              blk.querySelector('.addr1').value = r.address2+r.address3;
          }else alert('住所が見つかりません');
      }).catch(()=>alert('通信エラーで検索に失敗しました'));
});

/* チェックボックス 1 つ以上必須 */
document.getElementById('genForm').addEventListener('submit',e=>{
    for(const g of e.target.querySelectorAll('.chk-group[data-required="1"]')){
        if(!g.querySelector('input[type=checkbox]:checked')){
            alert('「'+g.dataset.label+'」を1つ以上選択してください');
            g.scrollIntoView({behavior:'smooth', block:'center'});
            e.preventDefault(); return;
        }
    }
});
</script>

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
