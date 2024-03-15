<?php

header('Content-Type: application/json');
header("Content-Type:text/html;charset=utf-8");

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

//구매하려고 하는 아이템 에셋이름
$m_itemAssetname = $_POST['m_itemAssetname'];

//구매하려고 하는 아이템 가격
$m_price = $_POST['m_price'];

$m_type = $_POST['m_type'];

//=======================================================구매 질의문 작성
//구매 알고리즘
//1) $m_itemAssetname으로 items 테이블에서 해당 아이템의 m_no값을 알아온다.
//2) memberinventory 테이블에 아이템을 추가한다.
//3) $m_email으로 member테이블에서 (m_money값 - $m_price) 저장한다.

//1) $m_itemAssetname으로 items 테이블에서 해당 아이템의 m_no값을 알아온다.
$select_query ="SELECT * FROM items WHERE m_assetname ='$m_itemAssetname' AND m_type = '$m_type'";
$result_query = mysqli_query($mysqli,$select_query);
$item_row = mysqli_fetch_array($result_query);
$m_item_m_no = $item_row['m_no'];

//2) memberinventory 테이블에 아이템을 추가한다.
$select_query =" INSERT INTO memberinventory(m_email,m_item,m_equip) VALUES('$m_email','$m_item_m_no', 0) ";
$result_query = mysqli_query($mysqli,$select_query);

//3) $m_email으로 member테이블에서 (m_money값 - $m_price) 저장한다.

$select_query ="SELECT * FROM member WHERE m_email ='$m_email'";
$result_query = mysqli_query($mysqli,$select_query);
$member_row = mysqli_fetch_array($result_query);
$member_money = $member_row['m_money'] - (int)$m_price;

$select_query ="UPDATE member SET m_money = $member_money WHERE m_email = '$m_email'";
$result_query = mysqli_query($mysqli,$select_query);

//===========================================================================구매함과 동시에 장착 시켜 준다.

$equip_query ="SELECT * FROM memberinventory WHERE m_email ='$m_email' AND m_equip = 1";
$equip_query = mysqli_query($mysqli,$equip_query);
while($equip_row = mysqli_fetch_array($equip_query))
{
    $item_query ="SELECT * FROM items WHERE m_no = $equip_row[m_item]";
    $item_query = mysqli_query($mysqli,$item_query);
    $item_row = mysqli_fetch_array($item_query);
    //드디어 찾았다. 자동차 종류이면서 장착하고있는 아이템 이라면
    if(strcmp($item_row['m_type'], $m_type) == 0 )
    {
        $update_query ="UPDATE memberinventory SET m_equip = 0 WHERE m_item = $equip_row[m_item] AND m_email = '$m_email'";
        mysqli_query($mysqli,$update_query);
    }
}

//memverinventory에서 알아온 고유번호의 아이템의 m_equip을 true로 바꾼다.
$select_query ="UPDATE memberinventory SET m_equip = 1 WHERE m_item = $m_item_m_no AND m_email = '$m_email'";
//질의한다.
$result_query = mysqli_query($mysqli,$select_query);

//배열 생성
$JObject = array();

//배열에 추가한다.
$JObject["m_money"] = $member_money;

//배열을 Json형태로 변환한다.
$returnJObject =  json_encode($JObject);

//클라이언트로 데이터를 Json형태를 보낸다.
echo $returnJObject;

//mysql 연결 종료
mysqli_close($mysqli);

?>

