<?php

use PHPMailer\PHPMailer\PHPMailer;

use PHPMailer\PHPMailer\Exception;

require "PHPMailer-master/PHPMailer-master/src/PHPMailer.php";

require "PHPMailer-master/PHPMailer-master/src/SMTP.php";

require "PHPMailer-master/PHPMailer-master/src/Exception.php";


function SendMail($receiveMailAddress,$subject,$content)
{
    $mail = new PHPMailer(true);

    try {

// 서버세팅

//디버깅 설정을 0 으로 하면 아무런 메시지가 출력되지 않습니다

        //$mail -> SMTPDebug = 2; // 디버깅 설정

        $mail -> isSMTP(); // SMTP 사용 설정



// 지메일일 경우 smtp.gmail.com, 네이버일 경우 smtp.naver.com



        $mail -> Host = "smtp.naver.com";               // 네이버의 smtp 서버
        // SMTP 인증을 사용함
        $mail -> SMTPAuth = true;
        // 메일 계정 (지메일일경우 지메일 계정)
        $mail -> Username = "서버운영종료";
        // 메일 비밀번호
        $mail -> Password = "서버운영종료";
        // SSL을 사용함
        $mail -> SMTPSecure = "ssl";
        // email 보낼때 사용할 포트를 지정
        $mail -> Port = 465;
        // 문자셋 인코딩
        $mail -> CharSet = "utf-8";



// 보내는 메일
        $mail -> setFrom("서버운영종료", "(주)레이싱게임");
        
// 받는 메일
        $mail -> addAddress($receiveMailAddress, "receive01");
        
// 메일 내용
        $mail -> isHTML(true); // HTML 태그 사용 여부

        $mail -> Subject = $subject;  // 메일 제목

        $mail -> Body = $content;     // 메일 내용

        
// Gmail로 메일을 발송하기 위해서는 CA인증이 필요하다.
// CA 인증을 받지 못한 경우에는 아래 설정하여 인증체크를 해지하여야 한다.

        $mail -> SMTPOptions = array(

            "ssl" => array(

                "verify_peer" => false

            , "verify_peer_name" => false

            , "allow_self_signed" => true

            )

        );

// 메일 전송
        $mail -> send();
        
// 메일 전송 예외처리
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error : ", $mail -> ErrorInfo;
    }
}

?>
