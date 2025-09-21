<?php

require_once "ProspettoPDFLaureando.php";
require_once "ProspettoPDFCommissione.php";
require_once "Configurazione.php";


class GeneratoreProspetti{
    public function __construct(string $cdl, string $data, array $matricole){

        //CREA LA CARTELLA DAL NOME CORTO
        $conf = Configurazione::ottieniIstanza($cdl);
        $infoCdl = $conf->getInfoCdl();
        $nomeCorto = $infoCdl['cdlShort'];
        $directory_path = "ProspettiLaureando/" . $nomeCorto;

        if (!file_exists($directory_path)) {
            mkdir($directory_path, 0777, true);
        } else { //Se già esiste cancello quello che c'era prima
            $files = glob($directory_path . '/*');

            // Loop attraverso i file e cancellati
            foreach ($files as $file) {
                unlink($file);
            }
        }

        //ELIMINO PROSPETTI COMMISSIONE PRECEDENTI
        $old_file = "ProspettiCommissione/" . $cdl . ".pdf";
        if (file_exists($old_file)) {
            unlink($old_file);
        }

        //crea prospetti
        $this->generaProspetti($cdl, $data, $matricole);
    }

    public function generaProspetti(string $cdl, string $data, array $matricole) :void
    {
        $comm = new ProspettoPDFCommissione();
        $comm->creaPDFCommissione($cdl);

        $conf = Configurazione::ottieniIstanza($cdl);
        $infoCdl = $conf->getInfoCdl();

        foreach ($matricole as $matricola) {
            $laureando = new ProspettoPDFLaureando();
            $retLau = $laureando->creaPDFLaureando($cdl, $data, $matricola);

            $laureando->inserisciAPDFCommissione($retLau['prospetto'], $retLau['nome'], $retLau['cognome'], $retLau['media'], $infoCdl, $comm);
        }

        //prendo elenco e prospetti laureandi, quindi creo prospetto commissione
        $retComm = $comm->getAttributes();
        $retComm['PDF']->WriteHTML($retComm['elenco']);
        $retComm['PDF']->WriteHTML($retComm['prospettiLaureandi']);
        $path = 'ProspettiCommissione/' . $cdl .'.pdf';
        $retComm['PDF']->Output($path, 'F');
    }
}
