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

//=======================================================질의문 작성

//질의문 만든다.
$map_query ="SELECT * FROM map";
//질의한다.
$map_query = mysqli_query($mysqli,$map_query);

//혹시나 로그인한 이메일 정보가 없다면 이런일은 발생하지 않지만 혹시 몰라서 예외처리
if($map_query->num_rows == 0)
{
    echo "false";
}
//로그인한 이메일 정보를 찾았으면 해당 유저의 데이터를 반환해준다.
else
{
    $mapList = array();

    while($map_row = mysqli_fetch_array($map_query))
    {
        $map = array(
            "m_assetname" => $map_row['m_assetname'],
            "m_name" => $map_row['m_name'],
        );

        //배열을 담는 배열에 추가한다.
        array_push($mapList,$map);
    }

    $JObject = array();
    $JObject["m_mapList"] = $mapList;

    //Json배열을 Json형태로 변환한다.
    $returnJObject =  json_encode($JObject);

    //클라이언트로 데이터를 Json형태를 보낸다.
    echo $returnJObject;
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

