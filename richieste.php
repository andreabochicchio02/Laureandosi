<?php

/*--------------------------------
 * GESTISCE  CREA PROSPETTI
-----------------------------------*/
if($_POST['action'] == 'crea'){
    require_once "class/GeneratoreProspetti.php";

    if(empty($_POST['SceltaOpzione']) || empty($_POST['DataLaurea'])
            || empty($_POST['Matricole'])){
        echo json_encode(false);
        return;
    }

    $matricole = explode(" ", $_POST['Matricole']);

    $crea = new GeneratoreProspetti($_POST['SceltaOpzione'], $_POST['DataLaurea'], $matricole);

    echo json_encode(true);
}


/*-------------------------
 * GESTISCE APRI PROSPETTI
---------------------------*/
if($_POST['action'] == 'apri'){
    header('Content-Type: application/json');

    if(empty($_POST['cdl'])){
        echo json_encode(false);
        return;
    }


    $pathComm = 'wp-content/themes/twentytwentythree/templates/ProspettiCommissione/' . $_POST['cdl'] . ".pdf";
    echo json_encode($pathComm);
}


/*--------------------------
 * GESTISCE INVIO PROSPETTI
----------------------------*/
if($_POST['action'] == 'invia'){
    require_once "class/Configurazione.php";

    header('Content-Type: application/json');

    if(empty($_POST['cdl'])){
        echo json_encode(false);
        return;
    }

    $info = Configurazione::ottieniIstanza($_POST['cdl']);
    $infoCdl = $info->getInfoCdl();

    $pathLau = 'ProspettiLaureando/' . $infoCdl['cdlShort'] . "/";

    $numeroFile = 0;
    $files = null;
    if (is_dir($pathLau)) {
        $files = scandir($pathLau);
        $files = array_slice($files, 2);         //elimino . e ..
        $numeroFile = count($files);
    }

    for($i=0; $i<$numeroFile; $i++) {
        $files[$i] = str_replace("_output.pdf", "", $files[$i]);
    }

    print_r(json_encode($files));
}

