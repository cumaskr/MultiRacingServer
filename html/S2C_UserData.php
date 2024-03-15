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
    $row = mysqli_fetch_array($result_query);

    $JObject = array();
    $JObject["m_nickname"] = $row['m_nickname'];

    //질의문 만든다.
    $select_query ="SELECT * FROM memberinventory WHERE m_email = '$login_id' AND m_equip = 1";

    //질의한다.
    $result_query = mysqli_query($mysqli,$select_query);

    while($row = mysqli_fetch_array($result_query))
    {
        //해당 유저가 가지고있는 아이템에 대한 정보를 얻기 위해 items 테이블에 질의한다.
        $item_query ="SELECT * FROM items WHERE m_no ='$row[m_item]'";

        //질의한다.
        $item_result_query = mysqli_query($mysqli,$item_query);

        $item_row = mysqli_fetch_array($item_result_query);

        //해당 유저가 장착하고 있는 아이템이 차 모델 이라면
        if(strcmp($item_row['m_type'],'car') == 0)
        {

            $JObject["m_car"] = $item_row['m_assetname'];
        }
        //해당 유저가 장착하고 있는 아이템이 바퀴 모델 이라면
        else if(strcmp($item_row['m_type'],'wheel') == 0)
        {
            $JObject["m_wheel"] = $item_row['m_assetname'];
        }
        //해당 유저가 장착하고 있는 아이템이 날개 모델 이라면
        else if(strcmp($item_row['m_type'],'wing') == 0)
        {
            $JObject["m_wing"] = $item_row['m_assetname'];
        }
    }

    //JOBJECT형식으로 다 인벤토릴 장착하고있는 아이템을 다 변환했다면
    $returnJObject =  json_encode($JObject);

    //클라이언트로 데이터를 Json형태로 보낸다.
    echo $returnJObject;
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

