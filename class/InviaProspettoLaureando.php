<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../lib/PHPMailer/src/Exception.php';
require '../lib/PHPMailer/src/PHPMailer.php';
require '../lib/PHPMailer/src/SMTP.php';
require_once 'Configurazione.php';
require_once 'CarrieraLaureando.php';

class InviaProspettoLaureando{

    public function __construct(){
        $this->messaggio = new PHPmailer();
        $this->messaggio->IsSMTP();
        $this->messaggio->Host = "mixer.unipi.it";
        $this->messaggio->SMTPSecure = "tls";
        $this->messaggio->SMTPAuth = false;
        $this->messaggio->Port = 25;
    }

    public function inviaProspetto(string $matricola, string $cdl): bool
    {
        $info = Configurazione::ottieniIstanza($cdl);
        $nomecorto = $info->getInfoCdl()['cdlShort'];
        $infoCdl = $info->getInfoMail();


        $this->messaggio->From='no-reply-laureandosi@ing.unipi.it';

        //$email = CarrieraLaureando::ottieniEmail($matricola);
        //$this->messaggio->AddAddress($email);
        $this->messaggio->AddAddress("a.bochicchio4@studenti.unipi.it");


        $this->messaggio->Subject= $infoCdl['oggetto'];
        $this->messaggio->Body=stripslashes($infoCdl['testoMail']);
        $attachmentPath = '../ProspettiLaureando/' . $nomecorto . "/" . $matricola . "_output.pdf";
        $this->messaggio->addAttachment($attachmentPath, 'Prospetto PDF.pdf');
        $ret = $this->messaggio->Send();
        $this->messaggio->SmtpClose();
        unset($this->messaggio);
        return $ret;
    }
}

$mail_manager = new InviaProspettoLaureando();
$mail_sent = $mail_manager->inviaProspetto($_POST['matricola'], $_POST['cdl']);
$mail_sent = $mail_sent ? 'true' : 'false';
echo json_encode($mail_sent);