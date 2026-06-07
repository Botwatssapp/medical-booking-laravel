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
        .status  { background: #FFF3CD; color: #7B4F00; padding: 12px 20px; border-radius: 8px; font-weight: bold; display: inline-block; margin-bottom: 24px; }
        .section-title { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em; color: #526069; margin-bottom: 10px; }
        .card    { border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .card-old { background: #fff4f4; border: 1px solid #ffd6d6; }
        .card-new { background: #eff4ff; border: 1px solid #b8d0ff; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid rgba(0,0,0,.06); }
        .info-row:last-child { border-bottom: none; }
        .label   { color: #526069; font-size: 14px; }
        .value   { color: #0d1c2f; font-weight: 600; font-size: 14px; }
        .arrow   { text-align: center; font-size: 28px; color: #003f87; margin: 8px 0; }
        .note    { color: #424752; font-size: 14px; line-height: 1.6; margin: 20px 0; }
        .btn     { display: inline-block; background: #003f87; color: white; padding: 14px 32px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 8px; }
        .footer  { background: #d5e3fd; padding: 24px; text-align: center; color: #424752; font-size: 12px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>+ SantéConnect</h1>
    </div>

    <div class="body">
        <span class="status">📅 Rendez-vous Reporté</span>

        <h2 style="color:#0d1c2f;">Bonjour, {{ $newAppointment->patient->name }}</h2>
        <p class="note">
            Votre rendez-vous avec <strong>Dr. {{ $newAppointment->doctor->user->name }}</strong>
            a dû être annulé. Nous l'avons automatiquement reporté sur le prochain créneau disponible.
        </p>

        {{-- Ancien rendez-vous --}}
        <p class="section-title">❌ Rendez-vous annulé</p>
        <div class="card card-old">
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">{{ $oldAppointment->appointment_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Heure</span>
                <span class="value">
                    @if($oldAppointment->availability)
                        {{ substr($oldAppointment->availability->start_time, 0, 5) }}
                        – {{ substr($oldAppointment->availability->end_time, 0, 5) }}
                    @else
                        {{ $oldAppointment->appointment_date->format('H:i') }}
                    @endif
                </span>
            </div>
        </div>

        <div class="arrow">↓</div>

        {{-- Nouveau rendez-vous --}}
        <p class="section-title">✅ Nouveau rendez-vous</p>
        <div class="card card-new">
            <div class="info-row">
                <span class="label">Médecin</span>
                <span class="value">Dr. {{ $newAppointment->doctor->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">{{ $newAppointment->appointment_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Heure</span>
                <span class="value">
                    @if($newAppointment->availability)
                        {{ substr($newAppointment->availability->start_time, 0, 5) }}
                        – {{ substr($newAppointment->availability->end_time, 0, 5) }}
                    @else
                        {{ $newAppointment->appointment_date->format('H:i') }}
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="label">Statut</span>
                <span class="value" style="color:#00695C;">✓ Confirmé automatiquement</span>
            </div>
            @if($newAppointment->notes)
            <div class="info-row">
                <span class="label">Notes</span>
                <span class="value">{{ $newAppointment->notes }}</span>
            </div>
            @endif
        </div>

        <p class="note">
            Ce nouveau rendez-vous est <strong>automatiquement confirmé</strong>.
            Si cette date ne vous convient pas, vous pouvez l'annuler et en prendre un autre.
        </p>

        <a href="{{ config('app.url') }}/patient/appointments" class="btn">
            Voir mes rendez-vous
        </a>
    </div>

    <div class="footer">
        <p>© {{ date('Y') }} SantéConnect. Tous droits réservés.</p>
        <p style="margin-top:8px;">Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
    </div>

</div>
</body>
</html>
