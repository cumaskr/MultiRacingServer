<?php

//=======================================================MySQL을 쓰기위한 셋팅을 한다.
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

//=======================================================멤버 변수

//로그인한 유저 이메일
$m_email = $_POST['email'];

//바꾸기 전 자동차(장착되어있는 자동차)
$m_nowCarbutton_name = $_POST['m_nowCarbutton_name'];

//바꿀  자동차(장착할 자동차)
$m_selectCarbutton_name = $_POST['m_selectCarbutton_name'];

$m_type = $_POST['m_type'];

//=======================================================질의문 작성
//알고리즘
//기존에 장착중이던 차 Equip은 false로
//1) $m_nowCarbutton_name이름의 아이템 고유 번호를알아온다.
//2) memverinventory에서 알아온 고유번호의 아이템의 m_equip을 false로 바꾼다.

//Post로 넘어온 m_no의 차 Equip은 true로
//1) $m_selectCarbutton_name이름의 아이템 고유 번호를알아온다.
//2) memverinventory에서 알아온 고유번호의 아이템의 m_equip을 true로 바꾼다.

//$m_prev_name이름의 아이템 고유 번호를알아온다. 해당 타입만 바꾼다.
//같은 아이템이름(default등이 겹치기때문에 타입으로 구분한다)
$select_query ="SELECT * FROM items WHERE m_assetname = '$m_nowCarbutton_name' AND m_type = '$m_type'";

$result_query = mysqli_query($mysqli,$select_query);

$row = mysqli_fetch_array($result_query);
//$m_nowCarbutton_no 아이템 고유 번호 담는다.
$m_nowCarbutton_no = $row['m_no'];



//$m_select_name이름의 아이템 고유 번호를알아온다.
$select_query ="SELECT * FROM items WHERE m_assetname = '$m_selectCarbutton_name' AND m_type = '$m_type'";

$result_query = mysqli_query($mysqli,$select_query);

$row = mysqli_fetch_array($result_query);
//$m_selectCarbutton_no에 아이템 고유 번호 담는다.
$m_selectCarbutton_no = $row['m_no'];



//memverinventory에서 이전 차의 알아온 고유번호의 아이템의 m_equip을 false로 바꾼다.
$select_query ="UPDATE memberinventory SET m_equip = 0 WHERE m_item = $m_nowCarbutton_no AND m_email = '$m_email'";
//질의한다.
$result_query = mysqli_query($mysqli,$select_query);

//memverinventory에서 알아온 고유번호의 아이템의 m_equip을 true로 바꾼다.
$select_query ="UPDATE memberinventory SET m_equip = 1 WHERE m_item = $m_selectCarbutton_no AND m_email = '$m_email'";
//질의한다.
$result_query = mysqli_query($mysqli,$select_query);

//mysql 연결 종료
mysqli_close($mysqli);

?>

