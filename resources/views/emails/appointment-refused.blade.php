<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9ff; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: #003f87; padding: 32px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 24px; }
        .body { padding: 40px; }
        .status { background: #FFF3E0; color: #E65100; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: inline-block; margin-bottom: 24px; }
        .info-box { background: #fff8f0; border-radius: 12px; padding: 24px; margin: 24px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ffe0b2; }
        .info-row:last-child { border-bottom: none; }
        .label { color: #526069; font-size: 14px; }
        .value { color: #0d1c2f; font-weight: 600; font-size: 14px; }
        .footer { background: #d5e3fd; padding: 24px; text-align: center; color: #424752; font-size: 12px; }
        .btn { display: inline-block; background: #003f87; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>+ SantéConnect</h1>
        </div>
        <div class="body">
            <span class="status">⚠️ Rendez-vous Refusé</span>
            <h2 style="color: #0d1c2f;">Bonjour, {{ $appointment->patient->name }}</h2>
            <p style="color: #424752;">Malheureusement, votre rendez-vous a été <strong>refusé</strong> par le médecin.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="label">Médecin</span>
                    <span class="value">Dr. {{ $appointment->doctor->user->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Date</span>
                    <span class="value">{{ date('d/m/Y', strtotime($appointment->date)) }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Heure</span>
                    <span class="value">{{ $appointment->time_slot }}</span>
                </div>
            </div>

            <p style="color: #424752; font-size: 14px;">Nous vous invitons à prendre rendez-vous avec un autre praticien disponible.</p>
            <a href="{{ config('app.url') }}/patient/doctors" class="btn">Trouver un autre médecin</a>
        </div>
        <div class="footer">
            <p>© 2024 SantéConnect. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
