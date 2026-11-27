<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Registro y listado de contactos de redEMC.">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken, ENT_QUOTES) ?>">
    <title>Contactos · redEMC</title>

    <link rel="icon" href="/assets/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Raleway:wght@400;500;600&display=swap">
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="brand-bar" aria-hidden="true"></div>

    <header class="site-header">
        <div class="container d-flex align-items-baseline gap-2">
            <span class="brand">red<strong>EMC</strong></span>
            <span class="brand-section">Contactos</span>
        </div>
    </header>

    <main class="container py-4 py-lg-5">
        <noscript>
            <div class="alert alert-warning">Para usar esta página necesitás activar JavaScript en tu navegador.</div>
        </noscript>

        <div class="row g-4">
            <section class="col-lg-5" aria-labelledby="form-title">
                <div class="panel">
                    <h1 id="form-title" class="panel-title">Nuevo contacto</h1>
                    <p class="panel-subtitle">Completá los datos del contacto.</p>

                    <div id="form-alert" class="alert alert-danger d-none" role="alert"></div>

                    <form id="contact-form" novalidate>
                        <div class="field">
                            <label for="name" class="form-label">Nombre <span class="required" aria-hidden="true">*</span></label>
                            <input id="name" name="name" type="text" class="form-control"
                                   maxlength="100" autocomplete="name" required
                                   aria-describedby="name-error">
                            <div id="name-error" class="invalid-feedback"></div>
                        </div>

                        <div class="field">
                            <label for="email" class="form-label">Email <span class="required" aria-hidden="true">*</span></label>
                            <input id="email" name="email" type="email" class="form-control"
                                   maxlength="254" autocomplete="email" inputmode="email" required
                                   aria-describedby="email-error">
                            <div id="email-error" class="invalid-feedback"></div>
                        </div>

                        <div class="field">
                            <label for="phone" class="form-label">Teléfono <span class="optional">(opcional)</span></label>
                            <input id="phone" name="phone" type="tel" class="form-control"
                                   maxlength="9" autocomplete="tel-national" inputmode="numeric"
                                   placeholder="097680102" aria-describedby="phone-hint phone-error">
                            <div id="phone-hint" class="form-text">Celular uruguayo: 9 dígitos, empezando con 09.</div>
                            <div id="phone-error" class="invalid-feedback"></div>
                        </div>

                        <button id="submit-button" type="submit" class="btn btn-brand w-100">
                            <span class="spinner-border spinner-border-sm d-none" aria-hidden="true"></span>
                            <span class="button-label">Guardar contacto</span>
                        </button>
                    </form>
                </div>
            </section>

            <section class="col-lg-7" aria-labelledby="list-title">
                <div class="panel">
                    <div class="panel-header">
                        <h2 id="list-title" class="panel-title mb-0">Listado de contactos</h2>
                        <span id="contacts-total" class="contacts-total"></span>
                    </div>

                    <div id="contact-list" class="contact-list" role="list" aria-live="polite"></div>
                    <div id="list-status" class="list-status" role="status"></div>
                    <div id="pagination" class="pagination-bar" role="navigation" aria-label="Paginado de contactos"></div>
                </div>
            </section>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="success-toast" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Contacto guardado.</div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script type="module" src="/assets/js/app.js"></script>
</body>
</html>
