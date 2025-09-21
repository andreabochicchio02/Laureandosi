<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../class/CarrieraLaureando.php';
require_once __DIR__ . '/../class/LaureandoInformatica.php';
require_once __DIR__ . '/../class/ProspettoPDFLaureando.php';
require_once __DIR__ . '/../class/ProspettoPDFCommissione.php';

$dati = file_get_contents(__DIR__ . '/datiTest.json');
$test = json_decode($dati, true);

class OracoloTest extends TestCase
{
    /**
     * @testdox Verifica Carriera Laureando
     */
    public function testLaureando(){
        global $test;

        foreach ($test as $input) {
            if($input['cdl'] === "T. Ing. Informatica"){
                $laureando = new LaureandoInformatica($input['matricola'], $input['data_appello'], $input['cdl']);
            } else {
                $laureando = new CarrieraLaureando($input['matricola'], $input['data_appello'], $input['cdl']);
            }


            echo "MATRICOLA: ". $input['matricola'] ."\n";

            $infoLau = $laureando->getAttributi();
            $this->assertSame($input['nome'], $infoLau['nome'],
                "Matricola: ". $input['matricola'] . " NOME ERRATO!");
            echo "\tNome OK!\n";

            $this->assertSame($input['cognome'], $infoLau['cognome'],
                "Matricola: ". $input['matricola'] . " COGNOME ERRATO!");
            echo "\tCognome OK!\n";

            $emailLau = $laureando->getEmail();
            $this->assertSame($input['email_ate'], $emailLau,
                "Matricola: ". $input['matricola'] . " EMAIL ERRATA!");
            echo "\tEmail OK!\n";

            $credCons = $laureando->calcolaCreditiConseguiti();
            $this->assertEquals($input['crediti_curriculari'], $credCons,
                "Matricola: ". $input['matricola'] . " CREDITI CURRICULARI ERRATI!");
            echo "\tCrediti curriculari OK!\n";

            $credPes = $laureando->calcolaCreditiMedia();
            $this->assertEquals($input['crediti_media'], $credPes,
                "Matricola: ". $input['matricola'] . " CREDITI MEDIA ERRATI!");
            echo "\tCrediti media OK!\n";

            $medPes = $laureando->calcolaMediaPesata();
            $this->assertEquals($input['media_pesata'], $medPes,
                "Matricola: ". $input['matricola'] . " MEDIA PESATA ERRATA!");
            echo "\tMedia pesata OK!\n";

            if($input['cdl'] === "T. Ing. Informatica"){
                $this->assertSame($input['bonus'], $laureando->getBonus(),
                    "Matricola: ". $input['matricola'] . " BONUS ERRATO!");
                echo "\tBonus OK!\n";

                $this->assertSame($input['media_pesata_inf'], $laureando->calcolaMediaPesataINF(),
                    "Matricola: ". $input['matricola'] . " MEDIA INF ERRATA!");
                echo "\tMedia inf OK!\n";
            }
            echo "\n";
        }
            echo "\n \n";
    }

    /**
     * @testdox Verifica Generatore Prospetti e Simulazione
     */
    public function testProspetti(){
        global $test;
        foreach ($test as $input) {
            $conf = Configurazione::ottieniIstanza($input['cdl']);
            $infoCdl = $conf->getInfoCdl();

            echo "MATRICOLA: ". $input['matricola'] ."\n";

            $comm = new ProspettoPDFCommissione();
            $comm->creaPDFCommissione($input['cdl']);


            $laureando = new ProspettoPDFLaureando();
            $retLau = $laureando->creaPDFLaureando($input['cdl'], $input['data_appello'], $input['matricola']);

            $sim = $laureando->inserisciAPDFCommissione(
                $retLau['prospetto'],
                $retLau['nome'],
                $retLau['cognome'],
                $retLau['media'],
                $infoCdl,
                $comm
            );

            for($i=0; $i<count($sim); $i++){
                $this->assertEquals(1, 1);
                $this->assertEquals($input['simulazione'][$i], $sim[$i],
                    "Matricola: ". $input['matricola'] . " SIMULAZIONE ERRATA ERRATA!");
            }
            echo "\tSimulazione OK!\n";

            $retComm = $comm->getAttributes();
            $retComm['PDF']->WriteHTML($retComm['elenco']);
            $retComm['PDF']->WriteHTML($retComm['prospettiLaureandi']);
            $path = 'ProspettiCommissione/' . $input['cdl'] . '.pdf';
            $retComm['PDF']->Output($path, 'F');
        }
    }
}
