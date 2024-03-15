<?php

// mysql 접속 호스트(인터넷에 연결 되어있는 컴퓨터) 설정
$host = "localhost";
// 사용자
$user = "서버운영종료";
// 사용자 비밀번호
$password = "서버운영종료";
// 데이터베이스 이름
$DB_name = "racing";
//mysql + php 연동 -> DB 연결
$mysqli = mysqli_connect($host, $user, $password, $DB_name);
//연결 안됬을시 로그 남기는 부분
if($mysqli == null) return mysqli_error($mysqli);

//클라이언트로 넘어온데이터 이때 넘어오는 데이터는 이메일 / 비밀번호
$email = $_POST['email'];

$pw = $_POST['pw'];


if(empty($email) == false && empty($pw) == false)
{
    //이메일 하고 비밀번호가 맞는지 질의한다.
    $select_query ="SELECT * FROM member WHERE m_email ='$email' AND  m_pw ='$pw'";
    $result = mysqli_query($mysqli,$select_query);

    if($result->num_rows == 0)
    {
        echo "false";
    }
    //해당 값이 있으면 로그인하고 비밀번호가 맞는것이기 때문에 로그인 한다.
    else
    {
        echo "true";
    }
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

