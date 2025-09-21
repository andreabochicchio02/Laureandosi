/*
GESTIONE BOTTONE CREA PROSPETTI
 */
async function crea(event) {
    event.preventDefault();

    let stato = document.getElementById("stato");
    let cdl = document.getElementById("CorsoDiLaurea").value;
    let dataLaurea = document.getElementById("DataLaurea").value;
    let matricole = document.getElementById("Matricole").value;

    let dati = new FormData();
    dati.append('action', "crea");
    dati.append('SceltaOpzione', cdl);
    dati.append('DataLaurea', dataLaurea);
    dati.append('Matricole', matricole);

    try {
        let r = await fetch('wp-content/themes/twentytwentythree/templates/richieste.php', {
            method: 'POST',
            body: dati,
        });
        let d = await r.json();

        if (d === true) {
            stato.textContent = "Prospetti Creati"
        } else {
            throw new Error();
        }
    }
    catch (e) {
        stato.textContent = "Errore creazione Prospetti"
    }
}




/*
GESTIONE BOTTONE ACCEDI PROSPETTI
 */
async function accedi() {
    let cdl = document.getElementById("CorsoDiLaurea").value;

    let dati = new FormData();
    dati.append('action', "apri");
    dati.append('cdl', cdl);

    try {
        let r = await fetch('wp-content/themes/twentytwentythree/templates/richieste.php', {
            method: 'POST',
            body: dati,
        });
        let d = await r.json();

        if (d !== false) {
                window.open(d, '_blank');
        } else {
            throw new Error();
        }
    }
    catch (e) {
        let stato = document.getElementById("stato");
        stato.textContent = "Errore apertura Prospetti"
    }
}

/*
GESTIONE BOTTONE INVIA PROSPETTI
 */
async function invia(event) {
    event.preventDefault();

    let cdl = document.getElementById("CorsoDiLaurea").value;
    let stato = document.getElementById("stato");

    let dati = new FormData();
    dati.append('action', "invia");
    dati.append('cdl', cdl);

    let matricole;
    try {
        let r = await fetch('wp-content/themes/twentytwentythree/templates/richieste.php', {
            method: 'POST',
            body: dati,
        });

        matricole = await r.json();

        if (matricole === false) {
            throw new Error();
        }

        if(matricole.length === 0){
            stato.textContent = "Inviata 0/0"
        }
    }
    catch (e) {
        stato.textContent = "Errore invio"
    }


    for(let i=0; i<matricole.length; i++){
        let matricola = new FormData();
        matricola.append('matricola', matricole[i]);
        matricola.append('cdl', cdl);

        try {
            let risposta = await fetch('wp-content/themes/twentytwentythree/templates/class/InviaProspettoLaureando.php', {
                method: 'POST',
                body: matricola,
            });

            const dati = await risposta.json();
            if(dati){
                stato.textContent = "Inviata " + (i+1) + "/" + matricole.length;
            } else {
                throw new Error();
            }

            await new Promise(resolve => setTimeout(resolve, 10000));

        } catch (e) {
            stato.textContent = "Errore " + (i+1) + "/" + matricole.length;
            return;
        }
    }
}