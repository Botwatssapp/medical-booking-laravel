import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('scSelect', () => ({
        open: false,
        options: [],
        label: '',
        init() {
            this.read();
            this.$refs.native?.addEventListener('change', () => this.read());
        },
        read() {
            const select = this.$refs.native;
            if (! select) {
                return;
            }
            this.options = Array.from(select.options).map((option) => ({
                value: option.value,
                text: option.text.trim(),
                selected: option.selected,
                disabled: option.disabled,
            }));
            this.label = this.options.find((option) => option.selected)?.text
                || this.options[0]?.text
                || '';
        },
        toggle() {
            this.open = ! this.open;
        },
        choose(option) {
            if (option.disabled) {
                return;
            }
            const select = this.$refs.native;
            select.value = option.value;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            this.read();
            this.open = false;
        },
    }));

    Alpine.data('scDemo', () => ({
        toast: '',
        wallet: null,
        twoFactor: ['', '', '', '', '', ''],
        showToast(message) {
            this.toast = message;
            window.clearTimeout(this._toastTimer);
            this._toastTimer = window.setTimeout(() => {
                this.toast = '';
            }, 3200);
        },
        simulateWallet(kind) {
            this.wallet = kind;
            this.showToast(
                kind === 'apple'
                    ? 'Aperçu Apple Wallet (démo) — aucun pass réel n’a été généré.'
                    : 'Aperçu Google Wallet (démo) — aucun pass réel n’a été généré.'
            );
        },
        simulateOauth(provider) {
            this.showToast(`Connexion ${provider} non configurée — aperçu visuel uniquement.`);
        },
        simulateSmsReset() {
            this.showToast('Envoi SMS non configuré — utilisez le lien e-mail.');
        },
        copyText(value, message) {
            const text = String(value ?? '');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showToast(message || 'Référence copiée.');
                }).catch(() => {
                    this.showToast('Impossible de copier automatiquement.');
                });
                return;
            }
            this.showToast('Copie automatique indisponible sur ce navigateur.');
        },
        printPass() {
            window.print();
        },
        downloadIcs(payload) {
            const lines = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//SanteConnect//Appointment Pass//FR',
                'BEGIN:VEVENT',
                `UID:appointment-${payload.id}@santeconnect`,
                `DTSTAMP:${payload.stamp}`,
                `DTSTART:${payload.start}`,
                `DTEND:${payload.end}`,
                `SUMMARY:${payload.summary}`,
                `DESCRIPTION:${payload.description}`,
                `LOCATION:${payload.location}`,
                'END:VEVENT',
                'END:VCALENDAR',
            ];
            const blob = new Blob([lines.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `santeconnect-rdv-${payload.id}.ics`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
            this.showToast('Fichier calendrier téléchargé.');
        },
        simulateFamilyBooking() {
            this.showToast('Réservation pour un proche : aperçu visuel uniquement — non disponible.');
        },
        moveOtp(index, event) {
            const value = event.target.value.replace(/\D/g, '').slice(-1);
            this.twoFactor[index] = value;
            if (value && event.target.nextElementSibling) {
                event.target.nextElementSibling.focus();
            }
        },
    }));

    Alpine.data('scDirectory', (payload) => ({
        doctors: payload.doctors || [],
        selectedId: payload.selectedId,
        selectedDay: payload.selectedDay,
        selectedSlot: payload.selectedSlot,
        format: 'clinic',
        toast(message) {
            if (window.Alpine && Alpine.$data(document.body) && typeof Alpine.$data(document.body).showToast === 'function') {
                Alpine.$data(document.body).showToast(message);
            }
        },
        current() {
            return this.doctors.find((doctor) => doctor.id === this.selectedId) || this.doctors[0] || null;
        },
        days() {
            return this.current()?.days || [];
        },
        day() {
            return this.days().find((item) => item.date === this.selectedDay) || this.days()[0] || null;
        },
        slots() {
            return this.day()?.slots || [];
        },
        morning() {
            return this.slots().filter((slot) => slot.hour < 12);
        },
        afternoon() {
            return this.slots().filter((slot) => slot.hour >= 12);
        },
        selectDoctor(id) {
            this.selectedId = id;
            const first = this.days()[0];
            this.selectedDay = first?.date || null;
            this.selectedSlot = first?.slots?.[0]?.id || null;
        },
        selectDay(date) {
            this.selectedDay = date;
            this.selectedSlot = this.day()?.slots?.[0]?.id || null;
        },
        selectSlot(id) {
            this.selectedSlot = id;
        },
        pickSlot(doctorId, slotId, date) {
            this.selectDoctor(doctorId);
            if (date) {
                this.selectDay(date);
            }
            this.selectSlot(slotId);
        },
        selectedHref() {
            return this.slots().find((slot) => slot.id === this.selectedSlot)?.href || null;
        },
        selectedLabel() {
            const day = this.day();
            const slot = this.slots().find((item) => item.id === this.selectedSlot);
            if (! day || ! slot) {
                return 'Aucun créneau sélectionné';
            }

            return `${day.full} · ${slot.time}`;
        },
        continueBooking() {
            const href = this.selectedHref();
            if (href) {
                window.location.href = href;
                return;
            }
            this.toast('Choisissez un créneau disponible.');
        },
    }));

    Alpine.data('scResetSuccess', (loginUrl) => ({
        seconds: 5,
        paused: false,
        loginUrl,
        init() {
            this._timer = window.setInterval(() => {
                if (this.paused) {
                    return;
                }
                this.seconds -= 1;
                if (this.seconds <= 0) {
                    window.clearInterval(this._timer);
                    window.location.href = this.loginUrl;
                }
            }, 1000);
        },
        togglePause() {
            this.paused = !this.paused;
        },
        dashoffset() {
            return 88 - (Math.max(this.seconds, 0) / 5) * 88;
        },
        rootDemo() {
            return window.Alpine && Alpine.$data(document.body) ? Alpine.$data(document.body) : null;
        },
        toast(message) {
            this.rootDemo()?.showToast?.(message);
        },
        sms() {
            this.rootDemo()?.simulateSmsReset?.();
        },
        wallet(kind) {
            this.rootDemo()?.simulateWallet?.(kind);
        },
    }));

    Alpine.data('scBooking', (initialStep = 1) => ({
        step: initialStep,
        submitting: false,
        pay: 'desk',
        modality: 'inperson',
        cover: 'none',
        next() {
            this.step = Math.min(this.step + 1, 4);
        },
        prev() {
            this.step = Math.max(this.step - 1, 1);
        },
        demoToast(message) {
            const root = document.body;
            if (window.Alpine && Alpine.$data(root) && typeof Alpine.$data(root).showToast === 'function') {
                Alpine.$data(root).showToast(message);
            }
        },
        submit(event) {
            this.submitting = true;
            event.target.submit();
        },
    }));
});

Alpine.start();
