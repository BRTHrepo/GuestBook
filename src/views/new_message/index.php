<?php require_once BASE_PATH . '/src/views/layout/header.php';
require_once BASE_PATH . '/src/helpers/LanguageHelper.php';

use helpers\LanguageHelper;

?>


<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Új üzenet hozzáadása</h2>
            <form id="guestbook-form" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Név</label>
                    <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                    <div class="invalid-feedback">Kötelező mező (min. 4 karakter).</div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail cím</label>
                    <input type="email" class="form-control" id="email" name="email" required maxlength="255">
                    <div class="invalid-feedback">Érvényes e-mail cím szükséges.</div>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Üzenet</label>
                    <textarea class="form-control" id="message" name="message" rows="4" required
                              maxlength="1000"></textarea>
                    <div class="invalid-feedback">Kötelező mező.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Értékelés (csillag):</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rate" id="rate-1" value="1" required>
                            <label class="form-check-label" for="rate-1">1</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rate" id="rate-2" value="2">
                            <label class="form-check-label" for="rate-2">2</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rate" id="rate-3" value="3">
                            <label class="form-check-label" for="rate-3">3</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rate" id="rate-4" value="4">
                            <label class="form-check-label" for="rate-4">4</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rate" id="rate-5" value="5" checked>
                            <label class="form-check-label" for="rate-5">5</label>
                        </div>
                    </div>

                </div>
                <button type="submit" class="btn btn-success">Üzenet beküldése</button>
            </form>
            <div id="form-message" class="mt-3"></div>
        </div>
    </div>
</div>

<script type="module">
    // Egyszerű e-mail validátor
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Név validátor (min. 4 karakter)
    function isValidName(name) {
        return name.trim().length >= 4;
    }

    document.getElementById('guestbook-form').addEventListener('submit', function (e) {
        e.preventDefault();

        let form = e.target;
        let name = form.name.value.trim();
        let email = form.email.value.trim();
        let valid = true;

        // Név ellenőrzése
        if (!isValidName(name)) {
            form.name.classList.add('is-invalid');
            valid = false;
        } else {
            form.name.classList.remove('is-invalid');
        }

        // E-mail ellenőrzése
        if (!isValidEmail(email)) {
            form.email.classList.add('is-invalid');
            valid = false;
        } else {
            form.email.classList.remove('is-invalid');
        }

        // Bootstrap natív validáció
        if (!form.message.value.trim()) {
            form.message.classList.add('is-invalid');
            valid = false;
        } else {
            form.message.classList.remove('is-invalid');
        }

        // Értékelés validálása
        const rate = form.querySelector('input[name="rate"]:checked');
        console.log(rate.value);
        if (rate.value === '') {

            valid = false;
        } else {

        }

        if (!valid) {
            document.getElementById('form-message').innerHTML =
                '<div class="alert alert-danger">Kérjük, töltsd ki helyesen az összes mezőt!</div>';
            return;
        }

        // Adatok összegyűjtése
        const data = {
            name: name,
            email: email,
            message: form.message.value.trim(),
            rate: rate.value
        };
        const params = {
            api: "new_message",
            message: data,
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
                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                return response.json();
            })
            .then(json => {
                if (json.success) {
                    form.reset();
                    form.classList.remove('was-validated');
                    document.getElementById('form-message').innerHTML =
                        '<div class="alert alert-success">Üzenet sikeresen elküldve, e-mail-es validálás és azt követő moderálás után jelenik meg!</div>';
                } else {
                    document.getElementById('form-message').innerHTML =
                        `<div class="alert alert-danger">Hiba: ${json.message || "Ismeretlen hiba."}</div>`;
                }
            })
            .catch(err => {
                document.getElementById('form-message').innerHTML =
                    `<div class="alert alert-danger">Hiba történt: ${err.message}</div>`;
            });
        document.getElementById('guestbook-form').active = false;
    });


</script>
<?php require_once BASE_PATH . '/src/views/layout/footer.php'; ?>


