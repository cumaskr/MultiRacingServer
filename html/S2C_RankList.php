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
//어떤 맵의 랭킹목록인지 알려주는 맵 번호
$m_mapassetname = $_POST['m_mapassetname'];

//=======================================================질의문 작성

//질의문 만든다.
$rank_query ="SELECT * FROM rank WHERE m_mapassetname = $m_mapassetname order by m_minute,m_second,m_millisecond";
//질의한다.
$rank_query = mysqli_query($mysqli,$rank_query);

//혹시나 로그인한 이메일 정보가 없다면 이런일은 발생하지 않지만 혹시 몰라서 예외처리
if($rank_query->num_rows == 0)
{
    $JObject = array();
    $JObject["m_empty"] = "true";

    //Json배열을 Json형태로 변환한다.
    $returnJObject =  json_encode($JObject);

    //클라이언트로 데이터를 Json형태를 보낸다.
    echo $returnJObject;
}
//로그인한 이메일 정보를 찾았으면 해당 유저의 데이터를 반환해준다.
else
{
    $rankList = array();

    while($rank_row = mysqli_fetch_array($rank_query))
    {
        $member_query ="SELECT * FROM member WHERE m_email = '$rank_row[m_email]'";

        $member_query = mysqli_query($mysqli,$member_query);

        $member_row = mysqli_fetch_array($member_query);

        $member = array(
            "m_nickname" => $member_row['m_nickname'],
            "m_minute" => $rank_row['m_minute'],
            "m_second" => $rank_row['m_second'],
            "m_millisecond" => $rank_row['m_millisecond']
        );
        //배열을 담는 배열에 추가한다.
        array_push($rankList,$member);
    }


    $JObject = array();
    $JObject["m_empty"] = "false";
    $JObject["m_rankList"] = $rankList;

    //Json배열을 Json형태로 변환한다.
    $returnJObject =  json_encode($JObject);

    //클라이언트로 데이터를 Json형태를 보낸다.
    echo $returnJObject;
}

//mysql 연결 종료
mysqli_close($mysqli);

?>

