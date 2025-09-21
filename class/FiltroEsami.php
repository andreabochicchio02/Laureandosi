<?php
class FiltroEsami{
    private string $cdl;
    private array $esamiNonAvg;
    private array $esamiNonCurr;

    public function __construct(string $cdl){
        $filtro = file_get_contents(__DIR__ . '/../configurazione/filtro_esami.json');
        $dati = json_decode($filtro, true);

        //Accedo
        $dati = $dati[$cdl]['*'];

        $this->cdl = $cdl;
        $this->esamiNonAvg = $dati["esami-non-avg"];
        $this->esamiNonCurr = $dati["esami-non-curr"];
    }

    public function getAttributes(): array
    {
        return [
            'nonAVG' => $this->esamiNonAvg,
            'nonCurr' => $this->esamiNonCurr
        ];
    }
}