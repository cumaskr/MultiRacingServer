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


//$select_query ="INSERT INTO items(m_type,m_name,m_price,m_carname) VALUES('wing','michelin2',1000,'프로펠라')";
//$result = mysqli_query($mysqli,$select_query);

//$select_query ="UPDATE items SET m_carname='옐로우' WHERE m_no=4";
//$result = mysqli_query($mysqli,$select_query);



var_dump("ㅎㅇ");

//mysql 연결 종료
mysqli_close($mysqli);

?>

