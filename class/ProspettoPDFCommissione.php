<?php

use Mpdf\Mpdf;

class ProspettoPDFCommissione{
    private Mpdf $PDFCommissione;
    private string $elenco;
    private string $prospettiLaurenadi;


    public function creaPDFCommissione(string $cdl): void
    {
        $this->PDFCommissione = new Mpdf();

        $html_init = '    
                        <!DOCTYPE html>
                            <html lang="it">
                            <head>
                                <link rel="stylesheet" href="stile/styleProspettiComm.css" >
                            </head>
                            <body>';

        //TITOLO
        $titolo = "<p class='top'>". $cdl . "</p>";
        $titolo .= "<p class='top'>LAUREANDOSI 2 - Progettazione: mario.cimino@unipi.it, Amministrazione: rose.rossiello@unipi.it</p>";
        $titolo .= "<p class='top'>LISTA LAUREANDI</p>";

        $html = implode(" ", array($html_init, $titolo));
        $this->PDFCommissione->WriteHTML($html);

        $this->elenco = "<table id='elenco'><tr><th>NOME</th><th>COGNOME</th><th>CDL</th><th>VOTO DI LAUREA</th></tr></table>";
        $this->prospettiLaurenadi = "";
    }

    public function aggiugiSimulazione(string &$prospetto, array $infoCdl, float $media): array
    {

        $newelem = "<body><pagebreak />";
        $prospetto = str_replace("<body>", $newelem, $prospetto);
        $prospetto .= '<table id="simulazione">';


        $ritorno_destra = array();
        $ritorno_sinistra = array();

        if($infoCdl['parametriTesi']['max']){
            $par_sim =  $infoCdl['parametriTesi'];
        } else {
            $par_sim =  $infoCdl['parametriCommissione'];
        }

        $elem = ($par_sim['max']-$par_sim['min'])/$par_sim['step'];
        $col4 = ($elem>7);

        if($col4){
            $prospetto .= '<tr><th colspan="4">';
        } else {
            $prospetto .= '<tr><th colspan="2">';
        }
        $prospetto .= 'SIMULAZIONE DI VOTO DI LAUREA</th></tr>';

        //INTESTAZIONE COLONNE
        $prospetto .= '<tr>';
        $iter = ($col4)? 2 : 1;
        for($it =0; $it<$iter; $it++) {
            if ($infoCdl['parametriTesi']['max']) {
                $prospetto .= '<td>VOTO TESI (T)</td>';
            } else {
                $prospetto .= '<td>VOTO COMMISSIONE (C)</td>';
            }
            $prospetto .= '<td>VOTO LAUREA</td>';
        }
        $prospetto .= '</tr>';

        // Sostituisco i valori di M, CFU
        $formula = $infoCdl['formulaVoto'];
        if (str_contains($formula, 'CFU') !== false) {
            $CFU_value = $infoCdl['cfuRichiesti'];
            $formula = str_replace('CFU', $CFU_value, $formula);
        }
        if (str_contains($formula, 'M') !== false) {
            $formula = str_replace('M', (string)$media, $formula);
        }

        //SIMULAZIONE usando paraemetro $media solo quando ci sono due colonne
        if(!$col4) {
            for ($i = $par_sim['min']; $i <= $par_sim['max']; $i+=$par_sim['step']) {
                $prospetto .= '<tr>';

                $formula_in = $formula;
                // Sostituisco i valori di T o CFU
                if (str_contains($formula_in, 'T') !== false) {
                    if ($infoCdl['parametriTesi']['max']) {
                        $formula_in = str_replace('T', $i, $formula_in);
                    } else {
                        $formula_in = str_replace('T', 0, $formula_in);
                    }
                }

                if (str_contains($formula_in, 'C') !== false) {
                    if ($infoCdl['parametriCommissione']['max']) {
                        $formula_in = str_replace('C', $i, $formula_in);
                    } else {
                        $formula_in = str_replace('C', 0, $formula_in);
                    }
                }

                $ritorno_destra [] = round(eval("return $formula_in;"), 3); //RITORNO PER TEST

                $prospetto .= '<td>' . $i . '</td>';
                $prospetto .= '<td>' . eval("return $formula_in;") . '</td>';
                $prospetto .= '</tr>';

            }
        } else {
            //SIMULAZIONE usando paraemetro $media solo quando ci sono due colonne
            $i_destra = $par_sim['min'];
            for (; $i_destra <= $par_sim['min']+$elem/2; $i_destra+=$par_sim['step']) {
                $prospetto .= '<tr>';
                $i_sinistra = $i_destra + ($elem/2) + 1;

                $formula_destra = $formula;
                $formula_sinistra = $formula;

                if (str_contains($formula, 'T') !== false) {
                    if ($infoCdl['parametriTesi']['max']) {
                        $formula_destra = str_replace('T', $i_destra, $formula_destra);
                        $formula_sinistra = str_replace('T', $i_sinistra, $formula_sinistra);
                    } else {
                        $formula_destra = str_replace('T', 0, $formula_destra);
                        $formula_sinistra = str_replace('T', 0, $formula_sinistra);
                    }
                }

                if (str_contains($formula, 'C') !== false) {
                    if ($infoCdl['parametriCommissione']['max']) {
                        $formula_destra = str_replace('C', $i_destra, $formula_destra);
                        $formula_sinistra = str_replace('C', $i_sinistra, $formula_sinistra);
                    } else {
                        $formula_destra = str_replace('C', 0, $formula_destra);
                        $formula_sinistra = str_replace('C', 0, $formula_sinistra);
                    }
                }


                $cifre = ($infoCdl['cdlShort'] === "m-ele")? 1 : 3;

                $ritorno_destra [] = round(eval("return $formula_destra;"), $cifre);    //RITORNO PER TEST

                $prospetto .= '<td>' . $i_destra . '</td>';
                $prospetto .= '<td>' . round(eval("return $formula_destra;"), $cifre) . '</td>';
                if($i_sinistra <= $par_sim['max']){
                    $prospetto .= '<td>' . $i_sinistra . '</td>';
                    $prospetto .= '<td>' . round(eval("return $formula_sinistra;"), $cifre) . '</td>';

                    $ritorno_sinistra [] = round(eval("return $formula_sinistra;"), $cifre);  //RITORNO PER TEST
                }
                $prospetto .= '</tr>';
            }
        }
        $prospetto .= '</table>';

        $prospetto .= '<p>'. $infoCdl['nota'] . '</p>';

        return array_merge($ritorno_destra, $ritorno_sinistra); //RITORNO PER TEST
    }

    public function aggiungiPdfLaureando(string $nome, string $cognome, string $prospetto): void
    {
        $newelem = '<tr><td>' . $nome . '</td><td>' . $cognome . '</td><td></td><td>/110</td></tr></table>';
        $this->elenco = str_replace("</table>", $newelem, $this->elenco);

        $this->prospettiLaurenadi .= $prospetto;
    }

    public function getAttributes(): array
    {
        return [
            'PDF' => $this->PDFCommissione,
            'elenco' => $this->elenco,
            'prospettiLaureandi' => $this->prospettiLaurenadi
        ];
    }
}