<?php
session_start();
require('dbconnect.php');

//ログインしてない状態でのアクセス禁止
if(!isset($_SESSION['47_LearnSNS']['id'])){
    header('Location: signin.php');
    exit();
 }

$sql = 'SELECT * FROM `users` WHERE `id` = ?';
$data= [$_SESSION['47_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);


//エラー内容を保持する配列
$errors = [];
//投稿ボタンが押された時(POST送信された時)
if(!empty($_POST)){
    //投稿処理
    //feedsテーブルに値を登録しよう
    //登録する時はfeed,user_id.createdの3つ
    $feed = $_POST['feed'];
    //投稿のからチェック
      if($feed != ''){
 //投稿処理
      $sql = 'INSERT INTO `feeds`(`feed`,`user_id`,`created`) VALUES(?,?,NOW())';
      $data = [$feed,$signin_user['id']];
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);

     header('Location: timeline.php');
     exit();

      }else{
      $errors['feed'] = 'blank';
      }
  }
//1.投稿情報を全て取得

$sql = 'SELECT `f`.*,`u`.`name`,`u`.`img_name`
     FROM`feeds` AS `f`
     LEFT JOIN `users` AS `u`
     ON `f`.`user_id`=`u`.`id` ORDER BY `created` DESC';
$data= [$_SESSION['47_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute();

//投稿城ふ全てを入れる配列定義
$feeds =[];
while(true){
    //recordは要するにfeed一件の情報
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    if($record==false){
        //レコードが取れえなくなったらwhileを抜ける
        break;
    }
    $feeds[] = $record;
}
//$feedsを元にHTML内に投稿内容、投稿日時、ユーザー名、ユーザー画像を表示


?>
<?php include('layouts/header.php'); ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <!--
        include(ファイル名);
        指定したファイルを組み込んで表示
        共通部分を切り出して使いたいページから読み込む
    -->
    <?php include('navbar.php'); ?>
      <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
                    <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
                </ul>
            </div>
            <div class="col-xs-9">
                <div class="feed_form thumbnail">
                    <form method="POST" action="">
                        <div class="form-group">
                            <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
                             <?php if(isset($errors['feed']) && $errors['feed'] == 'blank'):?>
                                    <p class="text-danger">内容を投稿してください</p>
                              <?php endif;?>
                        <input type="submit" value="投稿する" class="btn btn-primary">
                        </div>
                    </form>
                </div>
                <?php foreach($feeds as $feed):?>
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/<?php echo $feed['img_name']?>" width="40px">
                        </div>
                        <div class="col-xs-11">
                            <a href="profile.php" style="color: #7f7f7f;">
                                <?php echo $feed['name'];?>
                                <?php echo $feed['created'];?>
                        </div>
                    </div>
                    <div class="row feed_content">
                        <div class="col-xs-12">
                            <span style="font-size: 24px;">
                                <?php echo $feed['feed']?>
                            </span>
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <button class="btn btn-default">いいね！</button>
                            いいね数：
                            <span class="like-count">10</span>
                            <a href="#collapseComment" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：5</span>
                            <a href="edit.php" class="btn btn-success btn-xs">編集</a>
                            <a onclick="return confirm('ほんとに消すの？');" href="#" class="btn btn-danger btn-xs">削除</a>
                        </div>
                        <?php include('comment_view.php'); ?>
                    </div>
                </div>
            <?php endforeach;?>
                <div aria-label="Page navigation">
                    <ul class="pager">
                        <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include('layouts/footer.php'); ?>
</html>
