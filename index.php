<?php
/**
* Template Name: Laureandosi
*/
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laureandosi</title>

    <link rel="stylesheet" href="wp-content/themes/twentytwentythree/templates/stile/index.css">
    <script src="wp-content/themes/twentytwentythree/templates/script.js"></script>
</head>
<body>
    <main>
        <h1>Gestione Prospetti di Laurea</h1>

        <form method="POST">
            <div id="left">
                <label for="CorsoDiLaurea">CdL:</label>
                <select name="SceltaOpzione" id="CorsoDiLaurea">
                    <option value="" disabled selected>Seleziona un CdL</option>
                    <option>T. Ing. Biomedica</option>
                    <option>T. Ing. Elettronica</option>
                    <option>T. Ing. Informatica</option>
                    <option>T. Ing. delle Telecomunicazioni</option>
                    <option>M. Ing. Biomedica, Bionics Engineering</option>
                    <option>M. Ing. Elettronica</option>
                    <option>M. Computer Engineering, Artificial Intelligence and Data Enginering</option>
                    <option>M. Cybersecurity</option>
                    <option>M. Ing. Robotica e della Automazione</option>
                    <option>M. Ing. delle Telecomunicazioni</option>
                </select>

                <label for="DataLaurea">Data Laurea:</label>
                <input type = "date" id ="DataLaurea" name ="DataLaurea">
            </div>

            <div id="center">
                <label for="Matricole">Matricole:</label>
                <textarea id ="Matricole" name ="Matricole"></textarea>
            </div>

            <div id="right">
                <button id="CreaProspetti" value="crea" onclick="crea(event)">
                    Crea Prospetti
                </button>

                <a id="apri" onclick="accedi()">apri prospetti</a>

                <button id="InviaProspetti" value="invia" onclick="invia(event)">
                    Invia Prospetti
                </button>

                <p id="stato"></p>
            </div>
        <form>
    </main>
</body>
</html>