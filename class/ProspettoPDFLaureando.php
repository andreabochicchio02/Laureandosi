<?php

use Mpdf\Mpdf;

require_once "lib/mpdf/autoload.php";
require_once "lib/mpdf/mpdf/mpdf/src/Mpdf.php";

require_once "CarrieraLaureando.php";
require_once "LaureandoInformatica.php";
require_once "Configurazione.php";
require_once "ProspettoPDFCommissione.php";



class ProspettoPDFLaureando{

    public function creaPDFLaureando(string $cdl, string $data, string $matricola): array
    {
        //RACCOLGO TUTTI I DATI
        $laureando = ($cdl != 'T. Ing. Informatica')?
                                                new CarrieraLaureando($matricola, $data, $cdl) :
                                                new LaureandoInformatica($matricola, $data, $cdl);

        $dati = $laureando->getAttributi();


        $prospetto = new Mpdf();

        $html_init = '
                        <!DOCTYPE html>
                            <html lang="en">
                            <head>
                                <meta charset="UTF-8">
                                <link rel="stylesheet" href="stile/styleProspettiLau.css" >
                            </head>
                            <body>';

        //TITOLO
        $titolo = '<p class="titolo">' . $cdl .'</p>';
        $titolo .= '<p class="titolo">CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA</p>';


        //ANAGRAFICA
        $anagrafica = '<table id="intestazione">';
        $anagrafica .= '<tr><td>Matricola:</td><td>' . $dati['matricola']. '</td></tr>';
        $anagrafica .= '<tr><td>Nome:</td><td>' . $dati['nome']. '</td></tr>';
        $anagrafica .= '<tr><td>Cognome:</td><td>' . $dati['cognome']. '</td></tr>';
        $anagrafica .= '<tr><td>Email:</td><td>' . $dati['email']. '</td></tr>';
        $anagrafica .= '<tr><td>Data:</td><td>' . $dati['dataLaurea']. '</td></tr>';
        if($cdl == 'T. Ing. Informatica') {
            $anagrafica .= '<tr><td>Bonus:</td><td>' . $laureando->getBonus() . '</td></tr>';
        }
        $anagrafica .= '</table>';


        //CARRIERA
        $carriera = '<table id="carriera">';
        $carriera .= '<tr>
                        <th>ESAME</th>
                        <th>CFU</th>
                        <th>VOT</th>
                        <th>MED</th>';

        if ($cdl == 'T. Ing. Informatica') {
                        $carriera .= '<th>INF</th>';
        }
        $carriera .= '</tr>';

        foreach ($dati['esami'] as $esame){
            $carriera .= '<tr>
                        <td>'. $esame["Esame"] .'</td>
                        <td>'. $esame["CFU"] .'</td>
                        <td>'. $esame["Voto"] .'</td>
                        <td>'. ($esame["Media"]? 'X' : ' ') . '</td>';

            if ($cdl == 'T. Ing. Informatica') {
                $carriera .= '<td>'. ($esame["INF"]? 'X' : ' ') . '</td>';
            }
            $carriera .= '</tr>';
        }

        $carriera .= '</table>';


        //REPORT
        $info = Configurazione::ottieniIstanza($cdl);
        $infoCdl = $info->getInfoCdl();

        $report = '<table id="report">';

        $report .= '<tr><td>Media Pesata (M):</td><td>' . $laureando->calcolaMediaPesata(). '</td></tr>';
        $report  .= '<tr><td>Crediti che fanno media (CFU):</td><td>' . $laureando->calcolaCreditiMedia() . '</td></tr>';
        $report  .= '<tr><td>Crediti curriculari conseguiti:</td><td>' .
                                $laureando->calcolaCreditiConseguiti() . '/' . $infoCdl['cfuRichiesti']  . '</td></tr>';

        $par_tesi = $infoCdl['parametriTesi'];
        if(strpos($infoCdl['formulaVoto'], 'T') &&  $par_tesi['min'] == 0
                && $par_tesi['max'] == 0 && $par_tesi['step'] == 0){
            $report .= '<tr><td>Voto di Tesi (T):</td><td> 0 </td></tr>';
        }

        $par_comm = $infoCdl['parametriCommissione'];
        if (($pos = strpos($infoCdl['formulaVoto'], 'C')) !== false
            && (isset($infoCdl['formulaVoto'][$pos + 1]) && $infoCdl['formulaVoto'][$pos + 1] !== 'F')
            &&  $par_comm['min'] == 0 && $par_comm['max'] == 0 && $par_comm['step'] == 0){
            $report .= '<tr><td>Voto Commisssione (C):</td><td> 0 </td></tr>';
        }

        $report  .= '<tr><td>Formula calcolo voto di laurea:</td><td>' . $infoCdl['formulaVoto']. '</td></tr>';

        if($cdl == 'T. Ing. Informatica') {
            $report  .= '<tr><td>Media pesata esami INF</td><td>' . $laureando->calcolaMediaPesataINF() . '</td></tr>';
        }

        $report  .= '</table>';

        $html = implode(" ", array($html_init, $titolo, $anagrafica, $carriera, $report));


        $prospetto->WriteHTML($html . '</body></html>');
        $path = 'ProspettiLaureando/' . $infoCdl['cdlShort'] . '/' . $matricola . '_output.pdf';
        $prospetto->Output($path, 'F');

        return [
            'nome' => $dati['nome'],
            'cognome' => $dati['cognome'],
            'media' => $laureando->calcolaMediaPesata(),
            'prospetto' => $html
        ];

    }

    public function inserisciAPDFCommissione(string $prospetto, string $nome, string $cognome, float $media,  array $cdl, ProspettoPDFCommissione $comm): array
    {
        $ret = $comm->aggiugiSimulazione($prospetto, $cdl, $media);
        $comm->aggiungiPdfLaureando($nome, $cognome, $prospetto);
        return $ret;            //Utile per test Simulazione
    }
}