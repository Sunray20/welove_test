<?php
    class Email
    {
        function sendMail($to, $subject, $txt)
        {
            $header = "From:jakabdavid20@gmail.com \r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";

            $retval = mail ($to,$subject,$txt,$header);
            if( $retval == true ) {
                return "Message sent successfully...";
            }else {
                return "Message could not be sent...";
            }
        }
    }
?>