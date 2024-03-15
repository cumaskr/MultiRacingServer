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

//=======================================================질의문 작성

//질의문 만든다.
$select_query ="SELECT * FROM member WHERE m_email ='$login_id'";
//질의한다.
$result_query = mysqli_query($mysqli,$select_query);

//혹시나 로그인한 이메일 정보가 없다면 이런일은 발생하지 않지만 혹시 몰라서 예외처리
if($result_query->num_rows == 0)
{
    echo "false";
}
//로그인한 이메일 정보를 찾았으면 해당 유저의 데이터를 반환해준다.
else
{
    $member_row = mysqli_fetch_array($result_query);

    $JObject = array();
    $wheels = array();

    //회원의 닉네임 정보 넣어준다.
    $JObject["m_nickname"] = $member_row['m_nickname'];
    $JObject["m_money"] = $member_row['m_money'];

    //Items테이블 전체를 불러온다.
    $item_query ="SELECT * FROM items";

    $item_query = mysqli_query($mysqli,$item_query);

    while($item_row = mysqli_fetch_array($item_query))
    {
        //해당 유저가 가지고있는 아이템에 대한 정보를 얻기 위해 items 테이블에 질의한다.
        //회원 인벤토리 목록에서 해당유저가 해당 아이템을 가지고 있냐? 있으면 이미 구입한 상품이다.
        $memberInventory_query ="SELECT * FROM memberinventory WHERE m_item = $item_row[m_no] AND m_email = '$login_id' ";

        $memberInventory_query = mysqli_query($mysqli,$memberInventory_query);

        $memberInventory_isIn = mysqli_num_rows($memberInventory_query);

        $memberInventory_row = mysqli_fetch_array($memberInventory_query);

        //아이템 목록을 전체 돌면서 우성 바퀴만 처리해준다. 바퀴는 다 가져와야한다.
        if(strcmp($item_row['m_type'],'wheel') == 0)
        {
            //아이템 테이블에 있고 회원인벤토리에는 있는거라면 해당 아이템은 구매 하였다.
            if($memberInventory_isIn != 0)
            {
                //배열을 만든다.
                $wheel = array(
                    "m_no" => $item_row['m_no'],
                    "m_equip" => $memberInventory_row['m_equip'],
                    "m_assetname" => $item_row['m_assetname'],
                    "m_name" => $item_row['m_name'],
                    "m_price" => $item_row['m_price'],
                    "m_buy" => $memberInventory_isIn
                );
                //배열을 담는 배열에 추가한다.
                array_push($wheels,$wheel);
            }
            //아이템 테이블에 있고 회원인벤토리에는 없는거라면 해당 아이템은 아직 구매 하지 않았다.
            else
            {
                //배열을 만든다.
                $wheel = array(
                    "m_no" => $item_row['m_no'],
                    "m_equip" => 0,
                    "m_assetname" => $item_row['m_assetname'],
                    "m_name" => $item_row['m_name'],
                    "m_price" => $item_row['m_price'],
                    "m_buy" => $memberInventory_isIn
                );
                //배열을 담는 배열에 추가한다.
                array_push($wheels,$wheel);
            }
        }
        else if(strcmp($item_row['m_type'],'car') == 0 && $memberInventory_row['m_equip'] == 1)
        {
            $JObject["m_car"] = $item_row['m_assetname'];
        }
        else if(strcmp($item_row['m_type'],'wing') == 0 && $memberInventory_row['m_equip'] == 1)
        {
            $JObject["m_wing"] = $item_row['m_assetname'];
        }
    }






    //Json배열에 추가한다.
    $JObject["m_wheel"] = $wheels;

    //Json배열을 Json형태로 변환한다.
    $returnJObject =  json_encode($JObject);

    //클라이언트로 데이터를 Json형태를 보낸다.
    echo $returnJObject;
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

