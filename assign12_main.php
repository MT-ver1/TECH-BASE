<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: assign12_login.php");
    exit;
}

$dbname="testdb1";
$tb_user="user";
$tb_comment="comment";
$user=$_SESSION['user'];

echo"$user さまログイン中<br>";

//ログアウト処理
if(isset($_POST['logout']) && $_POST['logout']){
	session_destroy();
	echo_logout();
}

//メイン画面
else if(!isset($_POST['comment']) && !isset($_POST['re_num']) && !isset($_POST['dl_num'])){
	echo_main("","");
	exit;
}

//投稿内容登録処理
else if(isset($_POST['comment'])){
	echo_reg_comment($_POST['comment']);
	echo_main("","");
	exit;
}

//投稿内容編集処理
else if(isset($_POST['re_num']) && ($_POST['re_comment']=="")){
	$comment=echo_re_comment($_POST['re_num']);
	echo_main($_POST['re_num'],$comment);
	exit;
}
else if($_POST['re2']){
	echo_reg_recomment($_POST['re_num'],$_POST['re_comment']);
	echo_main("","");
	exit;
}

//投稿内容削除処理
else if(isset($_POST['dl_num'])){
	echo_delete_comment($_POST['dl_num']);
	echo_main("","");
	exit;
}

////////////////////////////////////////////////////////////////////////////////
//メイン画面
function echo_main($re_num,$comment)
{
	global $dbname,$tb_comment,$user;

	echo<<<EOT
<html>
	<head>
		<meta charset='utf-8'>
		<title>プロ３　最終課題</title>
	</head>
	<body>
EOT;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
		exit("Connect error!");
	}

	$result = mysqli_query($link,"SELECT * FROM $tb_comment where user = '$user'");
	if(!$result) {
    	exit("Select error on table ($tb_comment)!");
	}

	echo<<<EOT
		<form method="post" action="assign12_main.php">
			<input type="submit" name="logout" value="ログアウト">
		</form>
		
		<h1>ねこねこ掲示板</h1>
		<h3>投稿記事一覧<h3>
		<table border='1'>
		<tr><th>投稿番号</th><th>投稿内容</th><th>日時</th></tr>
EOT;

	while($assoc = mysqli_fetch_assoc($result)){

	echo<<<EOT
		<tr><td>{$assoc['id']}</td>
		<td>{$assoc['comment']}</td>
		<td>{$assoc['day']}</td></tr>
EOT;
	}

	mysqli_close($link);

	echo<<<EOT
	</table>
	
		<h3>新規記事投稿</h3>
		<form method="post" action="assign12_main.php">
			<textarea name="comment"></textarea><br>
			<input type="submit" name="btn" value="投稿">
		</form>

		<h3>記事編集</h3>
		編集したい記事番号を選んでください。<br>
		<form method="post" action="assign12_main.php">
			<input type="number" name="re_num" value="{$re_num}">
			<input type="submit" name="re1" value="編集"><br>
			編集：<br>
			<textarea name="re_comment">{$comment}</textarea><br>
			<input type="submit" name="re2" value="反映">
		</form>

		<h3>記事削除</h3>
		削除したい記事番号を選んでください。<br>
		<form method="post" action="assign12_main.php">
			<input type="number" name="dl_num" value=""><br>
			<input type="submit" name="btn" value="削除">
		</form>
	</body>
</html>
EOT;
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容登録処理
function echo_reg_comment($comment)
{
	global $dbname,$tb_comment,$user;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
		exit("Connect error!");
	}

	$date = date("Y-m-d H:i:s");
	$result = mysqli_query($link,"insert into $tb_comment set user='$user',comment='$comment', day='$date'");
	if(!$result) {
    	exit("Select error on table ($tb_comment)!");
	}

	mysqli_close($link);
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容編集処理
function echo_re_comment($re_num)
{
	global $dbname,$tb_comment,$user;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
		exit("Connect error!");
	}

	$result=mysqli_query($link,"SELECT * FROM $tb_comment where user = '$user'");
    if(!$result){
		exit("Select error on table ($tb_comment)!");
	} 

	while($row = mysqli_fetch_row($result)){
		if($row[0]==$re_num){
			$comment=$row[2];
			break;
		}
	}

	mysqli_close($link);
	return $comment;
}

////////////////////////////////////////////////////////////////////////////////
//投稿内容編集データ登録処理
function echo_reg_recomment($re_num,$comment)
{
	global $dbname,$tb_comment;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
		exit("Connect error!");
	}

	$date = date("Y-m-d H:i:s");
	$result = mysqli_query($link,"update $tb_comment set comment = '$comment', day = '$date' where id = '$re_num'");
	if(!$result) {
    	exit("update error on table ($tb_comment)!");
	}

	mysqli_close($link);
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容削除処理
function echo_delete_comment($dl_num)
{
	global $dbname,$tb_comment;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
		exit("Connect error!");
	}

	$result = mysqli_query($link,"delete from $tb_comment where id = '$dl_num'");
	if(!$result) {
    	exit("delete error on table ($tb_comment)!");
	}

	mysqli_close($link);
}
////////////////////////////////////////////////////////////////////////////////
//ログアウト処理
function echo_logout()
{
	echo<<<EOT
<html>
	<head>
		<meta charset="utf-8">
		<title>プロ３　最終課題</title>
	</head>
	<body>
		<form method="post" action="assign12_login.php">
			ログイン状態を切ります。<br>
			<input type="submit" value="ログイン画面へ">
		</form>
	</body>
</html>
EOT;
}
?>
