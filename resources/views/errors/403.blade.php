<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Accesso negato — Marche International Food S.r.l.</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f4f7f4;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      background: #fff;
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      padding: 3rem 2.5rem;
      max-width: 440px;
      width: 100%;
      text-align: center;
      box-shadow: 0 4px 24px rgba(22,43,28,.08);
    }
    .logo-wrap {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.75rem;
      margin-bottom: 2rem;
    }
    .logo-wrap img { width: 44px; height: 44px; object-fit: contain; }
    .logo-name { font-size: 0.85rem; font-weight: 700; color: #1c3d28; line-height: 1.3; text-align: left; }
    .logo-sub { font-size: 0.72rem; color: #5a8c6a; }
    .code {
      font-size: 5rem;
      font-weight: 800;
      color: #2a6941;
      line-height: 1;
      margin-bottom: 0.5rem;
    }
    h1 {
      font-size: 1.3rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.75rem;
    }
    p {
      color: #64748b;
      font-size: 0.9rem;
      line-height: 1.6;
      margin-bottom: 2rem;
    }
    a {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      background: #2a6941;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      padding: 0.6rem 1.4rem;
      font-size: 0.875rem;
      font-weight: 600;
      transition: background 0.15s;
    }
    a:hover { background: #1c3d28; }
    .divider {
      border: none;
      border-top: 1px solid #e2e8f0;
      margin: 2rem 0;
    }
    .footer { font-size: 0.75rem; color: #94a3b8; }
  </style>
</head>
<body>
  <div class="card">
    <div class="logo-wrap">
      <img src="/favicon.png" alt="MIF logo" />
      <div class="logo-name">
        Marche International Food<br>
        <span class="logo-sub">S.r.l.</span>
      </div>
    </div>
    <div class="code">403</div>
    <h1>Accesso non autorizzato</h1>
    <p>
      Non hai i permessi per visualizzare questa pagina.<br>
      Contatta l'amministratore se ritieni sia un errore.
    </p>
    <a href="/">&#8592; Torna alla Dashboard</a>
    <hr class="divider" />
    <p class="footer">Sistema di tracciabilità HACCP — Marche International Food S.r.l.</p>
  </div>
</body>
</html>
