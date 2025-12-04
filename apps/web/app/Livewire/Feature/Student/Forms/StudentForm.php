<?php

namespace App\Livewire\Feature\Student\Forms;

use App\Models\Student;
use App\Models\StudyProgram;
use App\Services\StudentService;
use App\Services\StudyProgramService;
use App\Services\UserService;
use App\Traits\Livewire\WithAlertModal;
use DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use PhpParser\ErrorHandler\Throwing;

class StudentForm extends Component
{
    use WithAlertModal;
    protected UserService $userService;
    protected StudentService $stService;
    protected StudyProgramService $spService;

    public $student;
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

    // Student Attribute
    public $nim;
    public $generation;
    public $sp_id;

    public function boot(UserService $userService, StudentService $stService, StudyProgramService $spService)
    {
        $this->userService = $userService;
        $this->stService = $stService;
        $this->spService = $spService;
    }

    public function mount($student = null)
    {
        $this->student = $student;
        $this->isEditing = (bool) $this->student;

        if ($this->student) {
            $this->username = $this->student->user->username;
            $this->name = $this->student->user->name;
            $this->password = $this->student->user->password;
            $this->email = $this->student->user->email;
            $this->phone_number = $this->student->user->phone_number;
            $this->gender = $this->student->user->gender;
            $this->address = $this->student->user->address;
            $this->nim = $this->student->nim;
            $this->generation = $this->student->generation;
            $this->sp_id = $this->student->sp_id;
        }
    }

    public function save()
    {
        $userId = $this->isEditing && $this->student ? $this->student->user->id : null;
        $studentId = $this->isEditing && $this->student ? $this->student->id : null;

        $rules = [
            'username' => ['required', 'string', 'min:3', 'max:50',$userId ? Rule::unique('users','username')->ignore($userId) : 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255',$userId ? Rule::unique('users','email')->ignore($userId) : 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'min:10', 'max:13'],
            'gender' => ['nullable', Rule::in([1,2])], // atau gunakan Enum Rule jika prefer
            'address' => ['nullable', 'string', 'max:500'],
            'nim' => ['required', 'string', 'max:20', $studentId ? Rule::unique('students','nim')->ignore($studentId) : 'unique:students,nim'],
            'generation' => ['required', 'integer', 'min:1990', 'max:' . now()->year],
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

                $studentData = [
                    'generation' => $validated['generation'],
                    'nim' => $validated['nim'],
                    'sp_id' => $validated['sp_id']
                ];

                if (!$this->isEditing) {
                    $userData['password'] = $validated['password'];
                }

                if ($this->student) {
                    $user = $this->userService->update($this->student->user, $userData);
                    $this->stService->update($this->student, $user->id, $studentData);
                } else {
                    $user = $this->userService->create( $userData);
                    $this->stService->create( $user->id, $studentData);
                    return $this->redirectRoute('student.index', navigate: true);
                }

                return $this->showSuccessAlert('Data Mahasiswa Berhasil Diupdate');
            });

        } catch (\Exception $e) {
            throw $e;
            $this->addError('code', $e->getMessage());
        }
    }

    // public function resetForm()
    // {
    //     $this->reset(['code', 'name']);
    //     $this->resetErrorBag();
    // }

    public function render()
    {
        $studyPrograms = $this->spService->getAll(isPaginated: false);
        return view('livewire.feature.student.forms.student-form', [
            'studyPrograms' => $studyPrograms
        ]);
    }
}
