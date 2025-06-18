<?php require_once BASE_PATH . '/src/views/layout/header.php';
require_once BASE_PATH . '/src/helpers/LanguageHelper.php';

use helpers\LanguageHelper;

?>


<div class="container mb-4">
    <!-- Szűrők -->
    <div class="row g-3">
        <div class="col-12 col-md-6 mb-2">
            <input type="text" id="search-input" class="form-control" placeholder="Keresés üzenetek között...">
        </div>
        <div class="col-12 col-md-6 mb-2">
            <div class="d-flex gap-2 flex-wrap justify-content-start">
                <!-- 1-5 csillagos szűrők -->
                <div class="mb-2">

                    <div class="d-flex gap-2 flex-wrap">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="star-1" value="1" checked>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="star-2" value="2" checked>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="star-3" value="3" checked>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="star-4" value="4" checked>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="star-5" value="5" checked>
                            <label class="form-check-label" for="star-5">★</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <button id="filter-btn" class="btn btn-primary">Szűrés indítása</button>
        </div>

    </div>
</div>


<!-- Üzenet lista -->
<div id="guestbook-list">
    <!-- Üzenet kártyák jönnek ide -->
</div>
<script type="module">
    console.log("Guestbook script loaded");
    // Csak a gombra tesszük az eseménykezelőt
    document.getElementById('filter-btn').addEventListener('click', fetchGuestbookMessages);


    // Segédfüggvény: összegyűjti a bepipált csillagokat (rate értékeket)
    function getSelectedStars() {
        const checkboxes = document.querySelectorAll('.form-check-input[type="checkbox"]');
        const selected = [];
        checkboxes.forEach(cb => {
            if (cb.checked) selected.push(cb.value);
        });
        return selected;
    }

    // Lekérdezi a keresőmező tartalmát
    function getSearchTerm() {
        return document.getElementById('search-input').value.trim();
    }

    // AJAX lekérdezés a szerver felé
    // AJAX lekérdezés a szerver felé
    function fetchGuestbookMessages() {
        const stars = getSelectedStars();
        const search = getSearchTerm();

        // Paraméterek elküldése JSON formátumban
        const params = {
            api: "guestbook",
            rate: stars,
            search: search,
            from: 0,
        };
        const url = ("api/");
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' // Kötelező a JSON-hoz
            },
            body: JSON.stringify(params) // JSON formátumba konvertálás
        })
            .then(response => {
                if (!response.ok) {
                    console.error("Hiba történt:", response.status, response.statusText);
                    document.getElementById('guestbook-list').innerHTML =
                        `<div class="alert alert-danger">Hiba történt az adatok lekérésekor: ${response.status} ${response.statusText}</div>`;
                }
                return response.json();
            })
            .then(data => renderMessages(data.data))
            .catch(err => {
                console.error("Hiba történt:", err);
                document.getElementById('guestbook-list').innerHTML = `
            <div class="alert alert-danger">
                Hiba történt: ${err.message}
            </div>
        `;
            });
    }

    // Üzenetek megjelenítése
    function renderMessages(messages) {
        if (messages === null) {
            console.error("Nincs adat a rendereléshez.");
            return;
        }
        const container = document.getElementById('guestbook-list');
        container.innerHTML = '';
        if (!messages.length === null) {
            document.getElementById('guestbook-list').innerHTML =
                `<div class="alert alert-warning">Nincs adat vagy hiba történt a válasz feldolgozásakor.</div>`;
            return;
        }
        messages.forEach(msg => {
            container.innerHTML += `
            <div class="card mb-3 text-start">
                <div class="card-body">
                    <h5 class="card-title">${msg.name} <small class="text-muted">${msg.created_at}</small></h5>
                     <div>${'★'.repeat(msg.rate)}${'☆'.repeat(5 - msg.rate)}</div>
                    <p class="card-text">${msg.message}</p>

                </div>
            </div>
        `;
        });
    }

    // Első betöltéskor is lekérjük az adatokat
    fetchGuestbookMessages();


</script>
<?php require_once BASE_PATH . '/src/views/layout/footer.php'; ?>


