<form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="accepted">
    <button type="submit"
            class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-xs font-semibold">
        Accepter
    </button>
</form>
<form method="POST" action="{{ route('doctor.appointments.update', $appointment) }}" class="inline"
      onsubmit="return confirm('Refuser la demande de {{ addslashes($appointment->patient->name) }} ?')">
    @csrf
    @method('PATCH')
    <input type="hidden" name="status" value="rejected">
    <button type="submit"
            class="inline-flex items-center gap-1.5 px-3 py-2 bg-red-50 hover:bg-red-100 border border-red-200 text-red-700 rounded-xl text-xs font-semibold">
        Refuser
    </button>
</form>
