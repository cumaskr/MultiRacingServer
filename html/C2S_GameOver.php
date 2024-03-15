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

$m_limit = 5;

$m_email = $_POST['email'];

$m_minute = $_POST['m_minute'];

$m_second = $_POST['m_second'];

$m_millisecond = $_POST['m_millisecond'];

$m_gamemoney = $_POST['m_money'];

$m_mapassetname = $_POST['m_mapassetname'];

//=============================================================================================================유저 돈 추가

$money_query ="SELECT * FROM member WHERE m_email = '$m_email'";
$money_query = mysqli_query($mysqli,$money_query);

$row = mysqli_fetch_array($money_query);
$m_prevMoney = $row['m_money'];

//유저가 원래 가지고 있던돈에 게임한판 하고 난후 받게 되는돈 추가한다.
$m_money = (int)$m_prevMoney + (int)$m_gamemoney;

$money_query ="UPDATE member SET m_money= '$m_money' WHERE m_email = '$m_email' ";
$money_query = mysqli_query($mysqli,$money_query);

//=============================================================================================================랭킹시스템

$rank_query ="SELECT * FROM rank WHERE m_mapassetname = $m_mapassetname";
$rank_query = mysqli_query($mysqli,$rank_query);

//만약 랭킹이 제한인원까지 차지 않았다면 그냥 넣는다.
if($rank_query->num_rows < $m_limit)
{
    $insert_query ="INSERT INTO rank(m_email,m_minute,m_second,m_millisecond,m_mapassetname) VALUES('$m_email','$m_minute','$m_second','$m_millisecond','$m_mapassetname')";
    $insert_query = mysqli_query($mysqli,$insert_query);
}
//만약 랭킹이 제한인원까지 다 찼다면
else
    {
        $insert_query ="INSERT INTO rank(m_email,m_minute,m_second,m_millisecond,m_mapassetname) VALUES('$m_email','$m_minute','$m_second','$m_millisecond','$m_mapassetname')";
        $insert_query = mysqli_query($mysqli,$insert_query);

        $rank_query ="SELECT * FROM rank WHERE m_mapassetname = $m_mapassetname order by m_minute,m_second,m_millisecond";
        $rank_query = mysqli_query($mysqli,$rank_query);

        $m_index = 0;

        while($rank_row = mysqli_fetch_array($rank_query))
        {
            if($m_index >= $m_limit)
            {
                $delete_query ="DELETE FROM rank WHERE m_no = '$rank_row[m_no]'";
                $delete_query = mysqli_query($mysqli,$delete_query);
                break;
            }
            $m_index++;
        }
    }
//mysql 연결 종료
mysqli_close($mysqli);

?>

