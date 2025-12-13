<?php

use App\Http\Middleware\GuestOnlyMiddleware;
use App\Http\Middleware\KplOnlyMiddleware;
use App\Http\Middleware\LbrOnlyMiddleware;
use App\Http\Middleware\LbrOrKplOnlyMiddleware;
use App\Http\Middleware\UserOnlyMiddleware;
use App\Livewire\Feature\AcademicClass\Pages\AcademicClassFormPage;
use App\Livewire\Feature\AcademicClass\Pages\AcademicClassListPage;
use App\Livewire\Feature\AcademicClass\Pages\StudentListPage;
use App\Livewire\Feature\Application\Pages\ApplicationList;
use App\Livewire\Feature\Auth\Login;
use App\Livewire\Feature\Computer\Pages\ComputerFormPage;
use App\Livewire\Feature\Computer\Pages\ComputerList;
use App\Livewire\Feature\Course\Pages\CourseFormPage;
use App\Livewire\Feature\Course\Pages\CourseList;
use App\Livewire\Feature\Dashboard\Index as Dashboard;
use App\Livewire\Feature\Department\Pages\DepartmentList;
use App\Livewire\Feature\Lecturer\Pages\LecturerFormPage;
use App\Livewire\Feature\Lecturer\Pages\LecturerList;
use App\Livewire\Feature\Room\Pages\RoomFormPage;
use App\Livewire\Feature\Room\Pages\RoomList;
use App\Livewire\Feature\Schedule\Pages\RequestListPage;
use App\Livewire\Feature\Schedule\Pages\ScheduleIndexPage;
use App\Livewire\Feature\Student\Pages\StudentFormPage;
use App\Livewire\Feature\Student\Pages\StudentList;
use App\Livewire\Feature\StudyProgram\Pages\StudyProgramFormPage;
use App\Livewire\Feature\StudyProgram\Pages\StudyProgramList;
use Illuminate\Support\Facades\Route;

Route::get('/', Login::class)->middleware([GuestOnlyMiddleware::class])->name('login');

Route::middleware([UserOnlyMiddleware::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/jadwal', ScheduleIndexPage::class)->name('schedule.index');
    Route::get('/jadwal/request', RequestListPage::class)->name('schedule.request.index');
    Route::get('/matakuliah', CourseList::class)->name('course.index');
    Route::get('/matakuliah/tambah', CourseFormPage::class)->name('course.add');
    Route::get('/matakuliah/{courseId}', CourseFormPage::class)->name('course.edit');

    Route::middleware([LbrOrKplOnlyMiddleware::class])->group(function () {
        Route::get('/jurusan', DepartmentList::class)->name('department.index');

        Route::get('/prodi', StudyProgramList::class)->name('study-program.index');
        Route::get('/prodi/tambah', StudyProgramFormPage::class)->where(['spId' =>'[0-9]+'])->name('study-program.add');
        Route::get('/prodi/{spId}', StudyProgramFormPage::class)->where(['spId' =>'[0-9]+'])->name('study-program.edit');
        Route::get('/prodi/{spId}/kelas', AcademicClassListPage::class)->where(['spId' =>'[0-9]+'])->name('study-program.class.index');
        Route::get('/prodi/{spId}/kelas/tambah', AcademicClassFormPage::class)->where(['spId' =>'[0-9]+'])->name('study-program.class.add');
        Route::get('/prodi/{spId}/kelas/{classId}', AcademicClassFormPage::class)->where(['spId' =>'[0-9]+', 'classId' =>'[0-9]+'])->name('study-program.class.edit');
        Route::get('/prodi/{spId}/kelas/{classId}/mahasiswa', StudentListPage::class)->where(['spId' =>'[0-9]+', 'classId' =>'[0-9]+'])->name('study-program.class.student.index');

        Route::get('/mahasiswa', StudentList::class)->name('student.index');
        Route::get('/mahasiswa/tambah', StudentFormPage::class)->name('student.add');
        Route::get('/mahasiswa/{studentId}', StudentFormPage::class)->where('studentId', '[0-9]+')->name('student.edit');

        Route::get('/dosen', LecturerList::class)->name('lecturer.index');
        Route::get('/dosen/tambah', LecturerFormPage::class)->name('lecturer.add');
        Route::get('/dosen/{lecturerId}', LecturerFormPage::class)->where('lecturerId', '[0-9]+')->name('lecturer.edit');

        Route::get('/ruangan', RoomList::class)->name('room.index');
        Route::get('/ruangan/tambah', RoomFormPage::class)->name('room.add');
        Route::get('/ruangan/{roomId}', RoomFormPage::class)->where('roomId', '[0-9]+')->name('room.edit');
        Route::get('/ruangan/{roomId}/komputer', ComputerList::class)->where('roomId', '[0-9]+')->name('room.computer.index');
        Route::get('/ruangan/{roomId}/komputer/tambah', ComputerFormPage::class)->where(['roomId' =>'[0-9]+'])->name('room.computer.add');
        Route::get('/ruangan/{roomId}/komputer/{computerId}', ComputerFormPage::class)->where(['roomId' => '[0-9]+', 'computerId' => '[0-9]+'])->name('room.computer.edit');
        Route::get('/ruangan/{roomId}/aplikasi', ApplicationList::class)->where('roomId', '[0-9]+')->name('room.app.index');
    });
    Route::middleware([KplOnlyMiddleware::class])->group(function () {
        Route::get('/jurusan', DepartmentList::class)->name('department.index');
    });
});
