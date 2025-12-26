<?php

namespace App\Livewire\Feature\Lecturer\Forms;

use App\Enums\User\UserGenderEnum;
use App\Enums\UserRoleEnum;
use App\Services\LecturerService;
use App\Services\StudyProgramService;
use App\Services\UserService;
use App\Traits\Livewire\WithAlertModal;
use DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class LecturerForm extends Component
{
    use WithAlertModal;
    protected UserService $userService;
    protected LecturerService $leService;
    protected StudyProgramService $spService;

    public $lecturer;
    public bool $isEditing;

    // Main User Attribute
    public $username;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    // Additional User Attribute
    public $phone_number;
    public $gender;
    public $address;

    // Lecturer Attribute
    public $nip;
    public $nidn;
    public $code;
    public $generation;
    public $sp_id;

    public function boot(UserService $userService, LecturerService $leService, StudyProgramService $spService)
    {
        $this->userService = $userService;
        $this->leService = $leService;
        $this->spService = $spService;
    }

    public function mount($lecturer = null)
    {
        $this->lecturer = $lecturer;
        $this->isEditing = (bool) $this->lecturer;

        if ($this->lecturer) {
            $this->username = $this->lecturer->user->username;
            $this->name = $this->lecturer->user->name;
            $this->password = $this->lecturer->user->password;
            $this->email = $this->lecturer->user->email;
            $this->phone_number = $this->lecturer->user->phone_number;
            $this->gender = $this->lecturer->user->gender;
            $this->address = $this->lecturer->user->address;
            $this->nip = $this->lecturer->nip;
            $this->nidn = $this->lecturer->nidn;
            $this->code = $this->lecturer->nip;
            $this->generation = $this->lecturer->generation;
            $this->sp_id = $this->lecturer->sp_id;
        }
    }

    public function save()
    {
        $userId = $this->isEditing && $this->lecturer ? $this->lecturer->user->id : null;
        $lecturerId = $this->isEditing && $this->lecturer ? $this->lecturer->id : null;

        $rules = [
            'username' => ['required', 'string', 'min:3', 'max:50',$userId ? Rule::unique('users','username')->ignore($userId) : 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255',$userId ? Rule::unique('users','email')->ignore($userId) : 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'min:10', 'max:13'],
            'gender' => ['nullable', Rule::enum(UserGenderEnum::class)], // atau gunakan Enum Rule jika prefer
            'address' => ['nullable', 'string', 'max:500'],
            'nip' => ['required', 'numeric', 'digits:18', $lecturerId ? Rule::unique('lecturers','nip')->ignore($lecturerId) : 'unique:lecturers,nip'],
            'nidn' => ['required', 'numeric', 'digits:10', $lecturerId ? Rule::unique('lecturers','nip')->ignore($lecturerId) : 'unique:lecturers,nip'],
            'code' => ['required', 'string', 'max:20', $lecturerId ? Rule::unique('lecturers','nip')->ignore($lecturerId) : 'unique:lecturers,nip'],
            'sp_id' => ['required', 'exists:study_programs,id'],
        ];

        if(!$this->isEditing) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'] ;
        }

        $validated = $this->validate($rules);

        try {
            DB::transaction(function () use ($validated) {
                $userData = [
                    'username' => $validated['username'],
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'],
                    'gender' => $validated['gender'],
                    'address' => $validated['address'],
                ];

                $lecturerData = [
                    'nip' => $validated['nip'],
                    'nidn' => $validated['nidn'],
                    'code' => $validated['code'],
                    'sp_id' => $validated['sp_id']
                ];

                if (!$this->isEditing) {
                    $userData['password'] = $validated['password'];
                }

                if ($this->lecturer) {
                    $user = $this->userService->update($this->lecturer->user, $userData);
                    $this->leService->update($this->lecturer, $user->id, $lecturerData);
                } else {
                    $user = $this->userService->create( $userData, role: UserRoleEnum::LECTURER->value);
                    $this->leService->create( $user->id, $lecturerData, );
                    return $this->redirectRoute('lecturer.index', navigate: true);
                }

                return $this->showSuccessAlert('Data Dosen Berhasil Diupdate');
            });
        } catch (\Exception $e) {
            $this->showErrorAlert('Terjadi kesalahan, silahkan coba lagi!');
        }
    }

    public function render()
    {
        $studyPrograms = $this->spService->getAll(isPaginated: false);
        return view('livewire.feature.lecturer.forms.lecturer-form', [
            'studyPrograms' => $studyPrograms
        ]);
    }
}
