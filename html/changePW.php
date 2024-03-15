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

//클라이언트로 넘어온데이터 이때 넘어오는 데이터는 중복확인이냐? / 가입완료냐? 를 판단하는
$email = $_POST['email'];
$newpw = $_POST['newpw'];

$select_query ="SELECT * FROM member WHERE m_email ='$email'";
$result = mysqli_query($mysqli,$select_query);

//중복 된 이메일이없다면 비밀번호 찾기가 안되므로 false를 반환한다. -> 클라이언트에서 안내 메세지 띄운다.
if($result->num_rows == 0)
{
    echo "false";
}
//중복 된 이메일이 있다면 이메일로 비밀번호 전송후 true를 반환한다. -> 클라이언트에서 안내 메세지 띄운다.
else
{
    //MySQL에 비밀번호 업데이트
    $select_query ="UPDATE member SET m_pw = '$newpw' WHERE m_email ='$email'";
    $result = mysqli_query($mysqli,$select_query);
    echo "true";
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

