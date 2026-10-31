<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DoctorException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Models\Doctor;
use App\Models\Speciality;
use App\Models\User;
use App\Services\DoctorOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des médecins côté administrateur.
 *
 * La création d'un profil `doctors` confirme un compte médecin inscrit.
 */
class DoctorController extends Controller
{
    public function __construct(private readonly DoctorOnboardingService $onboarding) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Doctor::class);

        $search = trim((string) $request->query('search', ''));
        $specialityId = $request->integer('speciality_id') ?: null;
        $status = $request->query('status');
        if (! in_array($status, ['validated', 'pending'], true)) {
            $status = null;
        }

        $allowedSorts = [
            'name' => 'users.name',
            'speciality' => 'specialities.name',
            'created' => 'doctors.created_at',
        ];
        $sortKey = $request->query('sort', 'name');
        $sort = $allowedSorts[$sortKey] ?? 'users.name';
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $doctorsQuery = Doctor::query()
            ->with(['user', 'speciality'])
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->join('specialities', 'specialities.id', '=', 'doctors.speciality_id')
            ->select('doctors.*')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', '%'.$search.'%')
                        ->orWhere('users.email', 'like', '%'.$search.'%')
                        ->orWhere('doctors.phone', 'like', '%'.$search.'%');
                });
            })
            ->when($specialityId, fn ($query) => $query->where('doctors.speciality_id', $specialityId))
            ->orderBy($sort, $direction);

        $doctors = ($status === 'pending'
            ? Doctor::query()->whereRaw('0 = 1')->paginate(15)
            : $doctorsQuery->paginate(15)
        )->withQueryString();

        $pendingDoctors = User::doctors()
            ->doesntHave('doctor')
            ->when($status === 'validated', fn ($query) => $query->whereRaw('0 = 1'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $totalDoctors = Doctor::query()->count();
        $doctorsWithSlots = Doctor::query()
            ->whereHas('availabilities', fn ($query) => $query->available())
            ->count();

        return view('admin.doctors.index', [
            'doctors' => $doctors,
            'pendingDoctors' => $pendingDoctors,
            'specialties' => Speciality::query()->orderBy('name')->get(),
            'filters' => [
                'search' => $search,
                'speciality_id' => $specialityId,
                'status' => $status,
                'sort' => array_key_exists($sortKey, $allowedSorts) ? $sortKey : 'name',
                'direction' => $direction,
            ],
            'stats' => [
                'total' => $totalDoctors,
                'withSlots' => $doctorsWithSlots,
                'withoutSlots' => max($totalDoctors - $doctorsWithSlots, 0),
                'pending' => User::doctors()->doesntHave('doctor')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Doctor::class);

        $users = User::doctors()->doesntHave('doctor')->orderBy('name')->get();
        $specialties = Speciality::orderBy('name')->get();

        return view('admin.doctors.create', compact('users', 'specialties'));
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        $this->authorize('create', Doctor::class);

        $data = $request->validated();
        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('doctors', 'public');
        }

        try {
            $this->onboarding->createProfile(
                User::query()->findOrFail((int) $data['user_id']),
                (int) $data['speciality_id'],
                [
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'photo' => $photoPath,
                ],
            );
        } catch (DoctorException $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            return back()->withErrors(['user_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin créé avec succès.');
    }

    public function edit(Doctor $doctor): View
    {
        $this->authorize('update', $doctor);

        $doctor->load(['user', 'speciality']);
        $specialties = Speciality::orderBy('name')->get();

        $appointmentCount = $doctor->appointments()->count();
        $monthAppointmentCount = $doctor->appointments()
            ->whereBetween('appointment_date', [
                now()->copy()->startOfMonth(),
                now()->copy()->endOfMonth(),
            ])
            ->count();
        $availableSlots = $doctor->availabilities()->available()->count();

        return view('admin.doctors.edit', compact(
            'doctor',
            'specialties',
            'appointmentCount',
            'monthAppointmentCount',
            'availableSlots'
        ));
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('update', $doctor);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($doctor->photo) {
                Storage::disk('public')->delete($doctor->photo);
            }
            $data['photo'] = $request->file('photo')->store('doctors', 'public');
        }

        try {
            $this->onboarding->updateByAdmin($doctor, $data);
        } catch (DoctorException $e) {
            return back()->withErrors(['speciality_id' => $e->getMessage()])->withInput();
        }

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin mis à jour avec succès.');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->authorize('delete', $doctor);

        $doctor->delete();

        return redirect()->route('admin.doctors.index')
            ->with('success', 'Médecin supprimé avec succès.');
    }
}
