<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: assign12_login.php");
    exit;
}

$dbname="データベース名";
$tb_user="ユーザ名";
$tb_comment="テーブル名";
$user=$_SESSION['user'];

echo"$user さまログイン中<br>";

//ログアウト処理
if(isset($_POST['logout']) && $_POST['logout']){
	session_destroy();
	echo_logout();
}

//メイン画面
else if(!isset($_POST['dl_num'])){
	echo_main();
	exit;
}

//投稿内容削除処理
else if(isset($_POST['dl_num'])){
	echo_delete_comment($_POST['dl_num']);
	echo_main();
	exit;
}

////////////////////////////////////////////////////////////////////////////////
//メイン画面
function echo_main()
{
	global $dbname,$tb_comment;

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

	$result = mysqli_query($link,"SELECT * FROM $tb_comment");
	if(!$result) {
    	exit("Select error on table ($tb_comment)!");
	}

	echo<<<EOT
		<form method="post" action="assign12_main_root.php">
			<input type="submit" name="logout" value="ログアウト">
		</form>
		
		<h1>ねこねこ掲示板</h1>
		<h3>投稿記事一覧<h3>
		<table border='1'>
		<tr><th>投稿番号</th><th>ユーザ名</th><th>投稿内容</th><th>日時</th></tr>
EOT;

	while($assoc = mysqli_fetch_assoc($result)){

	echo<<<EOT
		<tr><td>{$assoc['id']}</td>
		<td>{$assoc['user']}</td>
		<td>{$assoc['comment']}</td>
		<td>{$assoc['day']}</td></tr>
EOT;
	}

	mysqli_close($link);

	echo<<<EOT
	</table><br><br>
	
		<h3>記事削除</h3>
		削除したい記事番号を選んでください。<br>
		<form method="post" action="assign12_main_root.php">
			<input type="number" name="dl_num" value=""><br>
			<input type="submit" name="btn" value="削除">
		</form>
	</body>
</html>
EOT;
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
