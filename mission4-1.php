<?php

$dsn='データベース名';
$user='ユーザ名';
$password='パスワード';

//メイン画面
if(!isset($_POST['name']) && empty($_POST['ch_num']) && empty($_POST['ch_pass']) && empty($_POST['del_num']) && empty($_POST['del_pass'])){
//echo "メイン画面<br>";
	echo_main("","","","");
	exit;
}

//投稿内容登録処理
else if(!empty($_POST['pass'])){
//echo "投稿内容登録処理<br>";
	echo_reg_comment($_POST['name'],$_POST['txt'],$_POST['pass']);
	echo_main("","","","");
	exit;
}

//投稿内容編集処理
else if(!empty($_POST['ch_num']) && !empty($_POST['ch_pass']) && !isset($_POST['ch2'])){
//echo "投稿内容編集処理<br>";
	$parts=echo_re_comment($_POST['ch_num'],$_POST['ch_pass']);
	echo_main($parts[0],$parts[1],$parts[2],$parts[3]);
	exit;
}
else if(isset($_POST['ch2'])){
//echo "登録<br>";
	echo_reg_recomment($_POST['ch_num'],$_POST['ch_name'],$_POST['ch_txt'],$_POST['ch_pass']);
	echo_main("","","","");
	exit;
}

//投稿内容削除処理
else if(!empty($_POST['del_num']) && !empty($_POST['del_pass'])){
//echo "投稿内容削除処理<br>";
	echo_delete_comment($_POST['del_num'],$_POST['del_pass']);
	echo_main("","","","");
	exit;
}

//その他のエラー処理
else{
	echo "エラー<br>";
	echo_main("","","","");
	exit;
}
////////////////////////////////////////////////////////////////////////////////
//メイン画面
function echo_main($num,$name,$txt,$pass)
{
	global $dsn,$user,$password;

	echo<<<EOT
<html>
	<head>
		<meta charset='utf-8'>
		<title>mission4</title>
	</head>
	<body>
EOT;

	//データベース接続
	try{
		//データベース接続($pdoはpdoクラス?のインスタンス)
		$pdo = new PDO($dsn,$user,$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_EMULATE_PREPARES => false,             //SQLインジェクション対策で静的プレースホルダを使用
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' //dsnに文字エンコードを指定できるのはPHP5.3.6以降
					)
		);

		echo<<<EOT
		<h1>ねこねこ掲示板</h1>
		<h3>投稿記事一覧<h3>
		<table border='1'>
			<tr><th>投稿番号</th><th>あだ名</th><th>投稿内容</th><th>パスワード</th><th>日時</th></tr>
EOT;
		//データベースのデータ取得&表示処理
		$result = $pdo -> query('SELECT * FROM mission4 ORDER BY id ASC');               //SQL文を実行&データ取得?
		foreach($result as $row){

		echo<<<EOT
			<tr>
				<td>{$row['id']}</td>
				<td>{$row['name']}</td>
				<td>{$row['txt']}</td>
				<td>********</td>
				<td>{$row['day']}</td>
			</tr>
EOT;
		}

		//データベース接続切断
		$result=null;
		$pdo=null;

	} catch (PDOException $e) {
		exit($e ->getMessage());
	}

	echo<<<EOT
		</table>
		<p>
			<h2>新規入力欄</h2>
			<form method="post" action="mission4-1.php">
				名前：<br>
				<input type="text" name="name" size="10"><br>
				コメント：<br>
				<input type="text" name="txt" size="30"><br>
				パスワード：<br>
				<input type="password" name="pass" size="20"><br>

				<br>
				<input type="submit" value="投稿"><br><br>
			</form>
		</p>
		<p>
			<h2>登録項目編集欄</h2>
			<form method="post" action="mission4-1.php">
				編集したい投稿番号とそのパスワードを記入してください。<br>
				投稿番号：<br>
				<input type="number" name="ch_num" value="{$num}"><br>
				パスワード：<br>
				<input type="password" name="ch_pass" value="{$pass}">
				<input type="submit" name='ch1' value="編集"><br><br>
				名前：<br>
				<input type="text" name="ch_name" size="10" value="{$name}"><br>
				投稿内容：<br>
				<input type="text" name="ch_txt" size="30" value="{$txt}"><br>
				<input type="submit" name="ch2" value="反映"><br><br>
			</form>
		</p>
		<p>
			<h2>登録項目削除欄</h2>
			<form method="post" action="mission4-1.php">
				削除したい投稿番号とそのパスワードを記入してください。<br>
				投稿番号：<br>
				<input type="number" name="del_num"><br>
				パスワード：<br>
				<input type="password" name="del_pass">
				<input type="submit" value="削除"><br><br>
			</form>
		</p>
	</body>
</html>
EOT;
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容登録処理
function echo_reg_comment($name,$txt,$pass)
{
	global $dsn,$user,$password;

	//データベース接続
	try{
		//データベース接続($pdoはpdoクラス?のインスタンス)
		$pdo = new PDO($dsn,$user,$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_EMULATE_PREPARES => false,             //SQLインジェクション対策で静的プレースホルダを使用
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' //dsnに文字エンコードを指定できるのはPHP5.3.6以降
					)
		);

		//データベースにデータを登録
		$prepare = $pdo -> prepare('insert into mission4 (name,txt,pass) values (:name,:txt,:pass)'); //SQL分を実行する準備
		$prepare->bindParam(':name',$name, PDO::PARAM_STR);                                           //値をバウンドする
		$prepare->bindParam(':txt',$txt, PDO::PARAM_STR);                                             //値をバウンドする
		$prepare->bindParam(':pass',$pass, PDO::PARAM_STR);                                           //値をバウンドする
		$prepare->execute();                                                                          //プリペアードステートメントを実行

		//データベース接続切断
		$pdo=null;

	} catch (PDOException $e) {
		exit($e ->getMessage());
	}
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容編集処理
function echo_re_comment($ch_num,$ch_pass)
{
	global $dsn,$user,$password;

	//データベース接続
	try{
		//データベース接続($pdoはpdoクラス?のインスタンス)
		$pdo = new PDO($dsn,$user,$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_EMULATE_PREPARES => false,             //SQLインジェクション対策で静的プレースホルダを使用
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' //dsnに文字エンコードを指定できるのはPHP5.3.6以降
					)
		);

		//データベースのデータを取得して表示する
		$prepare = $pdo -> prepare('SELECT * FROM mission4 WHERE id = :ch_num AND pass = :ch_pass'); //SQL文の実行の準備
		$prepare -> bindParam(':ch_num',$ch_num,PDO::PARAM_INT);                                     //値をバウンドする
		$prepare -> bindParam(':ch_pass',$ch_pass,PDO::PARAM_STR);                                   //値をバウンドする
		$prepare -> execute();                                                                       //プリペアードステートメントを実行
		$result = $prepare -> fetch(PDO::FETCH_ASSOC);                                               //データを配列で取得

		//戻り値作成
		$parts0=$result['id'];
		$parts1=$result['name'];
		$parts2=$result['txt'];
		$parts3=$result['pass'];

		//データベース接続切断
		$result=null;
		$pdo=null;

	} catch (PDOException $e){
		exit($e ->getMessage());
	}

	return array($parts0,$parts1,$parts2,$parts3);

}

////////////////////////////////////////////////////////////////////////////////
//投稿内容編集データ登録処理
function echo_reg_recomment($ch_num,$ch_name,$ch_txt,$ch_pass)
{
	global $dsn,$user,$password;

	//データベース接続
	try{
		//データベース接続($pdoはpdoクラス?のインスタンス)
		$pdo = new PDO($dsn,$user,$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_EMULATE_PREPARES => false,             //SQLインジェクション対策で静的プレースホルダを使用
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' //dsnに文字エンコードを指定できるのはPHP5.3.6以降
					)
		);

		//データベースにデータを登録
		$prepare = $pdo -> prepare('UPDATE mission4 SET name = :ch_name, txt = :ch_txt WHERE id = :ch_num AND pass = :ch_pass'); //SQL分を実行する準備
		$prepare->bindParam(':ch_name',$ch_name, PDO::PARAM_STR);                                           //値をバウンドする
		$prepare->bindParam(':ch_txt',$ch_txt, PDO::PARAM_STR);                                             //値をバウンドする
		$prepare->bindParam(':ch_num',$ch_num, PDO::PARAM_INT);                                             //値をバウンドする
		$prepare->bindParam(':ch_pass',$ch_pass, PDO::PARAM_STR);                                           //値をバウンドする
		$prepare->execute();                                                                          //プリペアードステートメントを実行

		//データベース接続切断
		$pdo=null;

	} catch (PDOException $e) {
		exit($e ->getMessage());
	}
}
////////////////////////////////////////////////////////////////////////////////
//投稿内容削除処理
function echo_delete_comment($del_num,$del_pass)
{
	global $dsn,$user,$password;

	//データベース接続
	try{
		//データベース接続($pdoはpdoクラス?のインスタンス)
		$pdo = new PDO($dsn,$user,$password,
					array(
						PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_EMULATE_PREPARES => false,             //SQLインジェクション対策で静的プレースホルダを使用
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' //dsnに文字エンコードを指定できるのはPHP5.3.6以降
					)
		);

		//データベースにデータを登録
		$prepare = $pdo -> prepare('DELETE FROM mission4 WHERE id = :del_num AND pass = :del_pass');   //SQL分を実行する準備                                            //値をバウンドする
		$prepare->bindParam(':del_num',$del_num, PDO::PARAM_INT);                                        //値をバウンドする
		$prepare->bindParam(':del_pass',$del_pass, PDO::PARAM_STR);                                      //値をバウンドする
		$prepare->execute();                                                                             //プリペアードステートメントを実行

		//データベース接続切断
		$pdo=null;

	} catch (PDOException $e) {
		exit($e ->getMessage());
	}
}
?>
