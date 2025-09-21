<?php

class GestioneCarrieraStudente{
    public function restituisciAnagraficaStudente($matricola): string
    {
        $path = __DIR__ . '/../data/' . $matricola . '_anagrafica.json';
        if(!file_exists($path)){
            echo "Matricola non esiste";
            return false;
        }

        return file_get_contents(__DIR__ . '/../data/' . $matricola . '_anagrafica.json');
    }

    public function restituisciCarrieraStudente($matricola): string
    {
        $path = __DIR__ . '/../data/' . $matricola . '_esami.json';
        if(!file_exists($path)){
            echo "Matricola non esiste";
            return false;
        }

        return file_get_contents(__DIR__ . '/../data/' . $matricola . '_esami.json');
    }
}