<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9ff; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .header  { background: #003f87; padding: 32px; text-align: center; }
        .header h1 { color: white; margin: 0; font-size: 24px; }
        .body    { padding: 40px; }
        .status-cancelled { background: #FFDAD6; color: #93000A; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: inline-block; margin-bottom: 24px; }
        .status-missed    { background: #FFF3CD; color: #7B4F00; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: inline-block; margin-bottom: 24px; }
        .info-box { border-radius: 12px; padding: 24px; margin: 24px 0; }
        .box-cancelled { background: #fff4f4; }
        .box-missed    { background: #fffbf0; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,.06); }
        .info-row:last-child { border-bottom: none; }
        .label { color: #526069; font-size: 14px; }
        .value { color: #0d1c2f; font-weight: 600; font-size: 14px; }
        .note  { color: #424752; font-size: 14px; line-height: 1.6; }
        .btn   { display: inline-block; background: #003f87; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 24px; }
        .footer { background: #d5e3fd; padding: 24px; text-align: center; color: #424752; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>+ SantéConnect</h1>
    </div>

    <div class="body">

        @if($reason === 'missed')
            <span class="status-missed">⚠️ Rendez-vous Manqué</span>
            <h2 style="color:#0d1c2f;">Bonjour, {{ $appointment->patient->name }}</h2>
            <p class="note">
                Votre rendez-vous avec <strong>Dr. {{ $appointment->doctor->user->name }}</strong>
                est passé sans avoir été honoré. Il a été marqué comme <strong>manqué</strong>.
            </p>
        @else
            <span class="status-cancelled">❌ Rendez-vous Expiré</span>
            <h2 style="color:#0d1c2f;">Bonjour, {{ $appointment->patient->name }}</h2>
            <p class="note">
                Votre demande de rendez-vous avec <strong>Dr. {{ $appointment->doctor->user->name }}</strong>
                n'a pas été confirmée dans les délais. Elle a été <strong>automatiquement annulée</strong>.
            </p>
        @endif

        {{-- Détails du rendez-vous --}}
        <div class="info-box {{ $reason === 'missed' ? 'box-missed' : 'box-cancelled' }}">
            <div class="info-row">
                <span class="label">Médecin</span>
                <span class="value">Dr. {{ $appointment->doctor->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">{{ $appointment->appointment_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Heure</span>
                <span class="value">
                    @if($appointment->availability)
                        {{ substr($appointment->availability->start_time, 0, 5) }}
                        – {{ substr($appointment->availability->end_time, 0, 5) }}
                    @else
                        {{ $appointment->appointment_date->format('H:i') }}
                    @endif
                </span>
            </div>
            @if($appointment->notes)
            <div class="info-row">
                <span class="label">Notes</span>
                <span class="value">{{ $appointment->notes }}</span>
            </div>
            @endif
        </div>

        @if($reason === 'cancelled')
            <p class="note">Vous pouvez prendre un nouveau rendez-vous à tout moment sur SantéConnect.</p>
            <a href="{{ config('app.url') }}/patient/doctors" class="btn">Prendre un nouveau rendez-vous</a>
        @else
            <p class="note">Si vous avez besoin d'une nouvelle consultation, n'hésitez pas à prendre rendez-vous.</p>
            <a href="{{ config('app.url') }}/patient/doctors" class="btn">Prendre un nouveau rendez-vous</a>
        @endif

    </div>

    <div class="footer">
        <p>© {{ date('Y') }} SantéConnect. Tous droits réservés.</p>
        <p style="margin-top:8px;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>

</div>
</body>
</html>
