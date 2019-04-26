<?php
session_start();

$dbname="testdb1";
$tb_user="user";
$tb_comment="comment";

//初期画面
if(!isset($_POST['user']) && !isset($_POST['mk_user']) && !isset($_POST['dl_user'])){

	echo_login_page();
	exit;
}

/*ログイン処理*/
else if(isset($_POST['user'])){
	//ログイン画面で打ったユーザー名とパスワードを受け取る
	$user = $_POST['user'];
	$pass = $_POST['pass'];

	//ユーザ名とパスワードの照会
	$user_exist=echo_login_check($user,$pass);
	if($user_exist=='true'){
		$_SESSION['user'] = "$user";
		$_SESSION['pass'] = "$pass";
		if($user=='root') echo_trans_main_root();
		else echo_trans_main();
		exit;
	}else{
		echo"ユーザ名もしくはパスワードが間違っています。";
		echo_login_page();
		exit;
	}
}

/*アカウント登録処理*/
else if(isset($_POST['mk_user'])){
	$user = $_POST['mk_user'];
	$pass = $_POST['mk_pass'];
	echo_make_account($user, $pass);
	echo_login_page();
	exit;
}

/*アカウント削除処理*/
else if(isset($_POST['dl_user'])){
	$user = $_POST['dl_user'];
	$pass = $_POST['dl_pass'];
	echo_delete_account($user, $pass);
	echo_login_page();
	exit;
}
////////////////////////////////////////////////////////////////////////////////
//ログイン画面
function echo_login_page()
{
	echo<<<EOT
<html>
	<head>
		<meta charset = "utf-8">
		<title>プロ3　最終課題</title>
	</head>
	<body>
		<!--会員ログインフォーム-->
		<h1>ねこねこ掲示板会員ログインページ</h1>
		<h3>ログイン（会員登録済みの方）</h3>
		<form method="POST" action="assign12_login.php">
			username <input type="text" name="user" value="" placeholder="ログインＩＤを入力してください" size="40"><br>
			password <input type="password" name="pass" value="" placeholder="パスワードを入力してください" size="40"><br>
			<input type="submit" name="login" value="ログイン"><br><br>
		</form>
		<!--新規会員登録フォーム-->
		<h3>新規会員登録</h3>
		<form method="POST" action="assign12_login.php">
			username <input type="text" name="mk_user" value="" placeholder="ログインＩＤを入力してください" size="40"><br>
			password <input type="password" name="mk_pass" value="" placeholder="パスワードを入力してください" size="40"><br>
			<input type="submit" name="reg" value="登録"><br><br>
		</form>
		<!--会員情報削除フォーム-->
		<h3>会員情報削除</h3>
		<form method="POST" action="assign12_login.php">
			username <input type="text" name="dl_user" value="" placeholder="ログインＩＤを入力してください" size="40"><br>
			password <input type="password" name="dl_pass" value="" placeholder="パスワードを入力してください" size="40"><br>
			<input type="submit" name="dl" value="削除"><br><br>
		</form>
	<body>
</html>
EOT;
}

////////////////////////////////////////////////////////////////////////////////
//アカウント認証
function echo_login_check($user,$pass)
{
	global $dbname,$tb_user;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
    	exit("Connect error!");
	}

	$result = mysqli_query($link,"SELECT * FROM $tb_user");
	if(!$result) {
    	exit("Select error on table ($tb_user)!");
	}

	$user_exist = 'false';
	while($assoc = mysqli_fetch_assoc($result)){
		if(($assoc['user']==$user) &&($assoc['pass']==$pass)){
			$user_exist = 'true';
			break;
		}
	}

	mysqli_free_result($result);
	mysqli_close($link);

	return $user_exist;
}
////////////////////////////////////////////////////////////////////////////////
//アカウント登録処理
function echo_make_account($user,$pass)
{
	global $dbname,$tb_user;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
    	exit("Connect error!");
	}

	$result = mysqli_query($link,"SELECT * FROM $tb_user");
	if(!$result) {
    	exit("Select error on table ($tb_user)!");
	}

	$user_exist = 'false';
	while($assoc = mysqli_fetch_assoc($result)){
		if ($assoc['user'] == $user){
			$user_exist = 'true';
			echo"そのユーザ名は既に使用されています\n<br>";
			echo"別のユーザ名で再登録してください。<br>";
			break;
		}
	}

	if($user_exist=='false'){
		$result=mysqli_query($link,"insert into {$tb_user} set user='{$user}', pass='{$pass}'");
		if(! $result) exit("INSERT failed!\n<br>");
		else echo "ユーザ追加完了。ログインしてください。<br>\n";
	}

	mysqli_close($link);
}
////////////////////////////////////////////////////////////////////////////////
//アカウント削除
function echo_delete_account($user,$pass)
{
	global $dbname,$tb_user;

	$link = mysqli_connect('localhost','root','dbpass',$dbname);
	if(! $link) {
    	exit("Connect error!");
	}

	$result = mysqli_query($link,"SELECT * FROM $tb_user");
	if(!$result) {
    	exit("Select error on table ($tb_user)!");
	}

	$user_exist = 'false';
	while($assoc = mysqli_fetch_assoc($result)){
		if(($assoc['user']==$user) &&($assoc['pass']==$pass)){
			$user_exist = 'true';
			$result = mysqli_query($link,"delete from $tb_user where user='$user'");
			if(!$result) {
				exit("delete error on table ($tb_user)!");
			}
			break;
		}
	}

	if($user_exist == 'false') echo"指定の会員情報が見つからず削除できませんでした。\n<br>";
	else echo"会員情報削除完了\n<br>";

	mysqli_close($link);
}
////////////////////////////////////////////////////////////////////////////////
//管理者用メイン画面へ遷移
function echo_trans_main_root(){
	echo<<<EOT
<html>
	<head>
		<meta charset = "utf-8">
		<title>プロ3　最終課題</title>
	</head>
	<body>
		<h1>ねこねこ掲示板</h1>
		<form method="POST" action="assign12_main_root.php">
			<input type="submit" name="go" value="管理者画面へ"><br><br>
		</form>
	</body>
</html>
EOT;
}
////////////////////////////////////////////////////////////////////////////////
//ユーザ用メイン画面へ遷移
function echo_trans_main(){
	echo<<<EOT
<html>
	<head>
		<meta charset = "utf-8">
		<title>プロ3　最終課題</title>
	</head>
	<body>
		<h1>ねこねこ掲示板</h1>
		<form method="POST" action="assign12_main.php">
			<input type="submit" name="go" value="メイン画面へ"><br><br>
		</form>
	</body>
</html>
EOT;
}
?>
