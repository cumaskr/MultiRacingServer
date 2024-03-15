<?php

//=======================================================Json을 쓰기위한 셋팅을 한다.
header('Content-Type: application/json');
header("Content-Type:text/html;charset=utf-8");

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

//로그인한 이메일 정보가 'post_loginID'에 담겨 넘어온다.
$login_id = $_POST['email'];

$m_carname = $_POST['m_carAssetname'];
$m_wheelname = $_POST['m_wheelAssetname'];
$m_wingname = $_POST['m_wingAssetname'];

//=======================================================질의문 작성

$select_query ="SELECT * FROM items WHERE m_assetname ='$m_carname' AND m_type = 'car'";
$result_query = mysqli_query($mysqli,$select_query);
$item_row = mysqli_fetch_array($result_query);
$m_carItemNo = $item_row['m_no'];

$select_query ="SELECT * FROM items WHERE m_assetname ='$m_wheelname' AND m_type = 'wheel'";
$result_query = mysqli_query($mysqli,$select_query);
$item_row = mysqli_fetch_array($result_query);
$m_wheelItemNo = $item_row['m_no'];


$select_query ="SELECT * FROM items WHERE m_assetname ='$m_wingname'AND m_type = 'wing'";
$result_query = mysqli_query($mysqli,$select_query);
$item_row = mysqli_fetch_array($result_query);
$m_wingItemNo = $item_row['m_no'];

$JObject = array();

$select_query ="SELECT * FROM stat WHERE m_item ='$m_carItemNo'";
$result_query = mysqli_query($mysqli,$select_query);
$stat_row = mysqli_fetch_array($result_query);

$car = array(
    "m_topspeed" => $stat_row['m_topspeed'],
    "m_acceleration" => $stat_row['m_acceleration'],
    "m_handling" => $stat_row['m_handling']
);
$JObject["m_carstat"] = $car;


$select_query ="SELECT * FROM stat WHERE m_item ='$m_wheelItemNo'";
$result_query = mysqli_query($mysqli,$select_query);
$stat_row = mysqli_fetch_array($result_query);

$wheel = array(
    "m_topspeed" => $stat_row['m_topspeed'],
    "m_acceleration" => $stat_row['m_acceleration'],
    "m_handling" => $stat_row['m_handling']
);

$JObject["m_wheelstat"] = $wheel;

$select_query ="SELECT * FROM stat WHERE m_item ='$m_wingItemNo'";
$result_query = mysqli_query($mysqli,$select_query);
$stat_row = mysqli_fetch_array($result_query);

$wing = array(
    "m_topspeed" => $stat_row['m_topspeed'],
    "m_acceleration" => $stat_row['m_acceleration'],
    "m_handling" => $stat_row['m_handling']
);

$JObject["m_wingstat"] = $wing;


//Json배열을 Json형태로 변환한다.
$returnJObject =  json_encode($JObject);

//클라이언트로 데이터를 Json형태를 보낸다.
echo $returnJObject;

//mysql 연결 종료
mysqli_close($mysqli);

?>

